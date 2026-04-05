<?php

namespace App\Services;

use Aws\BedrockRuntime\BedrockRuntimeClient;
use Aws\Exception\AwsException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GptService
{
    protected ?string $openAiApiKey;

    protected string $openAiModel;

    protected ?string $primaryApiKey;

    protected string $primaryBaseUrl;

    protected string $primaryModel;

    protected ?string $bedrockBearer;

    protected ?string $bedrockAccessKey;

    protected ?string $bedrockSecretKey;

    protected bool $bedrockUseIam = true;

    protected string $bedrockRegion;

    protected string $bedrockModelId;

    protected int $timeout = 120;

    protected int $connectTimeout = 30;

    protected int $maxTokens = 1200;

    protected ?string $lastError = null;

    public function __construct()
    {
        $this->openAiApiKey = config('services.openai.key') ?: null;
        $this->openAiModel = (string) config('services.openai.model', 'gpt-4o-mini');
        $this->primaryApiKey = config('services.primary_llm.key') ?: null;
        $this->primaryBaseUrl = rtrim((string) config('services.primary_llm.base_url', 'https://openrouter.ai/api/v1'), '/');
        $this->primaryModel = (string) config('services.primary_llm.model', 'openai/gpt-oss-20b:free');
        $this->bedrockBearer = config('services.bedrock.bearer_token') ?: null;
        $this->bedrockAccessKey = config('services.bedrock.key') ?: null;
        $this->bedrockSecretKey = config('services.bedrock.secret') ?: null;
        $this->bedrockUseIam = (bool) config('services.bedrock.use_iam', true);
        $this->bedrockRegion = (string) config('services.bedrock.region', 'us-east-1');
        $this->bedrockModelId = (string) config('services.bedrock.model_id', 'amazon.nova-2-lite-v1:0');
        $this->timeout = max(10, (int) config('hirevo.llm_http_timeout_seconds', 120));
        $this->connectTimeout = max(5, (int) config('hirevo.llm_http_connect_timeout_seconds', 30));
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function isAvailable(): bool
    {
        return ! empty($this->primaryApiKey)
            || ! empty($this->openAiApiKey)
            || $this->isBedrockConfigured();
    }

    /**
     * Get a short resume summary from raw text. Trained for concise, professional summary.
     */
    public function getResumeSummary(string $text): ?string
    {
        $truncated = mb_substr($text, 0, 6000);
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a professional resume analyst. Output only the requested content. No preamble, no "Here is...", no markdown. Use clear, neutral language.',
            ],
            [
                'role' => 'user',
                'content' => "Summarize this resume in 4-5 clear, professional sentences. Include:\n"
                    . "1. Current or most recent job title and company, and total years of experience if stated.\n"
                    . "2. Top 3-5 technical or domain skills (e.g. Laravel, Data Analysis, Project Management).\n"
                    . "3. Education or notable certifications in one short phrase.\n"
                    . "4. One sentence on profile type (e.g. 'Strong full-stack profile' or 'Early-career developer with growth potential').\n\n"
                    . "Write in third person. No bullet points. Resume text:\n---\n" . $truncated,
            ],
        ];
        $response = $this->chat($messages);
        return $response ? trim($response) : null;
    }

    /**
     * Generate a full job description from a job title. Professional, ready to edit.
     */
    public function generateJobDescription(string $jobTitle): ?string
    {
        $title = trim($jobTitle);
        if ($title === '') {
            return null;
        }
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are an HR expert writing job descriptions. Output only the job description text. Use clear sections: About the role, Responsibilities, Requirements/Qualifications, Nice to have (optional). Use bullet points where appropriate. Write in a professional, inclusive tone. Be concise—no filler, no repetition. No preamble like "Here is the description".',
            ],
            [
                'role' => 'user',
                'content' => "Write a complete job description for the following job title. Include: a short intro (2-3 sentences), key responsibilities (4-6 bullets), required qualifications/skills (4-6 bullets), and optional nice-to-have (2-3 bullets max). Keep it practical, scannable, and roughly one printed page or less.\n\nJob title: " . $title,
            ],
        ];
        $maxTokens = (int) config('hirevo.llm_job_description_max_tokens', 750);
        $prevTimeout = $this->timeout;
        $this->timeout = min($prevTimeout, (int) config('hirevo.llm_job_description_timeout_seconds', 90));
        try {
            $response = $this->chat($messages, $maxTokens);
        } finally {
            $this->timeout = $prevTimeout;
        }

        return $response ? trim($response) : null;
    }

    /**
     * Get ATS score (0-100) and explanation. Trained for strict JSON output.
     */
    public function getResumeScoreAndExplanation(string $text): ?array
    {
        $truncated = mb_substr($text, 0, 6000);
        $system = 'You are an ATS (Applicant Tracking System) resume analyst. You MUST reply with ONLY a single valid JSON object. No markdown, no code fences, no extra text. Keys: "score" (integer 0-100) and "explanation" (string). Be strict: most resumes 45-72; strong 73-85; exceptional 86-100. The explanation must be 3-5 sentences: first 1-2 sentences on what works well (structure, keywords, clarity); then 2-3 sentences on specific, actionable improvements (e.g. "Add a Skills section with Python and SQL" or "Include metrics like percentage improvement").';
        $user = "Analyze this resume for ATS compatibility. Check: (1) Structure: Experience, Education, Skills sections and bullet points. (2) Keywords: role-relevant terms and technologies. (3) Achievements: quantifiable results (%, numbers). (4) Format: clear dates, contact info, no complex tables.\n\n"
            . "Give a realistic score. In explanation: briefly state strengths, then give 2-3 specific improvements the candidate can act on. Output ONLY this JSON:\n{\"score\": <0-100>, \"explanation\": \"<3-5 sentences: strengths then improvements>\"}\n\nResume text:\n---\n" . $truncated;

        $response = $this->chat([
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user],
        ]);

        if (! $response) {
            return null;
        }

        $decoded = $this->parseJsonObject($response);
        if ($decoded !== null && isset($decoded['score']) && isset($decoded['explanation'])) {
            $score = (int) $decoded['score'];
            $score = max(0, min(100, $score));
            return [
                'score' => $score,
                'explanation' => (string) $decoded['explanation'],
            ];
        }

        return null;
    }

    /**
     * Extract list of skills from resume text. Trained for strict JSON array output.
     */
    public function extractSkills(string $text): ?array
    {
        $truncated = mb_substr($text, 0, 6000);
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a resume parser. Output ONLY a valid JSON array of strings. No markdown, no code fences. Each skill: short, standard name (e.g. "Data Analysis", "Laravel", "REST API"). Include: programming languages, frameworks, tools, databases, soft skills (max 3-4 like Communication, Leadership). Deduplicate; normalize (e.g. "MS Excel" -> "Excel"). Return 12-25 skills, ordered: technical first, then tools, then soft skills.',
            ],
            [
                'role' => 'user',
                'content' => "Extract all professional skills from this resume. Return ONLY a JSON array of strings. Include technologies, frameworks, tools, and 2-4 soft skills. Example: [\"PHP\", \"Laravel\", \"MySQL\", \"REST API\", \"Git\", \"Problem Solving\"]. No other text.\n\nResume text:\n---\n" . $truncated,
            ],
        ];
        $response = $this->chat($messages);
        if (! $response) {
            return null;
        }

        $decoded = $this->parseJsonArray($response);
        if (! is_array($decoded)) {
            return null;
        }
        $skills = [];
        foreach ($decoded as $item) {
            if (is_string($item) && trim($item) !== '') {
                $skills[] = trim($item);
            }
        }
        return array_values(array_unique($skills));
    }

    /**
     * Single LLM round-trip: summary + ATS score + explanation + skills (replaces three separate calls).
     *
     * @return array{summary: string, score: int, explanation: string, skills: list<string>}|null
     */
    public function getResumeAnalysisBundle(string $text): ?array
    {
        $truncated = mb_substr($text, 0, 5500);
        $system = 'You are a resume analyst. Output ONLY one valid JSON object. No markdown, no code fences. '
            . 'Keys: "summary" (string, 4-6 professional sentences, third person: role, top skills, education, profile type), '
            . '"score" (integer 0-100, ATS-style resume quality; most resumes 45-72; strong 73-85), '
            . '"explanation" (string, 3-5 sentences: strengths then actionable improvements), '
            . '"skills" (JSON array of 12-22 short strings: technical first, tools, then 2-4 soft skills).';

        $user = "Analyze this resume. Return ONLY valid JSON of the form:\n"
            . '{"summary":"...","score":0,"explanation":"...","skills":["Skill1","Skill2"]}\n\nResume:\n---\n'
            .$truncated;

        $response = $this->chat([
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user],
        ], 1800);

        if (! $response) {
            return null;
        }

        $decoded = $this->parseJsonObject($response);
        if (! is_array($decoded)) {
            return null;
        }

        $summary = isset($decoded['summary']) ? trim((string) $decoded['summary']) : '';
        $explanation = isset($decoded['explanation']) ? trim((string) $decoded['explanation']) : '';
        if ($summary === '' || $explanation === '' || ! isset($decoded['score']) || ! is_numeric($decoded['score'])) {
            return null;
        }

        $score = max(0, min(100, (int) $decoded['score']));
        $skills = [];
        $skillsRaw = $decoded['skills'] ?? [];
        if (is_array($skillsRaw)) {
            foreach ($skillsRaw as $item) {
                if (is_string($item) && trim($item) !== '') {
                    $skills[] = trim($item);
                }
            }
        }
        $skills = array_values(array_unique($skills));

        return [
            'summary' => $summary,
            'score' => $score,
            'explanation' => $explanation,
            'skills' => $skills,
        ];
    }

    /**
     * Extract rich profile data from resume text for auto-filling candidate profile.
     * Returns flat keys plus arrays: work_experience, education_history, projects, certifications.
     */
    public function extractProfileFromResume(string $text): ?array
    {
        $truncated = mb_substr($text, 0, 12000);
        $system = 'You are a resume parser. Output ONLY one valid JSON object. No markdown, no code fences. '
            . 'Extract from the resume. Use null for unknown scalars; use [] for empty lists. '
            . 'Scalar keys: '
            . '"full_name", "email", "phone", '
            . '"headline" (current job title / role one line), '
            . '"bio_summary" (3-5 line professional summary / about), '
            . '"career_objective" (1-3 sentences goal), '
            . '"education" (short summary of highest qualification, one line), '
            . '"experience_years" (integer total years employed in tech/work, estimate if needed), '
            . '"skills" (comma-separated technical and soft skills, no tools here), '
            . '"tools" (comma-separated tools: Git, Docker, Jira, etc.), '
            . '"technical_skill_level" (one of: Beginner, Intermediate, Expert — overall, or null), '
            . '"location" (City, State, Country), '
            . '"current_company" (current employer name or null), '
            . '"linkedin_url", "github_url", "portfolio_url" (full URLs or null), '
            . '"preferred_job_role", "preferred_job_location", '
            . '"job_type" (Full-time|Part-time|Remote|Hybrid|Contract or null), '
            . '"notice_period" (e.g. "15 days" or null), '
            . '"expected_salary" (if stated), '
            . '"gender" (optional), "date_of_birth" (YYYY-MM-DD if clear, else null). '
            . 'Array keys (max 6 items each): '
            . '"work_experience": [{ "company", "role", "start_date", "end_date", "current" (boolean), "description" }], '
            . '"education_history": [{ "degree", "institution", "field", "start_year", "end_year", "grade" }], '
            . '"projects": [{ "title", "description", "technologies", "link" }], '
            . '"certifications": [{ "name", "issued_by", "year", "link" }]. '
            . 'Dates can be "Jan 2022" or "2022-01". Be accurate; do not invent employers.';

        $user = "Extract all fields from this resume as JSON.\n\nResume:\n---\n".$truncated;

        // Large schema (work/education/projects); default max_tokens would truncate and break JSON.
        $response = $this->chat([
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user],
        ], 4096);

        if (! $response) {
            return null;
        }

        $decoded = $this->parseJsonObject($response);
        if (! is_array($decoded)) {
            $this->logSafe('warning', 'extractProfileFromResume: JSON parse failed', [
                'response_tail' => mb_substr($response, -800),
                'gpt_error' => $this->getLastError(),
            ]);

            return null;
        }

        $str = fn ($v) => $v !== null && $v !== '' ? trim((string) $v) : null;

        $out = [
            'full_name' => $str($decoded['full_name'] ?? null),
            'email' => $str($decoded['email'] ?? null),
            'headline' => $str($decoded['headline'] ?? null),
            'bio_summary' => $str($decoded['bio_summary'] ?? null),
            'career_objective' => $str($decoded['career_objective'] ?? null),
            'education' => $str($decoded['education'] ?? null),
            'experience_years' => isset($decoded['experience_years']) && is_numeric($decoded['experience_years']) ? (int) $decoded['experience_years'] : null,
            'skills' => $str($decoded['skills'] ?? null),
            'tools' => $str($decoded['tools'] ?? null),
            'technical_skill_level' => $str($decoded['technical_skill_level'] ?? null),
            'location' => $str($decoded['location'] ?? null),
            'current_company' => $str($decoded['current_company'] ?? null),
            'linkedin_url' => $str($decoded['linkedin_url'] ?? null),
            'github_url' => $str($decoded['github_url'] ?? null),
            'portfolio_url' => $str($decoded['portfolio_url'] ?? null),
            'preferred_job_role' => $str($decoded['preferred_job_role'] ?? null),
            'preferred_job_location' => $str($decoded['preferred_job_location'] ?? null),
            'job_type' => $str($decoded['job_type'] ?? null),
            'notice_period' => $str($decoded['notice_period'] ?? null),
            'expected_salary' => $str($decoded['expected_salary'] ?? null),
            'phone' => $str($decoded['phone'] ?? null),
            'gender' => $str($decoded['gender'] ?? null),
            'date_of_birth' => $str($decoded['date_of_birth'] ?? null),
            'work_experience' => $this->normalizeResumeExperienceList($decoded['work_experience'] ?? []),
            'education_history' => $this->normalizeResumeEducationList($decoded['education_history'] ?? []),
            'projects' => $this->normalizeResumeProjectsList($decoded['projects'] ?? []),
            'certifications' => $this->normalizeResumeCertList($decoded['certifications'] ?? []),
        ];

        if ($out['email'] !== null && ! filter_var($out['email'], FILTER_VALIDATE_EMAIL)) {
            $out['email'] = null;
        }

        return $out;
    }

    /**
     * @param  mixed  $raw
     * @return list<array<string, mixed>>
     */
    protected function normalizeResumeExperienceList(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }
        $out = [];
        foreach (array_slice($raw, 0, 8) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $out[] = [
                'company' => isset($row['company']) ? mb_substr(trim((string) $row['company']), 0, 255) : '',
                'role' => isset($row['role']) ? mb_substr(trim((string) $row['role']), 0, 255) : '',
                'start_date' => isset($row['start_date']) ? mb_substr(trim((string) $row['start_date']), 0, 64) : '',
                'end_date' => isset($row['end_date']) ? mb_substr(trim((string) $row['end_date']), 0, 64) : '',
                'current' => ! empty($row['current']),
                'description' => isset($row['description']) ? mb_substr(trim((string) $row['description']), 0, 2000) : '',
            ];
        }

        return $out;
    }

    /**
     * @param  mixed  $raw
     * @return list<array<string, mixed>>
     */
    protected function normalizeResumeEducationList(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }
        $out = [];
        foreach (array_slice($raw, 0, 8) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $out[] = [
                'degree' => isset($row['degree']) ? mb_substr(trim((string) $row['degree']), 0, 120) : '',
                'institution' => isset($row['institution']) ? mb_substr(trim((string) $row['institution']), 0, 255) : '',
                'field' => isset($row['field']) ? mb_substr(trim((string) $row['field']), 0, 255) : '',
                'start_year' => isset($row['start_year']) ? mb_substr(trim((string) $row['start_year']), 0, 32) : '',
                'end_year' => isset($row['end_year']) ? mb_substr(trim((string) $row['end_year']), 0, 32) : '',
                'grade' => isset($row['grade']) ? mb_substr(trim((string) $row['grade']), 0, 64) : '',
            ];
        }

        return $out;
    }

    /**
     * @param  mixed  $raw
     * @return list<array<string, mixed>>
     */
    protected function normalizeResumeProjectsList(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }
        $out = [];
        foreach (array_slice($raw, 0, 8) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $out[] = [
                'title' => isset($row['title']) ? mb_substr(trim((string) $row['title']), 0, 255) : '',
                'description' => isset($row['description']) ? mb_substr(trim((string) $row['description']), 0, 2000) : '',
                'technologies' => isset($row['technologies']) ? mb_substr(trim((string) $row['technologies']), 0, 500) : '',
                'link' => isset($row['link']) ? mb_substr(trim((string) $row['link']), 0, 500) : '',
            ];
        }

        return $out;
    }

    /**
     * @param  mixed  $raw
     * @return list<array<string, mixed>>
     */
    protected function normalizeResumeCertList(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }
        $out = [];
        foreach (array_slice($raw, 0, 12) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $out[] = [
                'name' => isset($row['name']) ? mb_substr(trim((string) $row['name']), 0, 255) : '',
                'issued_by' => isset($row['issued_by']) ? mb_substr(trim((string) $row['issued_by']), 0, 255) : '',
                'year' => isset($row['year']) ? mb_substr(trim((string) $row['year']), 0, 32) : '',
                'link' => isset($row['link']) ? mb_substr(trim((string) $row['link']), 0, 500) : '',
            ];
        }

        return $out;
    }

    /**
     * Get resume-to-job match score (0-100) and explanation for employer view.
     * Input: resume text, job title, job description, required skills.
     */
    public function getResumeJobMatchScore(string $resumeText, string $jobTitle, string $jobDescription, array $requiredSkills): ?array
    {
        $resumeTruncated = mb_substr($resumeText, 0, 4000);
        $jobDescTruncated = mb_substr($jobDescription, 0, 1500);
        $skillsList = implode(', ', array_slice($requiredSkills, 0, 25));

        $system = 'You are an HR analyst evaluating how well a candidate resume matches a job role. '
            . 'Output ONLY a valid JSON object. No markdown, no code fences. '
            . 'Keys: "score" (integer 0-100) and "explanation" (string, 2-4 sentences). '
            . 'Score: 0-100 where 70+ = strong match, 50-69 = partial, below 50 = weak. '
            . 'Explanation should be professional and suitable to show to employer: briefly state match strength, key aligned skills, and 1-2 gaps if any.';

        $user = "Resume (excerpt):\n---\n{$resumeTruncated}\n---\n\n"
            . "Job title: {$jobTitle}\n\nJob description (excerpt):\n{$jobDescTruncated}\n\n"
            . "Required skills: {$skillsList}\n\n"
            . 'Give a match score 0-100 and a short explanation for the employer. Output ONLY: {"score": <0-100>, "explanation": "<2-4 sentences>"}';

        $response = $this->chat([
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user],
        ]);

        if (! $response) {
            return null;
        }

        $decoded = $this->parseJsonObject($response);
        if ($decoded !== null && isset($decoded['score'])) {
            $score = (int) $decoded['score'];
            $score = max(0, min(100, $score));
            return [
                'score' => $score,
                'explanation' => isset($decoded['explanation']) ? (string) $decoded['explanation'] : 'Match assessed by AI.',
            ];
        }

        return null;
    }

    /**
     * Extract a JSON object from response (strips markdown code blocks if present).
     */
    protected function parseJsonObject(string $raw): ?array
    {
        $cleaned = $this->stripJsonFromResponse($raw);
        $decoded = json_decode($cleaned, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        $slice = $this->extractBalancedJsonObject($cleaned) ?? $this->extractBalancedJsonObject($raw);
        if ($slice !== null) {
            $decoded = json_decode($slice, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    /**
     * First top-level `{ ... }` block, respecting strings and escapes (handles nested objects).
     */
    protected function extractBalancedJsonObject(string $raw): ?string
    {
        $start = strpos($raw, '{');
        if ($start === false) {
            return null;
        }
        $len = strlen($raw);
        $depth = 0;
        $inString = false;
        $escape = false;
        for ($i = $start; $i < $len; $i++) {
            $c = $raw[$i];
            if ($inString) {
                if ($escape) {
                    $escape = false;
                } elseif ($c === '\\') {
                    $escape = true;
                } elseif ($c === '"') {
                    $inString = false;
                }
            } else {
                if ($c === '"') {
                    $inString = true;
                } elseif ($c === '{') {
                    $depth++;
                } elseif ($c === '}') {
                    $depth--;
                    if ($depth === 0) {
                        return substr($raw, $start, $i - $start + 1);
                    }
                }
            }
        }

        return null;
    }

    /**
     * Extract a JSON array from response (strips markdown code blocks if present).
     */
    protected function parseJsonArray(string $raw): ?array
    {
        $cleaned = $this->stripJsonFromResponse($raw);
        $decoded = json_decode($cleaned, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        if (preg_match('/\[[^\[\]]*(?:\[[^\[\]]*\][^\[\]]*)*\]/s', $raw, $m)) {
            $decoded = json_decode($m[0], true);
            return is_array($decoded) ? $decoded : null;
        }
        return null;
    }

    /**
     * Remove markdown code blocks and trim so we get raw JSON.
     */
    protected function stripJsonFromResponse(string $response): string
    {
        $trimmed = trim($response);
        if (preg_match('/^```(?:json)?\s*([\s\S]*?)```\s*$/s', $trimmed, $m)) {
            return trim($m[1]);
        }
        return $trimmed;
    }

    protected function chat(array $messages, ?int $maxTokensOverride = null): ?string
    {
        $this->lastError = null;
        $maxTokens = $maxTokensOverride ?? $this->maxTokens;
        $trail = [];
        $bedrockTried = false;

        if ($this->shouldUseBedrockFirst()) {
            $bedrockTried = true;
            $bedrock = $this->requestBedrock($messages, $maxTokens);
            if ($bedrock !== null && $bedrock !== '') {
                return $bedrock;
            }
            $trail[] = 'Bedrock: '.($this->lastError ?? 'failed');
        }

        if (! empty($this->openAiApiKey)) {
            $content = $this->requestChatCompletion(
                'https://api.openai.com/v1',
                $this->openAiApiKey,
                $this->openAiModel,
                $messages,
                $maxTokens,
                isOpenRouter: false
            );
            if ($content !== null && $content !== '') {
                return $content;
            }
            $trail[] = 'OpenAI: '.($this->lastError ?? 'failed');
        }

        $openRouterWouldRun = ! empty($this->primaryApiKey)
            && ! $this->shouldSkipOpenRouterPrimary()
            && ! $this->shouldSkipOpenRouterFreeByPolicy();

        $openRouterPrimaryAttempted = false;
        if ($openRouterWouldRun) {
            $openRouterPrimaryAttempted = true;
            $content = $this->requestChatCompletion(
                $this->primaryBaseUrl,
                $this->primaryApiKey,
                $this->primaryModel,
                $messages,
                $maxTokens,
                isOpenRouter: str_contains($this->primaryBaseUrl, 'openrouter.ai')
            );
            if ($content !== null && $content !== '') {
                return $content;
            }
            $trail[] = 'OpenRouter ('.$this->primaryModel.'): '.($this->lastError ?? 'failed');
        } elseif (! empty($this->primaryApiKey)) {
            if ($this->shouldSkipOpenRouterPrimary()) {
                $trail[] = 'OpenRouter: cooling down after recent rate limits; try again in a minute.';
            } elseif ($this->shouldSkipOpenRouterFreeByPolicy()) {
                $trail[] = 'OpenRouter skipped: with OPENAI_API_KEY set, :free OpenRouter models are skipped. Use OpenAI, set OPENROUTER_SKIP_FREE_WHEN_OPENAI=false, or use a paid OpenRouter model.';
            }
        }

        $fallbackModel = trim((string) config('services.primary_llm.model_fallback', ''));
        if ($openRouterPrimaryAttempted
            && $fallbackModel !== ''
            && $fallbackModel !== $this->primaryModel
            && ! empty($this->primaryApiKey)
            && str_contains($this->primaryBaseUrl, 'openrouter.ai')) {
            $errLower = mb_strtolower($this->lastError ?? '');
            $looks429 = str_contains($errLower, '429')
                || str_contains($errLower, 'busy')
                || str_contains($errLower, 'rate limit');
            if ($looks429) {
                $this->logSafe('info', 'OpenRouter primary failed with overload; trying fallback model', [
                    'fallback' => $fallbackModel,
                ]);
                $fb = $this->requestChatCompletion(
                    $this->primaryBaseUrl,
                    $this->primaryApiKey,
                    $fallbackModel,
                    $messages,
                    $maxTokens,
                    isOpenRouter: true
                );
                if ($fb !== null && $fb !== '') {
                    return $fb;
                }
                $trail[] = 'OpenRouter fallback ('.$fallbackModel.'): '.($this->lastError ?? 'failed');
            }
        }

        if ($this->isBedrockConfigured() && ! $bedrockTried) {
            $bedrock = $this->requestBedrock($messages, $maxTokens);
            if ($bedrock !== null && $bedrock !== '') {
                return $bedrock;
            }
            $trail[] = 'Bedrock: '.($this->lastError ?? 'failed');
        }

        if ($trail === []) {
            $this->lastError = 'No AI API keys configured. Set AWS IAM credentials (or BEDROCK_USE_IAM=false + AWS_BEARER_TOKEN_BEDROCK), OPENAI_API_KEY, and/or OPENROUTER_API_KEY in .env.';
        } else {
            $this->lastError = implode(' ', $trail);
        }

        return null;
    }

    protected function shouldUseBedrockFirst(): bool
    {
        if (! (bool) config('services.bedrock.try_first', true)) {
            return false;
        }

        return $this->isBedrockConfigured();
    }

    protected function usesBedrockIam(): bool
    {
        return $this->bedrockUseIam;
    }

    protected function isBedrockConfigured(): bool
    {
        if ($this->usesBedrockIam()) {
            if (! empty($this->bedrockAccessKey) && ! empty($this->bedrockSecretKey)) {
                return true;
            }

            return (bool) config('services.bedrock.allow_default_credential_chain', false);
        }

        return ! empty($this->bedrockBearer);
    }

    /**
     * @return list<string>
     */
    protected function bedrockModelIdsToTry(): array
    {
        $primary = $this->bedrockModelId;
        $ids = [$primary];
        $fallback = config('services.bedrock.model_id_fallback');
        if (is_string($fallback) && $fallback !== '' && $fallback !== $primary) {
            $ids[] = $fallback;
        }

        return array_values(array_unique($ids));
    }

    /**
     * Bedrock: IAM (SDK) preferred; optional bearer HTTP when BEDROCK_USE_IAM=false.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    protected function requestBedrock(array $messages, int $maxTokens): ?string
    {
        $built = $this->buildBedrockMessagesPayload($messages);
        if ($built === null) {
            return null;
        }
        ['systemBlocks' => $systemBlocks, 'convMessages' => $convMessages] = $built;

        if ($this->usesBedrockIam()) {
            return $this->requestBedrockWithSdk($convMessages, $systemBlocks, $maxTokens);
        }

        if (! empty($this->bedrockBearer)) {
            return $this->requestBedrockWithBearer($convMessages, $systemBlocks, $maxTokens);
        }

        return null;
    }

    protected function makeBedrockRuntimeClient(): BedrockRuntimeClient
    {
        $region = preg_replace('/[^a-z0-9-]/i', '', $this->bedrockRegion) ?: 'us-east-1';
        $params = [
            'version' => 'latest',
            'region' => $region,
            'http' => [
                'timeout' => $this->timeout,
                'connect_timeout' => $this->connectTimeout,
            ],
        ];
        if (! empty($this->bedrockAccessKey) && ! empty($this->bedrockSecretKey)) {
            $params['credentials'] = [
                'key' => $this->bedrockAccessKey,
                'secret' => $this->bedrockSecretKey,
            ];
        }

        return new BedrockRuntimeClient($params);
    }

    /**
     * @param  list<array<string, mixed>>  $convMessages
     * @param  list<array{text: string}>  $systemBlocks
     */
    protected function requestBedrockWithSdk(array $convMessages, array $systemBlocks, int $maxTokens): ?string
    {
        try {
            $client = $this->makeBedrockRuntimeClient();
        } catch (\Throwable $e) {
            $this->lastError = 'Bedrock SDK client: '.$e->getMessage();

            return null;
        }

        $lastErr = null;
        foreach ($this->bedrockModelIdsToTry() as $modelId) {
            $text = $this->requestBedrockConverseSdk($client, $modelId, $convMessages, $systemBlocks, $maxTokens);
            if ($text !== null && $text !== '') {
                return $text;
            }
            $lastErr = $this->lastError;
            $text = $this->requestBedrockInvokeSdk($client, $modelId, $convMessages, $systemBlocks, $maxTokens);
            if ($text !== null && $text !== '') {
                return $text;
            }
            $lastErr = $this->lastError;
        }
        $this->lastError = $lastErr ?? 'Bedrock: all model IDs failed';

        return null;
    }

    /**
     * @param  list<array<string, mixed>>  $convMessages
     * @param  list<array{text: string}>  $systemBlocks
     */
    protected function requestBedrockWithBearer(array $convMessages, array $systemBlocks, int $maxTokens): ?string
    {
        $lastErr = null;
        foreach ($this->bedrockModelIdsToTry() as $modelId) {
            $text = $this->requestBedrockConverseRequest($modelId, $convMessages, $systemBlocks, $maxTokens);
            if ($text !== null && $text !== '') {
                return $text;
            }
            $lastErr = $this->lastError;
            $text = $this->requestBedrockInvokeRequest($modelId, $convMessages, $systemBlocks, $maxTokens);
            if ($text !== null && $text !== '') {
                return $text;
            }
            $lastErr = $this->lastError;
        }
        $this->lastError = $lastErr ?? 'Bedrock: all model IDs failed';

        return null;
    }

    /**
     * @param  list<array<string, mixed>>  $convMessages
     * @param  list<array{text: string}>  $systemBlocks
     */
    protected function requestBedrockConverseSdk(
        BedrockRuntimeClient $client,
        string $modelId,
        array $convMessages,
        array $systemBlocks,
        int $maxTokens
    ): ?string {
        $this->lastError = null;
        $params = [
            'modelId' => $modelId,
            'messages' => $convMessages,
            'inferenceConfig' => [
                'maxTokens' => min(max(1, $maxTokens), 8192),
                'temperature' => 0.3,
            ],
        ];
        if ($systemBlocks !== []) {
            $params['system'] = $systemBlocks;
        }

        try {
            $result = $client->converse($params);
            $data = $result->toArray();
            $text = $this->extractTextFromBedrockResponse($data);
            if ($text !== null && $text !== '') {
                return $text;
            }
            $this->lastError = 'Bedrock Converse: empty or unexpected response shape';

            return null;
        } catch (AwsException $e) {
            $this->lastError = 'Bedrock Converse: '.$e->getAwsErrorMessage();
            $this->logSafe('info', 'Bedrock Converse failed (IAM); will try Invoke', [
                'code' => $e->getAwsErrorCode(),
                'model_id' => $modelId,
            ]);

            return null;
        } catch (\Throwable $e) {
            $this->lastError = 'Bedrock Converse: '.$e->getMessage();
            $this->logSafe('info', 'Bedrock Converse exception (IAM); will try Invoke', [
                'message' => $e->getMessage(),
                'model_id' => $modelId,
            ]);

            return null;
        }
    }

    /**
     * @param  list<array<string, mixed>>  $convMessages
     * @param  list<array{text: string}>  $systemBlocks
     */
    protected function requestBedrockInvokeSdk(
        BedrockRuntimeClient $client,
        string $modelId,
        array $convMessages,
        array $systemBlocks,
        int $maxTokens
    ): ?string {
        $this->lastError = null;
        $payload = [
            'schemaVersion' => 'messages-v1',
            'messages' => $convMessages,
            'inferenceConfig' => [
                'maxTokens' => min(max(1, $maxTokens), 8192),
                'temperature' => 0.3,
            ],
        ];
        if ($systemBlocks !== []) {
            $payload['system'] = $systemBlocks;
        }

        $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE);
        if ($encoded === false) {
            $this->lastError = 'Bedrock Invoke: could not encode request body';

            return null;
        }

        try {
            $result = $client->invokeModel([
                'modelId' => $modelId,
                'contentType' => 'application/json',
                'accept' => 'application/json',
                'body' => $encoded,
            ]);
            $body = $result['body'] ?? null;
            $raw = $body instanceof \Psr\Http\Message\StreamInterface ? $body->getContents() : (string) $body;
            $data = json_decode($raw, true);
            if (! is_array($data)) {
                $this->lastError = 'Bedrock Invoke: invalid JSON body';

                return null;
            }
            $text = $this->extractTextFromBedrockResponse($data);
            if ($text !== null && $text !== '') {
                return $text;
            }
            $this->lastError = 'Bedrock Invoke: empty or unexpected response';

            return null;
        } catch (AwsException $e) {
            $this->lastError = 'Bedrock Invoke: '.$e->getAwsErrorMessage();
            $this->logSafe('warning', 'Bedrock Invoke failed (IAM)', [
                'code' => $e->getAwsErrorCode(),
                'model_id' => $modelId,
            ]);

            return null;
        } catch (\Throwable $e) {
            $this->lastError = 'Bedrock Invoke: '.$e->getMessage();
            $this->logSafe('warning', 'Bedrock Invoke exception (IAM)', [
                'message' => $e->getMessage(),
                'model_id' => $modelId,
            ]);

            return null;
        }
    }

    /**
     * @return array{systemBlocks: list<array{text: string}>, convMessages: list<array<string, mixed>>}|null
     */
    protected function buildBedrockMessagesPayload(array $messages): ?array
    {
        $systemBlocks = [];
        $convMessages = [];
        foreach ($messages as $m) {
            $role = isset($m['role']) ? (string) $m['role'] : 'user';
            $content = $m['content'] ?? '';
            if (! is_string($content)) {
                $content = is_array($content) ? json_encode($content) : (string) $content;
            }
            if ($role === 'system') {
                if (trim($content) !== '') {
                    $systemBlocks[] = ['text' => $content];
                }

                continue;
            }
            $brRole = $role === 'assistant' ? 'assistant' : 'user';
            $convMessages[] = [
                'role' => $brRole,
                'content' => [['text' => $content]],
            ];
        }

        if ($convMessages === []) {
            $this->lastError = 'Bedrock: no user/assistant messages to send';

            return null;
        }

        return ['systemBlocks' => $systemBlocks, 'convMessages' => $convMessages];
    }

    /**
     * @param  list<array<string, mixed>>  $convMessages
     * @param  list<array{text: string}>  $systemBlocks
     */
    protected function requestBedrockConverseRequest(string $modelId, array $convMessages, array $systemBlocks, int $maxTokens): ?string
    {
        $this->lastError = null;
        $encodedModel = rawurlencode($modelId);
        $region = preg_replace('/[^a-z0-9-]/i', '', $this->bedrockRegion) ?: 'us-east-1';
        $url = "https://bedrock-runtime.{$region}.amazonaws.com/model/{$encodedModel}/converse";

        $body = [
            'messages' => $convMessages,
            'inferenceConfig' => [
                'maxTokens' => min(max(1, $maxTokens), 8192),
                'temperature' => 0.3,
            ],
        ];
        if ($systemBlocks !== []) {
            $body['system'] = $systemBlocks;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->bedrockBearer,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
                ->connectTimeout($this->connectTimeout)
                ->timeout($this->timeout)
                ->post($url, $body);

            if (! $response->successful()) {
                $this->lastError = 'Bedrock Converse HTTP '.$response->status().': '.mb_substr($response->body(), 0, 300);
                $this->logSafe('info', 'Bedrock Converse failed; will try Invoke', [
                    'status' => $response->status(),
                    'model_id' => $modelId,
                ]);

                return null;
            }

            $data = $response->json();
            $text = $this->extractTextFromBedrockResponse($data);
            if ($text === null || $text === '') {
                $this->lastError = 'Bedrock Converse: empty or unexpected response shape';

                return null;
            }

            return $text;
        } catch (\Throwable $e) {
            $this->lastError = 'Bedrock Converse: '.$e->getMessage();
            $this->logSafe('info', 'Bedrock Converse exception; will try Invoke', [
                'message' => $e->getMessage(),
                'model_id' => $modelId,
            ]);

            return null;
        }
    }

    /**
     * Amazon Nova / messages-v1 InvokeModel (works with Bedrock API keys that disallow Converse).
     *
     * @param  list<array<string, mixed>>  $convMessages
     * @param  list<array{text: string}>  $systemBlocks
     */
    protected function requestBedrockInvokeRequest(string $modelId, array $convMessages, array $systemBlocks, int $maxTokens): ?string
    {
        $this->lastError = null;
        $encodedModel = rawurlencode($modelId);
        $region = preg_replace('/[^a-z0-9-]/i', '', $this->bedrockRegion) ?: 'us-east-1';
        $url = "https://bedrock-runtime.{$region}.amazonaws.com/model/{$encodedModel}/invoke";

        $body = [
            'schemaVersion' => 'messages-v1',
            'messages' => $convMessages,
            'inferenceConfig' => [
                'maxTokens' => min(max(1, $maxTokens), 8192),
                'temperature' => 0.3,
            ],
        ];
        if ($systemBlocks !== []) {
            $body['system'] = $systemBlocks;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->bedrockBearer,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
                ->connectTimeout($this->connectTimeout)
                ->timeout($this->timeout)
                ->post($url, $body);

            if (! $response->successful()) {
                $this->lastError = 'Bedrock Invoke HTTP '.$response->status().': '.mb_substr($response->body(), 0, 300);
                $this->logSafe('warning', 'Bedrock Invoke failed', [
                    'status' => $response->status(),
                    'model_id' => $modelId,
                    'body' => mb_substr($response->body(), 0, 500),
                ]);

                return null;
            }

            $data = $response->json();
            $text = $this->extractTextFromBedrockResponse($data);
            if ($text === null || $text === '') {
                $this->lastError = 'Bedrock Invoke: empty or unexpected response';

                return null;
            }

            return $text;
        } catch (\Throwable $e) {
            $this->lastError = 'Bedrock Invoke: '.$e->getMessage();
            $this->logSafe('warning', 'Bedrock Invoke exception', [
                'message' => $e->getMessage(),
                'model_id' => $modelId,
            ]);

            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function extractTextFromBedrockResponse(?array $data): ?string
    {
        if ($data === null) {
            return null;
        }

        $blocks = $data['output']['message']['content'] ?? null;
        if (is_array($blocks)) {
            $textOut = $this->concatBedrockContentBlocks($blocks);
            if ($textOut !== '') {
                return $textOut;
            }
        }

        $blocks = $data['output']['content'] ?? null;
        if (is_array($blocks)) {
            $textOut = $this->concatBedrockContentBlocks($blocks);
            if ($textOut !== '') {
                return $textOut;
            }
        }

        if (isset($data['results'][0]['outputText']) && is_string($data['results'][0]['outputText'])) {
            return trim($data['results'][0]['outputText']);
        }

        if (isset($data['completion']) && is_string($data['completion'])) {
            return trim($data['completion']);
        }

        return null;
    }

    /**
     * @param  list<mixed>  $blocks
     */
    protected function concatBedrockContentBlocks(array $blocks): string
    {
        $textOut = '';
        foreach ($blocks as $block) {
            if (is_array($block) && isset($block['text']) && is_string($block['text'])) {
                $textOut .= $block['text'];
            }
        }

        return trim($textOut);
    }

    /**
     * OpenAI-compatible chat completions (OpenAI, OpenRouter, etc.).
     */
    protected function requestChatCompletion(
        string $baseUrl,
        string $apiKey,
        string $model,
        array $messages,
        int $maxTokens,
        bool $isOpenRouter
    ): ?string {
        $this->lastError = null;
        $url = rtrim($baseUrl, '/').'/chat/completions';
        $retry429 = $isOpenRouter && (bool) config('services.primary_llm.retry_on_429', true);
        $configuredAttempts = (int) config('services.primary_llm.openrouter_429_max_attempts', 5);
        $max429Attempts = $retry429
            ? max(2, min(8, $configuredAttempts))
            : 1;
        $maxTimeoutAttempts = 2;

        for ($timeoutRound = 0; $timeoutRound < $maxTimeoutAttempts; $timeoutRound++) {
            if ($timeoutRound > 0) {
                usleep(2_000_000);
                $this->logSafe('info', 'Chat completion retry after HTTP timeout', [
                    'url' => $url,
                    'model' => $model,
                    'round' => $timeoutRound + 1,
                ]);
            }

            try {
                $client = Http::withToken($apiKey)
                    ->connectTimeout($this->connectTimeout)
                    ->timeout($this->timeout)
                    ->acceptJson();
                if ($isOpenRouter) {
                    $client = $client->withHeaders([
                        'HTTP-Referer' => (string) config('services.primary_llm.http_referer', config('app.url', '')),
                        'X-Title' => (string) config('services.primary_llm.app_title', config('app.name', 'App')),
                    ]);
                }

                for ($attempt = 1; $attempt <= $max429Attempts; $attempt++) {
                    if ($attempt > 1) {
                        // 1s, 2s, 4s… capped (configurable; lower cap = faster retries).
                        $cap = max(1, min(15, (int) config('services.primary_llm.openrouter_429_delay_cap_seconds', 4)));
                        $delaySec = min($cap, 2 ** ($attempt - 2));
                        usleep($delaySec * 1_000_000);
                        $this->logSafe('info', 'OpenRouter 429 retry', [
                            'model' => $model,
                            'attempt' => $attempt,
                            'delay_sec' => $delaySec,
                        ]);
                    }

                    $response = $client->post($url, [
                        'model' => $model,
                        'messages' => $messages,
                        'max_tokens' => $maxTokens,
                        'temperature' => 0.3,
                    ]);

                    if ($response->successful()) {
                        if ($isOpenRouter) {
                            $this->clearOpenRouterCircuitCache();
                        }
                        $data = $response->json();
                        $content = $data['choices'][0]['message']['content'] ?? null;
                        if (! is_string($content) || trim($content) === '') {
                            $this->lastError = 'Empty model response';

                            return null;
                        }

                        return trim($content);
                    }

                    $status = $response->status();
                    $body = $response->json();
                    $message = null;
                    if (is_array($body) && isset($body['error'])) {
                        $err = $body['error'];
                        $message = is_array($err) ? ($err['message'] ?? $err['code'] ?? null) : (string) $err;
                    }
                    $label = $isOpenRouter ? 'OpenRouter' : 'OpenAI';

                    if ($isOpenRouter && $status === 429 && $attempt < $max429Attempts) {
                        continue;
                    }

                    if ($status === 429) {
                        if ($isOpenRouter) {
                            $this->markOpenRouterCircuitOpenForRequest();
                        }
                        $this->lastError = $isOpenRouter
                            ? 'OpenRouter free/upstream model is temporarily busy (429). Try OpenAI/Bedrock or retry shortly.'
                            : $label.' rate limit reached. Try again later or check your plan.';
                    } else {
                        $this->lastError = $label.' API error ('.$status.'): '.(is_string($message) && $message !== '' ? $message : substr($response->body(), 0, 200));
                    }
                    $failLevel = ($isOpenRouter && $status === 429) ? 'info' : 'warning';
                    $this->logSafe($failLevel, $label.' chat completion failed', ['status' => $status, 'body' => $response->body()]);

                    return null;
                }

                return null;
            } catch (\Throwable $e) {
                if ($this->isLikelyHttpTimeout($e) && $timeoutRound < $maxTimeoutAttempts - 1) {
                    continue;
                }
                $this->lastError = 'Request failed: '.$e->getMessage();
                $this->logSafe('warning', 'Chat completion request failed', ['message' => $e->getMessage(), 'url' => $url]);

                return null;
            }
        }

        return null;
    }

    protected function isLikelyHttpTimeout(\Throwable $e): bool
    {
        $m = $e->getMessage();

        return str_contains($m, 'cURL error 28')
            || str_contains($m, 'Operation timed out')
            || str_contains($m, 'timed out after');
    }

    /**
     * Skip OpenRouter after a 429 for the rest of this request and/or cache TTL (many LLM calls per upload).
     */
    protected function shouldSkipOpenRouterPrimary(): bool
    {
        if ((bool) config('services.primary_llm.circuit_on_429', true)) {
            $req = $this->currentRequest();
            if ($req !== null && $req->attributes->get('hirevo_openrouter_skip') === true) {
                return true;
            }
        }

        if ((bool) config('services.primary_llm.circuit_cache', true)) {
            try {
                return Cache::has($this->openRouterCircuitCacheKey());
            } catch (\Throwable) {
                return false;
            }
        }

        return false;
    }

    protected function markOpenRouterCircuitOpenForRequest(): void
    {
        if ((bool) config('services.primary_llm.circuit_on_429', true)) {
            $req = $this->currentRequest();
            if ($req !== null) {
                $req->attributes->set('hirevo_openrouter_skip', true);
            }
        }

        if ((bool) config('services.primary_llm.circuit_cache', true)) {
            try {
                $ttl = max(30, (int) config('services.primary_llm.circuit_cache_ttl', 120));
                Cache::put($this->openRouterCircuitCacheKey(), true, $ttl);
            } catch (\Throwable) {
            }
        }
    }

    protected function clearOpenRouterCircuitCache(): void
    {
        if (! (bool) config('services.primary_llm.circuit_cache', true)) {
            return;
        }
        try {
            Cache::forget($this->openRouterCircuitCacheKey());
        } catch (\Throwable) {
        }
    }

    protected function openRouterCircuitCacheKey(): string
    {
        $keyMaterial = ($this->primaryApiKey ?? '').'|'.($this->primaryModel ?? '');

        return 'hirevo.openrouter.429.'.hash('sha256', $keyMaterial);
    }

    protected function currentRequest(): ?Request
    {
        try {
            if (! app()->runningInConsole()) {
                $r = request();

                return $r instanceof Request ? $r : null;
            }
        } catch (\Throwable) {
        }

        return null;
    }

    /**
     * OpenRouter ":free" models are often 429 upstream; if OpenAI is configured, use it directly.
     */
    protected function shouldSkipOpenRouterFreeByPolicy(): bool
    {
        if (! (bool) config('services.primary_llm.skip_free_when_openai', true)) {
            return false;
        }
        if (empty($this->openAiApiKey)) {
            return false;
        }
        if (! str_contains($this->primaryBaseUrl, 'openrouter.ai')) {
            return false;
        }

        return str_contains($this->primaryModel, ':free');
    }

    /**
     * Avoid breaking requests when logging is misconfigured (e.g. empty LOG_CHANNEL).
     */
    protected function logSafe(string $level, string $message, array $context = []): void
    {
        try {
            match ($level) {
                'debug' => Log::debug($message, $context),
                'info' => Log::info($message, $context),
                'warning' => Log::warning($message, $context),
                default => Log::info($message, $context),
            };
        } catch (\Throwable) {
            error_log('[hirevo] '.$message.' '.json_encode($context, JSON_UNESCAPED_UNICODE));
        }
    }
}
