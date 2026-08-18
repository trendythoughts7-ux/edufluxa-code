<?php

namespace App\Console\Commands;

use App\Models\Blog;
use App\Models\Category;
use App\Models\Webinar;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate public/sitemap.xml from static pages and live database content';

    public function handle()
    {
        $urls = [];

        // Static pages
        $static = [
            ['loc' => '/', 'priority' => '1.00'],
            ['loc' => '/login', 'priority' => '0.80'],
            ['loc' => '/register', 'priority' => '0.80'],
            ['loc' => '/courses', 'priority' => '0.90'],
            ['loc' => '/tutoring', 'priority' => '0.90'],
            ['loc' => '/about', 'priority' => '0.70'],
            ['loc' => '/contact', 'priority' => '0.70'],
            ['loc' => '/instructors', 'priority' => '0.70'],
            ['loc' => '/organizations', 'priority' => '0.60'],
            ['loc' => '/blog', 'priority' => '0.60'],
        ];
        foreach ($static as $s) {
            $urls[] = $s;
        }

        // Courses (active, non-private)
        Webinar::where('status', 'active')
            ->where('private', 0)
            ->select('slug', 'updated_at')
            ->chunk(500, function ($courses) use (&$urls) {
                foreach ($courses as $c) {
                    $urls[] = [
                        'loc' => '/course/' . $c->slug,
                        'priority' => '0.90',
                        'lastmod' => Carbon::createFromTimestamp($c->updated_at)->toAtomString(),
                    ];
                }
            });

        // Top-level categories
        $topCategories = Category::where('enable', true)->whereNull('parent_id')->get(['id', 'slug']);
        foreach ($topCategories as $cat) {
            $urls[] = [
                'loc' => '/categories/' . $cat->slug,
                'priority' => '0.80',
            ];
        }

        // Subcategories (need parent slug for the /{parent}/{sub} route)
        $subCategories = Category::where('enable', true)->whereNotNull('parent_id')->get(['id', 'slug', 'parent_id']);
        $topSlugsById = $topCategories->pluck('slug', 'id');
        foreach ($subCategories as $sub) {
            $parentSlug = $topSlugsById->get($sub->parent_id);
            if ($parentSlug) {
                $urls[] = [
                    'loc' => '/categories/' . $parentSlug . '/' . $sub->slug,
                    'priority' => '0.70',
                ];
            }
        }

        // Instructors & organizations (active only)
        User::whereIn('role_id', [3, 4])
            ->where('status', 'active')
            ->whereNotNull('username')
            ->select('username')
            ->chunk(500, function ($users) use (&$urls) {
                foreach ($users as $u) {
                    $urls[] = [
                        'loc' => '/users/' . $u->username . '/profile',
                        'priority' => '0.60',
                    ];
                }
            });

        // Blog posts (published)
        Blog::where('status', 'publish')
            ->select('slug', 'updated_at')
            ->chunk(500, function ($posts) use (&$urls) {
                foreach ($posts as $p) {
                    $urls[] = [
                        'loc' => '/blog/' . $p->slug,
                        'priority' => '0.60',
                        'lastmod' => Carbon::createFromTimestamp($p->updated_at)->toAtomString(),
                    ];
                }
            });

        $base = rtrim(config('app.url'), '/');
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            $xml .= '<url>' . "\n";
            $xml .= '<loc>' . $base . $u['loc'] . '</loc>' . "\n";
            if (!empty($u['lastmod'])) {
                $xml .= '<lastmod>' . $u['lastmod'] . '</lastmod>' . "\n";
            }
            $xml .= '<priority>' . $u['priority'] . '</priority>' . "\n";
            $xml .= '</url>' . "\n";
        }
        $xml .= '</urlset>';

        file_put_contents(public_path('sitemap.xml'), $xml);

        $this->info('Sitemap generated with ' . count($urls) . ' URLs.');
        return 0;
    }
}
