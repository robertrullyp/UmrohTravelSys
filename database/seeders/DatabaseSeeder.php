<?php

namespace Database\Seeders;

use App\Models\CompanyProfile;
use App\Models\Contact;
use App\Models\Gallery;
use App\Models\Schedule;
use App\Models\SiteSetting;
use App\Models\UmrahPackage;
use App\Models\User;
use App\Support\AdminPermissions;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => config('admin.initial_email', 'admin@example.com')],
            [
                'name' => config('admin.initial_name', 'Admin'),
                'password' => config('admin.initial_password', 'rahasia'),
                'is_admin' => true,
            ],
        );

        $permissions = collect(AdminPermissions::all())
            ->map(fn (string $permission): Permission => Permission::query()->firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]));

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $superAdmin = Role::findOrCreate('super-admin', 'web');
        $superAdmin->syncPermissions($permissions->pluck('name')->all());

        $adminRole = Role::findOrCreate('admin', 'web');
        $adminRole->syncPermissions(AdminPermissions::operationalAdmin());

        $admin->syncRoles([$superAdmin->name]);

        CompanyProfile::query()->updateOrCreate(
            ['id' => 1],
            [
                'company_name' => 'PT Amara Al Medina Travel',
                'about' => 'PT Amara Al Medina Travel adalah biro perjalanan umrah dan haji yang berkomitmen memberikan pelayanan terbaik, amanah, dan profesional untuk kenyamanan jamaah.',
                'vision' => 'Menjadi penyelenggara perjalanan umrah dan haji terpercaya dengan pelayanan terbaik.',
                'missions' => [
                    'Memberikan pelayanan terbaik untuk jamaah.',
                    'Menyelenggarakan perjalanan sesuai syariah.',
                    'Menjaga amanah dan kepercayaan jamaah.',
                ],
                'photo_path' => 'profiles/profile-office.jpeg',
                'is_active' => true,
            ],
        );

        $packages = [
            [
                'name' => 'Umroh Plus Tarim Paket 19 Hari',
                'duration_days' => 19,
                'price' => 61000000,
                'airline' => 'Garuda Indonesia',
                'makkah_hotel' => 'Hotel Makkah bintang 5',
                'madinah_hotel' => 'Hotel Madinah bintang 5',
                'departure_month' => 'Bulan Oktober',
                'is_featured' => true,
                'image_path' => 'packages/package-plus-tarim.jpeg',
            ],
            [
                'name' => 'Umroh Reguler 12 Hari',
                'duration_days' => 12,
                'price' => 28500000,
                'airline' => 'Garuda Indonesia',
                'makkah_hotel' => 'Hotel Makkah bintang 4',
                'madinah_hotel' => 'Hotel Madinah bintang 4',
                'departure_month' => 'Bulan Oktober',
                'image_path' => 'packages/public-paket.jpeg',
            ],
            [
                'name' => 'Umroh Ramadhan 15 Hari',
                'duration_days' => 15,
                'price' => 45000000,
                'airline' => 'Saudi Airlines',
                'makkah_hotel' => 'Hotel Makkah bintang 5',
                'madinah_hotel' => 'Hotel Madinah bintang 5',
                'departure_month' => 'Bulan Maret',
                'image_path' => 'packages/public-paket.jpeg',
            ],
            [
                'name' => 'Umroh Plus Turki 16 Hari',
                'duration_days' => 16,
                'price' => 38000000,
                'airline' => 'Turkish Airlines',
                'makkah_hotel' => 'Hotel Makkah bintang 4',
                'madinah_hotel' => 'Hotel Madinah bintang 4',
                'departure_month' => 'Bulan November',
                'image_path' => 'packages/public-paket.jpeg',
            ],
            [
                'name' => 'Umroh Promo 9 Hari',
                'duration_days' => 9,
                'price' => 19900000,
                'airline' => 'Lion Air',
                'makkah_hotel' => 'Hotel Makkah bintang 3',
                'madinah_hotel' => 'Hotel Madinah bintang 3',
                'departure_month' => 'Bulan November',
                'image_path' => 'packages/public-paket.jpeg',
            ],
        ];

        foreach ($packages as $index => $package) {
            UmrahPackage::query()->updateOrCreate(
                ['slug' => Str::slug($package['name'])],
                [
                    ...$package,
                    'description' => 'Paket perjalanan ibadah umrah dengan pendampingan tim berpengalaman, fasilitas nyaman, dan jadwal yang tertata.',
                    'includes' => ['Tiket pesawat', 'Hotel', 'Visa umrah', 'Transportasi', 'Muthawwif'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ],
            );
        }

        $scheduleRows = [
            ['Umroh Plus Tarim Paket 19 Hari', now()->addMonths(1)->day(10)->toDateString(), 20],
            ['Umroh Reguler 12 Hari', now()->addMonths(1)->day(18)->toDateString(), 25],
            ['Umroh Ramadhan 15 Hari', now()->addMonths(2)->day(5)->toDateString(), 20],
            ['Umroh Plus Turki 16 Hari', now()->addMonths(2)->day(15)->toDateString(), 20],
            ['Umroh Promo 9 Hari', now()->addMonths(3)->day(12)->toDateString(), 30],
        ];

        foreach ($scheduleRows as [$packageName, $date, $quota]) {
            $package = UmrahPackage::query()->where('name', $packageName)->first();

            if ($package === null) {
                continue;
            }

            Schedule::query()->updateOrCreate(
                ['umrah_package_id' => $package->id, 'departure_date' => $date],
                [
                    'capacity' => $quota,
                    'quota' => $quota,
                    'status' => 'Tersedia',
                    'is_active' => true,
                ],
            );
        }

        $galleryRows = [
            ['City Tour Madinah', now()->subDays(45)->toDateString(), 'galleries/public-galeri.jpeg'],
            ['Jamaah di Masjid Nabawi', now()->subDays(42)->toDateString(), 'galleries/gallery-grid.jpeg'],
            ['Perjalanan ke Mekkah', now()->subDays(39)->toDateString(), 'galleries/public-galeri.jpeg'],
            ['Ziarah Raudhah', now()->subDays(36)->toDateString(), 'galleries/gallery-grid.jpeg'],
            ['Foto Bersama Jamaah', now()->subDays(33)->toDateString(), 'galleries/public-galeri.jpeg'],
        ];

        foreach ($galleryRows as $index => [$title, $date, $image]) {
            Gallery::query()->updateOrCreate(
                ['title' => $title],
                [
                    'image_path' => $image,
                    'taken_at' => $date,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }

        Contact::query()->updateOrCreate(
            ['id' => 1],
            [
                'address' => 'Jl. A. Yani Batu Piring, Paringin Selatan, Balangan, Kalimantan Selatan',
                'whatsapp' => '082252239507',
                'email' => 'ptamaraalmedinatravel@gmail.com',
                'instagram' => '@amaraalmedina_travel',
                'map_embed_url' => 'https://www.google.com/maps?q=Paringin%20Selatan%20Balangan&output=embed',
                'latitude' => -2.331994,
                'longitude' => 115.471695,
                'is_active' => true,
                'is_primary' => true,
            ],
        );

        $settings = [
            'brand_logo_path' => 'images/site/logo.png',
            'favicon_path' => 'images/site/logo.png',
            'hero_image_path' => 'images/site/beranda-img.jpg',
            'hero_title_highlight' => 'Perjalanan Ibadah Umrah',
            'hero_title' => 'Nyaman, Aman & Terpercaya',
            'hero_subtitle' => 'PT. Amara Al Medina Travel siap menjadi mitra perjalanan ibadah terbaik Anda dengan pelayanan profesional dan amanah.',
            'cta_whatsapp' => '082252239507',
        ];

        foreach ($settings as $key => $value) {
            SiteSetting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
