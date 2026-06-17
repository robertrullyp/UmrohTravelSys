<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Gallery;
use App\Models\Schedule;
use App\Models\UmrahPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_public_pages_render_seeded_content(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Perjalanan Ibadah Umrah')
            ->assertSee('Lihat Jadwal')
            ->assertSee('Lihat Semua Paket')
            ->assertSee('Lihat Semua Jadwal')
            ->assertSee('Kuota')
            ->assertSee('Tersedia')
            ->assertSee('images/site/logo.png')
            ->assertDontSee('images/site/logo-nobg.png')
            ->assertSee('images/site/beranda-img.jpg');
        $this->get('/profil')
            ->assertOk()
            ->assertSee('Profil Perusahaan')
            ->assertSee('Mengenal layanan dan komitmen PT Amara Al Medina Travel.')
            ->assertDontSee('Home / Profil');
        $this->get('/paket-umrah')->assertOk()->assertSee('Umroh Plus Tarim Paket 19 Hari');
        $this->get('/jadwal')
            ->assertOk()
            ->assertSee('Jadwal Keberangkatan')
            ->assertSee('Kuota')
            ->assertSee('Tersedia');
        $this->get('/galeri')
            ->assertOk()
            ->assertSee('Galeri Kegiatan')
            ->assertSee('data-gallery-lightbox', false)
            ->assertSee('data-gallery-trigger', false);
        $this->get('/kontak')
            ->assertOk()
            ->assertSee('Informasi Kontak')
            ->assertSee('Hubungi admin untuk konsultasi paket, jadwal, dan keberangkatan.')
            ->assertSee('href="tel:+6282252239507"', false)
            ->assertSee('href="mailto:ptamaraalmedinatravel@gmail.com"', false)
            ->assertSee('https://www.google.com/maps/search/?api=1', false)
            ->assertDontSee('Home / Kontak');
    }

    public function test_public_contact_uses_primary_contact_for_footer_and_lists_active_contacts(): void
    {
        Contact::query()->update([
            'updated_at' => now()->subDays(3),
            'is_active' => false,
            'is_primary' => false,
        ]);

        $primaryContact = Contact::query()->create([
            'address' => 'Alamat Kontak Utama',
            'whatsapp' => '081111111111',
            'email' => 'primary-contact@example.test',
            'instagram' => '@primary_contact',
            'map_embed_url' => 'https://www.google.com/maps/embed?pb=primary',
            'latitude' => -2.3333333,
            'longitude' => 115.3333333,
            'is_active' => true,
            'is_primary' => true,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);
        $secondaryContact = Contact::query()->create([
            'address' => 'Alamat Kontak Cabang',
            'whatsapp' => '082222222222',
            'email' => 'branch-contact@example.test',
            'instagram' => '@branch_contact',
            'map_embed_url' => 'https://www.google.com/maps/embed?pb=branch',
            'is_active' => true,
            'is_primary' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $inactiveContact = Contact::query()->create([
            'address' => 'Alamat Tidak Tampil',
            'whatsapp' => '083333333333',
            'email' => 'inactive-contact@example.test',
            'is_active' => false,
            'is_primary' => false,
        ]);

        $this->get('/kontak')
            ->assertOk()
            ->assertSeeInOrder([$primaryContact->address, $secondaryContact->address])
            ->assertSee($primaryContact->email)
            ->assertSee($secondaryContact->email)
            ->assertSee('href="https://wa.me/6281111111111"', false)
            ->assertSee('href="https://wa.me/6282222222222"', false)
            ->assertDontSee($inactiveContact->address);

        $this->get('/')
            ->assertOk()
            ->assertSee('href="tel:+6281111111111"', false)
            ->assertSee('href="mailto:primary-contact@example.test"', false)
            ->assertDontSee('branch-contact@example.test');
    }

    public function test_home_shows_five_latest_added_schedules(): void
    {
        $package = UmrahPackage::query()->firstOrFail();
        Schedule::query()->update([
            'created_at' => now()->subDays(30),
            'updated_at' => now()->subDays(30),
        ]);

        $schedules = collect(range(1, 6))->map(function (int $index) use ($package): Schedule {
            return Schedule::query()->create([
                'umrah_package_id' => $package->id,
                'departure_date' => today()->addYears(2)->addDays($index),
                'capacity' => 20 + $index,
                'quota' => 10 + $index,
                'status' => 'Tersedia',
                'is_active' => true,
                'created_at' => now()->subMinutes(6 - $index),
                'updated_at' => now()->subMinutes(6 - $index),
            ]);
        });

        $response = $this->get('/')->assertOk();

        $oldest = $schedules->first();
        $response->assertDontSee($oldest->departure_date->translatedFormat('d F Y'));

        $schedules->skip(1)->each(function (Schedule $schedule) use ($response): void {
            $response->assertSee($schedule->departure_date->translatedFormat('d F Y'));
            $response->assertSee((string) $schedule->capacity);
            $response->assertSee((string) $schedule->quota);
        });
    }

    public function test_package_detail_route_uses_slug(): void
    {
        $package = UmrahPackage::query()->firstOrFail();

        $this->get(route('packages.show', $package))
            ->assertOk()
            ->assertSee($package->name);
    }

    public function test_content_models_can_be_updated_for_crud_flow(): void
    {
        $package = UmrahPackage::query()->firstOrFail();
        $package->update(['price' => 62000000]);

        $gallery = Gallery::query()->create([
            'title' => 'Test Upload Foto',
            'image_path' => 'galleries/test.jpeg',
            'taken_at' => '2026-06-14',
            'is_active' => true,
            'sort_order' => 99,
        ]);

        $this->assertDatabaseHas('umrah_packages', [
            'id' => $package->id,
            'price' => 62000000,
        ]);
        $this->assertDatabaseHas('galleries', ['id' => $gallery->id]);

        $gallery->delete();

        $this->assertDatabaseMissing('galleries', ['id' => $gallery->id]);
    }
}
