<x-filament-widgets::widget>
    <x-filament::section>
        <div class="admin-info-card">
            <div>
                <p class="admin-info-eyebrow">Informasi</p>
                <h2>Selamat datang, Admin</h2>
                <p class="admin-info-copy">
                    Kelola paket umrah, jadwal keberangkatan, galeri, profil, dan kontak website dari panel ini.
                </p>
            </div>

            <div class="admin-info-list">
                <div>
                    <span>Kontak aktif</span>
                    <strong>{{ $contact?->whatsapp ?? '-' }}</strong>
                </div>
                <div>
                    <span>Jadwal terdekat</span>
                    <strong>
                        {{ $nextSchedule?->departure_date?->translatedFormat('d F Y') ?? '-' }}
                    </strong>
                </div>
                <div>
                    <span>Paket jadwal</span>
                    <strong>{{ $nextSchedule?->umrahPackage?->name ?? '-' }}</strong>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
