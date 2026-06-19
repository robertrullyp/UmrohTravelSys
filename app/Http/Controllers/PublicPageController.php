<?php

namespace App\Http\Controllers;

use App\Models\CompanyProfile;
use App\Models\Contact;
use App\Models\Gallery;
use App\Models\Schedule;
use App\Models\SiteSetting;
use App\Models\UmrahPackage;
use App\Services\SeoService;
use Illuminate\Contracts\View\View;

class PublicPageController extends Controller
{
    public function home(): View
    {
        return view('public.home', $this->publicViewData('home', [
            'featuredPackage' => UmrahPackage::query()
                ->where('is_active', true)
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->first(),
            'packages' => UmrahPackage::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->limit(3)
                ->get(),
            'schedules' => Schedule::query()
                ->with('umrahPackage')
                ->where('is_active', true)
                ->latest('created_at')
                ->latest('id')
                ->limit(5)
                ->get(),
            'galleries' => Gallery::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->limit(6)
                ->get(),
        ]));
    }

    public function profile(): View
    {
        return view('public.profile', $this->publicViewData('profile'));
    }

    public function packages(): View
    {
        return view('public.packages', $this->publicViewData('packages', [
            'packages' => UmrahPackage::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(),
        ]));
    }

    public function packageDetail(UmrahPackage $package): View
    {
        abort_unless($package->is_active, 404);

        $schedules = $package->schedules()
            ->where('is_active', true)
            ->orderBy('departure_date')
            ->get();

        return view('public.package-detail', $this->publicViewData('package', [
            'package' => $package,
            'schedules' => $schedules,
        ]));
    }

    public function schedules(): View
    {
        return view('public.schedules', $this->publicViewData('schedules', [
            'schedules' => Schedule::query()
                ->with('umrahPackage')
                ->where('is_active', true)
                ->orderBy('departure_date')
                ->get(),
        ]));
    }

    public function galleries(): View
    {
        return view('public.galleries', $this->publicViewData('galleries', [
            'galleries' => Gallery::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(),
        ]));
    }

    public function contact(): View
    {
        return view('public.contact', $this->publicViewData('contact'));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function publicViewData(string $page, array $data = []): array
    {
        $viewData = [...$this->sharedData(), ...$data];
        $viewData['seo'] = app(SeoService::class)->forPage($page, $viewData);

        return $viewData;
    }

    protected function sharedData(): array
    {
        $contacts = Contact::query()
            ->where('is_active', true)
            ->orderByDesc('is_primary')
            ->latest('updated_at')
            ->latest('id')
            ->get();
        $primaryContact = $contacts->firstWhere('is_primary', true) ?? $contacts->first();

        return [
            'profile' => CompanyProfile::query()->whereKey(1)->where('is_active', true)->first(),
            'contact' => $primaryContact,
            'contacts' => $contacts,
            'settings' => SiteSetting::query()->pluck('value', 'key'),
        ];
    }
}
