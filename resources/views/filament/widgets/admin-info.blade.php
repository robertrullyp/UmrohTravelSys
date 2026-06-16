<x-filament-widgets::widget>
    <x-filament::section>
        <div class="admin-info-card">
            <div>
                <p class="admin-info-eyebrow">Booking</p>
                <h2>Selamat datang, Admin</h2>
                <p class="admin-info-copy">
                    Pantau booking masuk, proses review, dan kuota jadwal yang mulai menipis dari sini.
                </p>
            </div>

            <div class="admin-info-list">
                <div>
                    <span>Booking pending</span>
                    <strong>{{ $pendingCount }}</strong>
                </div>
                <div>
                    <span>Disetujui hari ini</span>
                    <strong>{{ $approvedTodayCount }}</strong>
                </div>
                <div>
                    <span>Booking terbaru</span>
                    <strong>
                        @if ($latestBooking)
                            <a href="{{ \App\Filament\Resources\Bookings\BookingResource::getUrl('view', ['record' => $latestBooking]) }}">
                                {{ $latestBooking->booking_number }}
                            </a>
                        @else
                            -
                        @endif
                    </strong>
                </div>
                <div>
                    <span>Kuota menipis</span>
                    <strong>
                        {{ $lowQuotaSchedules->isNotEmpty() ? $lowQuotaSchedules->count() . ' jadwal' : '-' }}
                    </strong>
                </div>
            </div>

            @if ($lowQuotaSchedules->isNotEmpty())
                <div class="admin-info-low-quota">
                    @foreach ($lowQuotaSchedules as $schedule)
                        <a href="{{ $bookingIndexUrl }}" class="admin-info-low-quota-item">
                            <strong>{{ $schedule->umrahPackage?->name }}</strong>
                            <span>{{ $schedule->departure_date->translatedFormat('d F Y') }} · {{ $schedule->quota }} kursi</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
