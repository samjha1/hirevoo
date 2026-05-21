<?php

namespace App\Http\Controllers;

use App\Models\JobRole;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

class SeoController extends Controller
{
    public function robots(): Response
    {
        $base = rtrim(config('app.url'), '/');
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /theme/',
            'Disallow: /sign-in',
            'Disallow: /sign-up',
            'Disallow: /forgot-password',
            'Disallow: /reset-password',
            'Disallow: /set-password',
            'Disallow: /verify-email',
            'Disallow: /auth/',
            'Disallow: /dashboard',
            'Disallow: /profile',
            'Disallow: /employer/',
            'Disallow: /resume/',
            'Sitemap: ' . $base . '/sitemap.xml',
            '',
        ];

        return response(implode("\n", $lines), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function sitemap(): Response
    {
        $minutes = max(5, (int) config('seo.sitemap_cache_minutes', 60));
        $xml = Cache::remember('hirevo.sitemap.xml', now()->addMinutes($minutes), function () {
            return $this->buildSitemapXml();
        });

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=' . ($minutes * 60),
        ]);
    }

    protected function buildSitemapXml(): string
    {
        $urls = [];
        $now = now()->toAtomString();

        $static = [
            'home' => ['/', 'daily', '1.0'],
            'job-list' => ['/job-list', 'daily', '0.9'],
            'job-openings' => ['/job-openings', 'daily', '0.9'],
            'resume.upload' => ['/resume/upload', 'weekly', '0.9'],
            'pricing' => ['/pricing', 'weekly', '0.8'],
            'about' => ['/about', 'monthly', '0.7'],
            'contact' => ['/contact', 'monthly', '0.7'],
            'faq' => ['/faq', 'monthly', '0.7'],
            'help' => ['/help', 'monthly', '0.6'],
            'terms' => ['/terms', 'yearly', '0.3'],
            'privacy' => ['/privacy', 'yearly', '0.3'],
            'cookies' => ['/cookies', 'yearly', '0.3'],
            'disclaimer' => ['/disclaimer', 'yearly', '0.3'],
        ];

        foreach ($static as $routeName => [$path, $freq, $priority]) {
            if (Route::has($routeName)) {
                $urls[] = $this->urlEntry(route($routeName), $now, $freq, $priority);
            }
        }

        JobRole::where('is_active', true)->orderBy('title')->chunk(100, function ($roles) use (&$urls, $now) {
            foreach ($roles as $role) {
                if (Route::has('job-goal.show')) {
                    $urls[] = $this->urlEntry(
                        route('job-goal.show', $role),
                        optional($role->updated_at)->toAtomString() ?? $now,
                        'weekly',
                        '0.8'
                    );
                }
            }
        });

        $body = implode("\n", $urls);

        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n"
            . $body . "\n"
            . '</urlset>';
    }

    protected function urlEntry(string $loc, string $lastmod, string $changefreq, string $priority): string
    {
        return '  <url>'
            . '<loc>' . htmlspecialchars($loc, ENT_XML1) . '</loc>'
            . '<lastmod>' . htmlspecialchars($lastmod, ENT_XML1) . '</lastmod>'
            . '<changefreq>' . $changefreq . '</changefreq>'
            . '<priority>' . $priority . '</priority>'
            . '</url>';
    }
}
