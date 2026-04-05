<?php

namespace App\Services;

use App\Models\CandidateProfile;
use App\Models\JobRole;
use App\Models\JobRequiredSkill;
use App\Models\Resume;
use Illuminate\Support\Str;
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
     * Best-effort name, email, and phone from resume text (AI + regex fallbacks).
     *
     * @return array{name: ?string, email: ?string, phone: ?string}
     */
    public function extractContactIdentityFromText(string $text): array
    {
        $name = null;
        $email = null;
        $phone = null;

        if ($this->gptService->isAvailable() && trim($text) !== '') {
            $data = $this->gptService->extractProfileFromResume($text);
            if (is_array($data)) {
                if (! empty($data['full_name'])) {
                    $name = Str::limit(trim((string) $data['full_name']), 255, '');
                }
                if (! empty($data['email']) && filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    $email = strtolower(trim((string) $data['email']));
                }
                if (! empty($data['phone'])) {
                    $phone = $this->normalizePhoneCandidate((string) $data['phone']);
                }
            }
        }

        if ($email === null || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = $this->extractFirstEmailFromText($text);
        }
        if ($phone === null || $phone === '') {
            $phone = $this->extractFirstPhoneFromText($text);
        }
        if ($name === null || trim($name) === '') {
            $name = $this->guessNameFromResumeLines($text);
        }
        if (($name === null || trim($name) === '') && $email) {
            $name = $this->deriveDisplayNameFromEmail($email);
        }
        if ($name !== null) {
            $name = Str::limit(trim($name), 255, '');
        }

        return [
            'name' => $name !== null && $name !== '' ? $name : null,
            'email' => $email,
            'phone' => $phone,
        ];
    }

    /**
     * True if the string is safe to use as a registration email (extracted or user-supplied fallback).
     */
    public function isRecognizedRegistrationEmail(?string $email): bool
    {
        if ($email === null) {
            return false;
        }
        $email = strtolower(trim($email));

        return $email !== '' && $this->isPlausibleContactEmail($email);
    }

    /**
     * Find a plausible contact email in noisy PDF-extracted text (line breaks, extra spaces, labels).
     */
    protected function extractFirstEmailFromText(string $text): ?string
    {
        $text = $this->scrubExtractedPdfText($text);
        if ($text === '') {
            return null;
        }

        foreach ($this->collectEmailCandidatesFromText($text) as $candidate) {
            if ($this->isPlausibleContactEmail($candidate)) {
                return strtolower($candidate);
            }
        }

        return null;
    }

    /**
     * Clean common PDF-to-text artifacts so emails and labels match reliably.
     */
    protected function scrubExtractedPdfText(string $text): string
    {
        $text = str_replace("\xC2\xAD", '', $text);
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text) ?? '';
        $text = preg_replace('/\x{00A0}/u', ' ', $text) ?? '';

        return trim($text);
    }

    /**
     * Ordered candidates: higher-confidence patterns first.
     *
     * @return list<string>
     */
    protected function collectEmailCandidatesFromText(string $text): array
    {
        $ordered = [];
        $seen = [];
        $add = function (?string $e) use (&$ordered, &$seen): void {
            $e = strtolower(trim((string) $e));
            if ($e === '' || isset($seen[$e])) {
                return;
            }
            $seen[$e] = true;
            $ordered[] = $e;
        };

        // mailto: links
        if (preg_match_all('#mailto:\s*([A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,})#i', $text, $m)) {
            foreach ($m[1] as $raw) {
                $add($this->compactEmailString($raw));
            }
        }

        // "Email:" / "E-mail:" style lines (PDF often keeps these on one line)
        if (preg_match_all(
            '/(?:^|[\s\-–—|])(?:e[\s.\-]*mail|correo|contact)\s*[:\s]\s*([^\s<>"\']+@[^\s<>"\']+)/iu',
            $text,
            $m
        )) {
            foreach ($m[1] as $raw) {
                $add($this->compactEmailString($raw));
            }
        }

        // Emails with spaces around @ or dots (common PDF extraction)
        if (preg_match_all(
            '/[A-Za-z0-9._%+\-]+\s*@\s*[A-Za-z0-9.\-]+\s*(?:\.\s*)+[A-Za-z]{2,}/',
            $text,
            $m
        )) {
            foreach ($m[0] as $raw) {
                $add($this->compactEmailString($raw));
            }
        }

        // Standard contiguous tokens
        if (preg_match_all('/\b[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}\b/', $text, $m)) {
            foreach ($m[0] as $raw) {
                $add(trim($raw));
            }
        }

        // Newlines often split "user@" and "domain.com" — join lines then re-scan
        $oneLine = preg_replace('/\s+/u', ' ', $text) ?? '';
        if (preg_match_all(
            '/[A-Za-z0-9._%+\-]+\s*@\s*[A-Za-z0-9.\-]+\s*(?:\.\s*)+[A-Za-z]{2,}/',
            $oneLine,
            $m
        )) {
            foreach ($m[0] as $raw) {
                $add($this->compactEmailString($raw));
            }
        }

        if (preg_match_all('/\b[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}\b/', $oneLine, $m)) {
            foreach ($m[0] as $raw) {
                $add(trim($raw));
            }
        }

        // Aggressive: remove all whitespace and look for user@host.tld pattern
        $squashed = preg_replace('/\s+/u', '', $text) ?? '';
        if (preg_match_all('/[A-Za-z0-9._%+\-]{1,64}@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}/', $squashed, $m)) {
            foreach ($m[0] as $raw) {
                $add($raw);
            }
        }

        return $ordered;
    }

    protected function compactEmailString(string $raw): string
    {
        $s = strtolower(trim($raw));
        $s = preg_replace('/\s+/', '', $s) ?? '';

        return trim($s, '.,;:<>"\'()[]');
    }

    protected function isPlausibleContactEmail(string $email): bool
    {
        $email = strtolower(trim($email));
        if ($email === '' || ! str_contains($email, '@')) {
            return false;
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ! $this->isDisposableOrPlaceholderEmail($email);
        }

        // Slightly looser check when filter_var rejects IDN or rare local parts
        if (preg_match('/^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$/', $email)) {
            return ! $this->isDisposableOrPlaceholderEmail($email);
        }

        return false;
    }

    protected function isDisposableOrPlaceholderEmail(string $email): bool
    {
        $lower = strtolower($email);
        if (str_contains($lower, 'example.com') || str_contains($lower, 'test@test') || str_contains($lower, 'yourmail') || str_contains($lower, 'email.com')) {
            return true;
        }
        if (preg_match('/^(no-?reply|donotreply|privacy)@/i', $lower)) {
            return true;
        }

        return false;
    }

    protected function extractFirstPhoneFromText(string $text): ?string
    {
        $patterns = [
            '/\+?\d{1,3}[\s.-]?\(?\d{2,4}\)?[\s.-]?\d{3,4}[\s.-]?\d{3,4}(?:[\s.-]?\d{2,5})?/',
            '/\(\d{3}\)\s*\d{3}[\s.-]?\d{4}/',
            '/\b\d{3}[\s.-]?\d{3}[\s.-]?\d{4}\b/',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $m)) {
                $normalized = $this->normalizePhoneCandidate($m[0]);
                if ($normalized !== null && $normalized !== '') {
                    return $normalized;
                }
            }
        }

        return null;
    }

    protected function normalizePhoneCandidate(string $raw): ?string
    {
        $t = trim(preg_replace('/[^\d+()\s.-]/', '', $raw) ?? '');
        $digits = preg_replace('/\D/', '', $t) ?? '';
        if (strlen($digits) < 10) {
            return null;
        }

        return Str::limit(preg_replace('/\s+/', ' ', $t) ?? '', 30, '');
    }

    protected function guessNameFromResumeLines(string $text): ?string
    {
        $lines = preg_split("/\r\n|\r|\n/", $text) ?: [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || mb_strlen($line) > 120) {
                continue;
            }
            if (str_contains($line, '@') || preg_match('#https?://#i', $line)) {
                continue;
            }
            if (preg_match('#^\d[\d\s().+/-]{8,}$#', $line)) {
                continue;
            }
            if (preg_match('/^[A-Za-z\x{00C0}-\x{024F}][A-Za-z\x{00C0}-\x{024F}\s.\'-]{1,100}$/u', $line)) {
                $words = preg_split('/\s+/', $line) ?: [];
                $words = array_values(array_filter($words, fn ($w) => $w !== ''));
                if (count($words) >= 2 && count($words) <= 6) {
                    return $line;
                }
            }
        }

        return null;
    }

    protected function deriveDisplayNameFromEmail(string $email): string
    {
        $local = strstr($email, '@', true) ?: $email;
        $local = str_replace(['.', '_', '-'], ' ', $local);
        $local = preg_replace('/\d+/', '', $local) ?? '';
        $local = trim(preg_replace('/\s+/', ' ', $local) ?? '');
        if ($local === '') {
            return 'Candidate';
        }

        return Str::title($local);
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
            $bundle = $this->gptService->getResumeAnalysisBundle($text);
            if ($bundle !== null) {
                $summary = $bundle['summary'];
                $score = $bundle['score'];
                $explanation = $bundle['explanation'];
                $skills = $bundle['skills'] !== [] ? $bundle['skills'] : null;
            }
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

        return $resume;
    }

    /**
     * Fill profile from resume stored data (no GPT): skills from extracted_skills, headline from ai_summary,
     * plus light regex hints from raw PDF text (phone, education line, location).
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

        $path = storage_path('app/'.$resume->file_path);
        $text = '';
        if (is_readable($path)) {
            $text = $this->extractTextFromFile($path, $resume->mime_type ?? 'application/pdf');
        }

        if ($text !== '' && trim((string) ($user->phone ?? '')) === '') {
            $phone = $this->extractFirstPhoneFromText($text);
            if ($phone !== null && $phone !== '') {
                $user->update(['phone' => Str::limit($phone, 30, '')]);
            }
        }

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
        if (is_string($summary) && trim($summary) !== '' && trim((string) ($profile->headline ?? '')) === '') {
            $firstLine = trim(explode("\n", $summary)[0]);
            $headline = mb_strlen($firstLine) > 120 ? mb_substr($firstLine, 0, 117) . '...' : $firstLine;
            if ($headline !== '') {
                $profile->headline = $headline;
                $changed = true;
            }
        }

        if ($text !== '' && trim((string) ($profile->education ?? '')) === '') {
            $edu = $this->guessEducationLineFromText($text);
            if ($edu !== null) {
                $profile->education = $edu;
                $changed = true;
            }
        }

        if ($text !== '' && trim((string) ($profile->location ?? '')) === '') {
            $loc = $this->guessLocationLineFromText($text);
            if ($loc !== null) {
                $profile->location = Str::limit($loc, 255, '');
                $changed = true;
            }
        }

        if ($changed) {
            $profile->save();
        }
    }

    protected function guessEducationLineFromText(string $text): ?string
    {
        $snippet = mb_substr($text, 0, 8000);
        $lines = preg_split("/\r\n|\r|\n/", $snippet) ?: [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || mb_strlen($line) > 220) {
                continue;
            }
            if (preg_match('/\b(B\.?\s*Tech|M\.?\s*Tech|Bachelor|Master|Ph\.?\s*D|MBA|B\.?\s*E|M\.?\s*E|B\.?\s*Sc|M\.?\s*Sc|B\.?\s*A|M\.?\s*A|Diploma|B\.?\s*Com|B\.?Arch)\b/ui', $line)) {
                return mb_substr($line, 0, 255);
            }
        }

        return null;
    }

    protected function guessLocationLineFromText(string $text): ?string
    {
        $snippet = mb_substr($text, 0, 4000);
        $lines = preg_split("/\r\n|\r|\n/", $snippet) ?: [];
        $pattern = '/\b(Mumbai|Delhi|Bangalore|Bengaluru|Hyderabad|Pune|Chennai|Kolkata|Noida|Gurgaon|Ahmedabad|Jaipur|India|USA|United States|UK|London|Canada|Singapore|Dubai)\b/iu';
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || mb_strlen($line) > 150) {
                continue;
            }
            if (str_contains($line, '@') || preg_match('#https?://#i', $line)) {
                continue;
            }
            if (preg_match($pattern, $line)) {
                return $line;
            }
        }

        return null;
    }

    /**
     * Copy AI summary into bio when bio is still empty (no GPT profile extract).
     */
    public function fillProfileBioFromResumeSummary(Resume $resume): void
    {
        $user = $resume->user;
        if (! $user || ! $user->isCandidate()) {
            return;
        }

        $summary = $resume->ai_summary;
        if (! is_string($summary) || trim($summary) === '') {
            return;
        }

        $profile = $user->candidateProfile ?? new CandidateProfile(['user_id' => $user->id]);
        if ($profile->bio_summary !== null && trim((string) $profile->bio_summary) !== '') {
            return;
        }

        $profile->bio_summary = mb_strlen($summary) > 2500 ? mb_substr($summary, 0, 2497).'...' : $summary;
        $profile->save();
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
     * Rule-based resume-to-employer-job match (no GPT). Used at apply time when AI is unavailable.
     * Returns ['score' => 0-100, 'explanation' => string].
     */
    public function getEmployerJobMatchRuleBased(string $resumeText, string $jobTitle, string $jobDescription, array $requiredSkills = []): array
    {
        $skillsText = implode(' ', array_filter(array_map(fn ($s) => is_string($s) ? trim($s) : '', $requiredSkills)));
        $jobText = mb_strtolower(trim($jobTitle) . ' ' . trim($jobDescription) . ' ' . trim($skillsText));
        $resumeLower = mb_strtolower($resumeText);
        if (mb_strlen($jobText) < 2) {
            return ['score' => 50, 'explanation' => 'Match score based on profile and job details.'];
        }
        $stopWords = ['the', 'and', 'for', 'with', 'from', 'this', 'that', 'are', 'was', 'were', 'have', 'has', 'will', 'your', 'you', 'can', 'all', 'any', 'not', 'into', 'our', 'out', 'may', 'more', 'than', 'their', 'what', 'when', 'which', 'who', 'about', 'after', 'being', 'been', 'before', 'between', 'both', 'during', 'each', 'other', 'such', 'them', 'then', 'there', 'these', 'they', 'would', 'could', 'should', 'able', 'need', 'using', 'used', 'work', 'job', 'role', 'position'];
        $extractWords = function (string $text) use ($stopWords): array {
            $words = preg_split('/[\s\p{P}\d]+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
            $filtered = array_filter($words, function ($w) use ($stopWords) {
                $w = mb_strtolower($w);
                return mb_strlen($w) >= 2 && ! in_array($w, $stopWords, true);
            });
            return array_values(array_unique(array_map('mb_strtolower', $filtered)));
        };
        $jobWords = $extractWords($jobText);
        if (count($jobWords) === 0) {
            return ['score' => 50, 'explanation' => 'Match score based on profile and job details.'];
        }
        $matched = 0;
        foreach ($jobWords as $word) {
            if (mb_strlen($word) >= 2 && mb_strpos($resumeLower, $word) !== false) {
                $matched++;
            }
        }
        $score = (int) round((count($jobWords) > 0 ? ($matched / count($jobWords)) : 0.5) * 100);
        $score = min(100, max(0, $score));
        $explanation = sprintf(
            'Candidate resume overlaps with job keywords (%d of %d). Score based on relevance to role.',
            $matched,
            count($jobWords)
        );
        return ['score' => $score, 'explanation' => $explanation];
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
