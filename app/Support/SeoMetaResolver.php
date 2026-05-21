<?php

namespace App\Support;

use App\Models\EmployerJob;
use App\Models\JobRole;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SeoMetaResolver
{
    public function resolve(Request $request): array
    {
        $route = $request->route();
        $routeName = $route?->getName();
        $canonical = $this->canonicalUrl($request);
        $robots = $this->robotsDirective($routeName);
        $ogImage = asset(config('seo.default_og_image'));

        $title = config('seo.site_name');
        $description = config('seo.default_description');
        $ogType = 'website';
        $structured = [];

        if ($routeName && isset(config('seo.static_pages')[$routeName])) {
            $page = config('seo.static_pages')[$routeName];
            $title = $page['title'] ?? $title;
            $description = $page['description'] ?? $description;
        }

        if ($routeName === 'job-goal.show' && ($jobRole = $route?->parameter('jobRole')) instanceof JobRole) {
            $title = $jobRole->title . ' — Skill Match & Job Goal';
            $description = $this->truncateMeta(
                'See required skills, your match score, and related openings for ' . $jobRole->title . ' on Hirevo.'
            );
            $structured[] = $this->breadcrumbSchema([
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Job Goals', 'url' => route('job-list')],
                ['name' => $jobRole->title, 'url' => $canonical],
            ]);
        }

        if ($routeName === 'job-openings.apply' && ($job = $route?->parameter('job')) instanceof EmployerJob) {
            $title = 'Apply — ' . $job->title;
            $company = $job->company_name ?: $job->user?->referrerProfile?->company_name;
            $description = $this->truncateMeta(
                'Apply to ' . $job->title . ($company ? ' at ' . $company : '') . '. Submit your Hirevo profile and resume.'
            );
            $structured[] = $this->jobPostingSchema($job, $canonical);
            $structured[] = $this->breadcrumbSchema([
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Job Openings', 'url' => route('job-openings')],
                ['name' => $job->title, 'url' => $canonical],
            ]);
        }

        if ($routeName === 'job-goal.apply' && ($jobRole = $route?->parameter('jobRole')) instanceof JobRole) {
            $title = 'Apply — ' . $jobRole->title;
            $description = $this->truncateMeta('Apply to the ' . $jobRole->title . ' job goal on Hirevo.');
        }

        if ($routeName === 'faq') {
            $structured[] = $this->faqPageSchema();
        }

        if ($robots === 'index, follow' && $routeName && ! in_array($routeName, ['home', 'faq'], true)) {
            $crumbs = $this->defaultBreadcrumbs($routeName, $canonical, $request);
            if ($crumbs !== []) {
                $structured[] = $this->breadcrumbSchema($crumbs);
            }
        }

        $structured[] = $this->organizationSchema();
        $structured[] = $this->webSiteSchema();

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'robots' => $robots,
            'og_type' => $ogType,
            'og_image' => $ogImage,
            'og_url' => $canonical,
            'structured_data' => array_values(array_filter($structured)),
        ];
    }

    protected function canonicalUrl(Request $request): string
    {
        $ignore = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'fbclid', 'ref'];
        $query = collect($request->query())
            ->except($ignore)
            ->filter(fn ($v) => $v !== null && $v !== '')
            ->all();

        $url = url('/' . ltrim($request->path(), '/'));

        return $query === [] ? $url : $url . '?' . http_build_query($query);
    }

    protected function robotsDirective(?string $routeName): string
    {
        if ($routeName === null) {
            return 'noindex, nofollow';
        }

        foreach (config('seo.noindex_route_prefixes', []) as $prefix) {
            if ($routeName === $prefix || str_starts_with($routeName, $prefix)) {
                return 'noindex, nofollow';
            }
        }

        if (str_contains($routeName, 'admin')) {
            return 'noindex, nofollow';
        }

        return 'index, follow';
    }

    protected function truncateMeta(string $text, int $max = 160): string
    {
        $text = trim(preg_replace('/\s+/', ' ', $text) ?? $text);

        return Str::length($text) <= $max ? $text : Str::substr($text, 0, $max - 1) . '…';
    }

    protected function organizationSchema(): array
    {
        $org = config('seo.organization');
        $base = rtrim(config('app.url'), '/');

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $org['name'] ?? config('seo.site_name'),
            'url' => $org['url'] ?? $base,
            'logo' => asset($org['logo'] ?? config('seo.default_og_image')),
        ];

        if (! empty($org['email'])) {
            $data['email'] = $org['email'];
        }
        if (! empty($org['same_as'])) {
            $data['sameAs'] = $org['same_as'];
        }

        return $data;
    }

    protected function webSiteSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => config('seo.site_name'),
            'url' => rtrim(config('app.url'), '/'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => route('job-openings') . '?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    protected function breadcrumbSchema(array $items): array
    {
        $list = [];
        foreach ($items as $i => $item) {
            $list[] = [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item['name'],
                'item' => $item['url'],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $list,
        ];
    }

    protected function faqPageSchema(): array
    {
        $entities = [];
        foreach (config('seo.faq_schema', []) as $row) {
            $entities[] = [
                '@type' => 'Question',
                'name' => $row['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $row['answer'],
                ],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $entities,
        ];
    }

    protected function jobPostingSchema(EmployerJob $job, string $url): array
    {
        $company = $job->company_name ?: $job->user?->referrerProfile?->company_name ?: config('seo.site_name');

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'JobPosting',
            'title' => $job->title,
            'description' => $this->truncateMeta(strip_tags((string) ($job->description ?? $job->title)), 5000),
            'datePosted' => optional($job->created_at)->toAtomString(),
            'validThrough' => optional($job->updated_at)->addMonths(3)->toAtomString(),
            'employmentType' => $this->schemaEmploymentType($job->job_type),
            'hiringOrganization' => [
                '@type' => 'Organization',
                'name' => $company,
            ],
            'url' => $url,
            'directApply' => true,
        ];

        if ($job->formatted_location) {
            $data['jobLocation'] = [
                '@type' => 'Place',
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressLocality' => $job->formatted_location,
                ],
            ];
        }

        return $data;
    }

    protected function schemaEmploymentType(?string $jobType): string
    {
        return match ($jobType) {
            'part_time' => 'PART_TIME',
            'contract' => 'CONTRACTOR',
            'internship' => 'INTERN',
            'temporary' => 'TEMPORARY',
            'volunteer' => 'VOLUNTEER',
            default => 'FULL_TIME',
        };
    }

    protected function defaultBreadcrumbs(string $routeName, string $canonical, Request $request): array
    {
        $home = ['name' => 'Home', 'url' => route('home')];
        $map = [
            'job-list' => [['name' => 'Job Goals', 'url' => $canonical]],
            'job-openings' => [['name' => 'Job Openings', 'url' => $canonical]],
            'pricing' => [['name' => 'Pricing', 'url' => $canonical]],
            'about' => [['name' => 'About', 'url' => $canonical]],
            'contact' => [['name' => 'Contact', 'url' => $canonical]],
            'faq' => [['name' => 'FAQ', 'url' => $canonical]],
            'help' => [['name' => 'Help', 'url' => $canonical]],
            'resume.upload' => [['name' => 'Resume Score', 'url' => $canonical]],
        ];

        if (! isset($map[$routeName])) {
            return [];
        }

        return array_merge([$home], $map[$routeName]);
    }
}
