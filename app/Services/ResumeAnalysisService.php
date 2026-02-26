<?php

namespace App\Services;

use App\Models\CandidateProfile;
use App\Models\JobRole;
use App\Models\JobRequiredSkill;
use App\Models\Resume;
use Smalot\PdfParser\Parser as PdfParser;

class ResumeAnalysisService
{
    public function __construct(
        protected GptService $gptService
    ) {}

    /**
     * Extract raw text from uploaded file. PDF supported; DOC returns empty for MVP.
     */
    public function extractTextFromFile(string $path, string $mimeType): string
    {
        if (str_starts_with($mimeType, 'application/pdf') || str_ends_with(strtolower($path), '.pdf')) {
            try {
                $parser = new PdfParser();
                $pdf = $parser->parseFile($path);
                return $pdf->getText() ?: '';
            } catch (\Throwable $e) {
                return '';
            }
        }
        return '';
    }

    /**
     * Rule-based ATS score 0-100 when GPT is not used.
     * Stricter criteria so scores are spread realistically (typical resume 50-75, strong 80+).
     */
    public function ruleBasedScore(string $text): int
    {
        $trimmed = trim($text);
        $len = mb_strlen($trimmed);
        $lower = mb_strtolower($text);

        $score = 0;

        // Length (max 22): very short resumes score low
        if ($len >= 600) {
            $score += 22;
        } elseif ($len >= 400) {
            $score += 17;
        } elseif ($len >= 250) {
            $score += 12;
        } elseif ($len >= 100) {
            $score += 6;
        }

        // Clear section headings (max 45)
        if (preg_match('/\b(experience|work\s+history|employment|professional\s+experience)\b/i', $lower)) {
            $score += 15;
        }
        if (preg_match('/\b(education|academic|degree|university|college|qualification)\b/i', $lower)) {
            $score += 12;
        }
        if (preg_match('/\b(skills?|technical\s+skills|proficient|expertise)\b/i', $lower)) {
            $score += 12;
        }
        if (preg_match('/\b(summary|objective|profile)\b/i', $lower)) {
            $score += 6;
        }

        // Quantifiable achievements (numbers, percentages) – max 15
        if (preg_match('/\d+%|\d+\s*(years?|yrs?)|increased|reduced|improved|saved|\d+\s*(million|k|thousand)/i', $lower)) {
            $score += 15;
        } elseif (preg_match('/\d+/', $lower)) {
            $score += 7;
        }

        // Contact info present – max 8
        if (preg_match('/\b[\w.+%-]+@[\w.-]+\.\w{2,}\b|\+\d[\d\s-]{8,}|phone|mobile|contact/i', $lower)) {
            $score += 8;
        }

        // Bullet points or list structure – max 5
        if (preg_match('/[•·\-\*]\s+\w|^\s*[\-\*]\s/m', $trimmed)) {
            $score += 5;
        }

        return (int) min(100, max(0, $score));
    }

    /**
     * Rule-based explanation when GPT is not used.
     */
    public function ruleBasedExplanation(int $score): string
    {
        if ($score >= 70) {
            return 'Your resume has good structure and relevant sections. Consider adding more quantifiable achievements and keywords for the roles you target.';
        }
        if ($score >= 50) {
            return 'Your resume could be stronger. Add clear Experience and Education sections, and a dedicated Skills section with keywords from the job description.';
        }
        return 'Your resume is quite short or missing key sections. Add Experience, Education, and Skills with relevant keywords to improve ATS compatibility.';
    }

    /**
     * Extract skills by matching resume text to job_required_skills (from DB).
     */
    public function keywordBasedSkills(string $text): array
    {
        $skillNames = JobRequiredSkill::query()
            ->distinct()
            ->pluck('skill_name')
            ->map(fn ($s) => trim($s))
            ->filter()
            ->unique()
            ->values()
            ->all();
        $lower = mb_strtolower($text);
        $found = [];
        foreach ($skillNames as $name) {
            if (mb_strlen($name) < 2) {
                continue;
            }
            if (mb_strpos($lower, mb_strtolower($name)) !== false) {
                $found[] = $name;
            }
        }
        return array_values(array_unique($found));
    }

    /**
     * Extract skills by matching resume text to a common list of tech/professional skills.
     * Used so resumes show extracted skills even when DB job_required_skills don't match.
     */
    public function commonSkillsFromText(string $text): array
    {
        $lower = mb_strtolower($text);
        $found = [];
        foreach ($this->getCommonSkillTerms() as $term) {
            $t = mb_strtolower(trim($term));
            if (mb_strlen($t) < 2) {
                continue;
            }
            if (mb_strpos($lower, $t) !== false) {
                $found[] = $term;
            }
        }
        return array_values(array_unique($found));
    }

