<?php

namespace App\Http\Controllers;

use App\Models\CompanyProfile;
use App\Models\Contact;
use App\Models\Gallery;
use App\Models\GalleryPhoto;
use App\Models\Schedule;
use App\Models\SiteSetting;
use App\Models\UmrahPackage;
use App\Services\SeoService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(SeoService $seo): Response
    {
        $packages = UmrahPackage::query()
            ->where('is_active', true)
            ->where('is_indexable', true)
            ->withMax([
                'schedules as active_schedules_updated_at' => fn ($query) => $query->where('is_active', true),
            ], 'updated_at')
            ->orderBy('sort_order')
            ->get();

        $updated = [
            'settings' => SiteSetting::query()->max('updated_at'),
            'profile' => CompanyProfile::query()->where('is_active', true)->max('updated_at'),
            'contacts' => Contact::query()->where('is_active', true)->max('updated_at'),
            'packages' => UmrahPackage::query()->where('is_active', true)->where('is_indexable', true)->max('updated_at'),
            'schedules' => Schedule::query()->where('is_active', true)->max('updated_at'),
            'galleries' => Gallery::query()->where('is_active', true)->max('updated_at'),
            'gallery_photos' => GalleryPhoto::query()
                ->whereHas('gallery', fn ($query) => $query->where('is_active', true))
                ->max('updated_at'),
        ];

        $entries = collect([
            $this->entry($seo->routeUrl('home'), $this->latest($updated)),
            $this->entry($seo->routeUrl('profile'), $this->latest([$updated['profile'], $updated['settings']])),
            $this->entry($seo->routeUrl('packages'), $this->latest([$updated['packages'], $updated['settings']])),
            $this->entry($seo->routeUrl('schedules'), $this->latest([$updated['schedules'], $updated['settings']])),
            $this->entry($seo->routeUrl('galleries'), $this->latest([$updated['galleries'], $updated['gallery_photos'], $updated['settings']])),
            $this->entry($seo->routeUrl('contact'), $this->latest([$updated['contacts'], $updated['settings']])),
            $this->entry($seo->routeUrl('bookings.create'), $this->latest([$updated['packages'], $updated['schedules'], $updated['settings']])),
        ])->merge($packages->map(function (UmrahPackage $package) use ($seo): array {
            return $this->entry(
                $seo->routeUrl('packages.show', $package),
                $this->latest([$package->updated_at, $package->active_schedules_updated_at]),
            );
        }));

        return response()
            ->view('sitemap', ['entries' => $entries])
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=900');
    }

    /** @return array{loc: string, lastmod: ?string} */
    private function entry(string $url, ?CarbonImmutable $lastModified): array
    {
        return [
            'loc' => $url,
            'lastmod' => $lastModified?->utc()->toAtomString(),
        ];
    }

    private function latest(iterable $values): ?CarbonImmutable
    {
        return collect($values)
            ->filter()
            ->map(fn ($value): CarbonImmutable => CarbonImmutable::parse($value))
            ->sortDesc()
            ->first();
    }
}