    /**
     * Common technical and professional skills to detect in resume text.
     */
    protected function getCommonSkillTerms(): array
    {
        return [
            'Laravel', 'PHP', 'Java', 'JavaScript', 'TypeScript', 'Python', 'Node.js', 'React', 'Vue.js', 'Angular',
            'SQL', 'MySQL', 'PostgreSQL', 'MongoDB', 'REST API', 'RESTful', 'GraphQL', 'Git', 'Docker', 'AWS',
            'HTML', 'CSS', 'Bootstrap', 'Tailwind', 'jQuery', 'Redux', 'Express.js', 'Next.js', 'Nuxt',
            'Data Analysis', 'Data Visualization', 'Excel', 'Power BI', 'Tableau', 'Machine Learning', 'AI', 'ETL', 'Statistics',
            'Agile', 'Scrum', 'Jira', 'Project Management', 'Communication', 'Leadership', 'Problem Solving', 'Roadmap', 'Analytics',
            'Manual Testing', 'Automation',
            'C++', 'C#', '.NET', 'Ruby', 'Rails', 'Go', 'Golang', 'Kotlin', 'Swift', 'Flutter', 'React Native',
            'Linux', 'Ubuntu', 'CI/CD', 'Jenkins', 'Kubernetes', 'Azure', 'GCP', 'API', 'Microservices',
            'Full Stack', 'Frontend', 'Backend', 'Web Development', 'Software Development',
        ];
    }

    /**
     * Run full analysis: extract text, optionally use GPT, else rule-based. Update resume and return it.
     * Merges GPT skills with keyword-based skills for smoother results.
     */
    public function analyzeResume(Resume $resume): Resume
    {
        $path = storage_path('app/' . $resume->file_path);
        $text = $this->extractTextFromFile($path, $resume->mime_type ?? 'application/pdf');
        if ($text === '') {
            $text = 'No extractable text from document.';
        }

        $useGpt = $this->gptService->isAvailable();
        $score = null;
        $explanation = null;
        $summary = null;
        $skills = null;

        if ($useGpt) {
            $summary = $this->gptService->getResumeSummary($text);
            $scoreData = $this->gptService->getResumeScoreAndExplanation($text);
            if ($scoreData !== null) {
                $score = $scoreData['score'];
                $explanation = $scoreData['explanation'];
            }
            $skills = $this->gptService->extractSkills($text);
        }

        if ($score === null) {
            $score = $this->ruleBasedScore($text);
            $explanation = $this->ruleBasedExplanation($score);
        }
        if ($summary === null || $summary === '') {
            $summary = $this->fallbackSummary($text);
        }

        $keywordSkills = $this->keywordBasedSkills($text);
        $commonSkills = $this->commonSkillsFromText($text);
        if ($skills === null || $skills === []) {
            $skills = $this->mergeSkills($commonSkills, $keywordSkills);
        } else {
            $skills = $this->mergeSkills($skills, $this->mergeSkills($commonSkills, $keywordSkills));
        }

        $resume->update([
            'ai_score' => $score,
            'ai_score_explanation' => $explanation,
            'ai_summary' => $summary,
            'extracted_skills' => $skills,
        ]);

        $resume->refresh();

        if ($this->gptService->isAvailable()) {
            $this->fillProfileFromResumeText($resume->user_id, $text);
        } else {
            $this->fillProfileFromResumeFallback($resume);
        }

        return $resume;
    }

    /**
     * Fill candidate profile from resume text using GPT. Called after analyzeResume when GPT is available.
     */
    public function fillProfileFromResumeText(int $userId, string $text): void
    {
        $user = \App\Models\User::find($userId);
        if (! $user || ! $user->isCandidate()) {
            return;
        }

        $data = $this->gptService->extractProfileFromResume($text);
        if (! $data) {
            $this->fillProfileFromResumeFallback(\App\Models\Resume::where('user_id', $userId)->orderByDesc('created_at')->first());
            return;
        }

        if (! empty($data['phone'])) {
            $user->update(['phone' => $data['phone']]);
        }

        $profile = $user->candidateProfile ?? new CandidateProfile(['user_id' => $user->id]);
        if (! empty($data['headline'])) {
            $profile->headline = $data['headline'];
        }
        if (! empty($data['education'])) {
            $profile->education = $data['education'];
        }
        if (isset($data['experience_years']) && $data['experience_years'] !== null) {
            $profile->experience_years = $data['experience_years'];
        }
        if (! empty($data['skills'])) {
            $profile->skills = $data['skills'];
        }
        if (! empty($data['location'])) {
            $profile->location = $data['location'];
        }
        if (! empty($data['expected_salary'])) {
            $profile->expected_salary = $data['expected_salary'];
        }
        $profile->save();
    }

    /**
     * Fill profile from resume stored data (no GPT): skills from extracted_skills, headline from ai_summary.
     * Ensures profile columns get data even when OpenAI is not configured.
     */
    public function fillProfileFromResumeFallback(Resume $resume): void
    {
        if (! $resume) {
            return;
        }
        $user = $resume->user;
        if (! $user || ! $user->isCandidate()) {
            return;
        }

        $profile = $user->candidateProfile ?? new CandidateProfile(['user_id' => $user->id]);
        $changed = false;

        $skills = $resume->extracted_skills;
        if (is_array($skills) && count($skills) > 0) {
            $skillStr = implode(', ', array_map(fn ($s) => is_string($s) ? trim($s) : '', $skills));
            $skillStr = trim(preg_replace('/,\s*,/', ',', $skillStr));
            if ($skillStr !== '') {
                $profile->skills = $skillStr;
                $changed = true;
            }
        }

        $summary = $resume->ai_summary;
        if (is_string($summary) && trim($summary) !== '') {
            $firstLine = trim(explode("\n", $summary)[0]);
            $headline = mb_strlen($firstLine) > 120 ? mb_substr($firstLine, 0, 117) . '...' : $firstLine;
            if ($headline !== '') {
                $profile->headline = $headline;
                $changed = true;
            }
        }

        if ($changed) {
            $profile->save();
        }
    }

    protected function fallbackSummary(string $text): string
    {
        $trimmed = trim($text);
        if (mb_strlen($trimmed) <= 500) {
            return $trimmed;
        }
        return mb_substr($trimmed, 0, 497) . '...';
    }

    /**
     * Rule-based resume-to-job-role match score (0-100) and explanation.
     * Used when GPT is not available. Employer-facing explanation.
     */
    public function getResumeJobRoleMatch(Resume $resume, JobRole $jobRole): array
    {
        $extracted = $resume->getExtractedSkillsList();
        $jobRole->loadMissing('requiredSkills');
        $required = $jobRole->requiredSkills->pluck('skill_name')->map(fn ($s) => strtolower(trim($s)))->unique()->values()->all();

        if (count($required) === 0) {
            return [
                'score' => 50,
                'explanation' => 'No specific skills defined for this role. Your resume has been submitted for review.',
            ];
        }

        $matched = array_values(array_intersect($required, $extracted));
        $missing = array_values(array_diff($required, $extracted));
        $score = (int) round((count($matched) / count($required)) * 100);
        $score = min(100, max(0, $score));

        $explanation = sprintf(
            'Candidate has %d of %d required skills (%.0f%% match). Matched: %s.',
            count($matched),
            count($required),
            (count($matched) / count($required)) * 100,
            count($matched) > 0 ? implode(', ', array_slice($matched, 0, 5)) . (count($matched) > 5 ? '...' : '') : 'none'
        );
        if (count($missing) > 0) {
            $explanation .= ' Gaps: ' . implode(', ', array_slice($missing, 0, 5)) . (count($missing) > 5 ? '...' : '') . '.';
        }

        return [
            'score' => $score,
            'explanation' => $explanation,
        ];
    }

    /**
     * Get job roles sorted by resume match % for the job-goals resume view.
     *
     * @return array<int, array{job_role: JobRole, match_percentage: int, missing_skills: array<string>}>
     */
    public function getMatchingJobGoalsForResume(Resume $resume, int $limit = 20): array
    {
        $extracted = $resume->getExtractedSkillsList();
        $roles = JobRole::where('is_active', true)->with('requiredSkills')->get();
        $scored = [];
        foreach ($roles as $role) {
            $required = $role->requiredSkills->pluck('skill_name')->map(fn ($s) => strtolower(trim($s)))->unique()->values()->all();
            if (count($required) === 0) {
                $scored[] = ['job_role' => $role, 'match_percentage' => 0, 'missing_skills' => []];
                continue;
            }
            $matched = array_values(array_intersect($required, $extracted));
            $missing = array_values(array_diff($required, $extracted));
            $matchPercentage = (int) round((count($matched) / count($required)) * 100);
            $scored[] = [
                'job_role' => $role,
                'match_percentage' => $matchPercentage,
                'missing_skills' => $missing,
            ];
        }
        usort($scored, fn ($a, $b) => $b['match_percentage'] <=> $a['match_percentage']);
        return array_slice($scored, 0, $limit);
    }

    /**
     * Merge GPT-extracted skills with keyword-matched skills (no duplicates, preserve order).
     */
    protected function mergeSkills(array $fromGpt, array $fromKeywords): array
    {
        $seen = [];
        $merged = [];
        foreach ($fromGpt as $s) {
            $n = is_string($s) ? trim($s) : '';
            if ($n !== '' && ! isset($seen[mb_strtolower($n)])) {
                $seen[mb_strtolower($n)] = true;
                $merged[] = $n;
            }
        }
        foreach ($fromKeywords as $s) {
            $n = is_string($s) ? trim($s) : '';
            if ($n !== '' && ! isset($seen[mb_strtolower($n)])) {
                $seen[mb_strtolower($n)] = true;
                $merged[] = $n;
            }
        }
        return $merged;
    }
}
