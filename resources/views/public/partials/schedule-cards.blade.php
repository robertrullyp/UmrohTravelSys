@php
    $showPackage = $showPackage ?? true;
    $showStatus = $showStatus ?? true;
    $showAction = $showAction ?? false;
    $bookingPackage = $bookingPackage ?? null;
@endphp

<div class="schedule-card-list" aria-label="Daftar jadwal keberangkatan">
    @forelse ($schedules as $schedule)
        @php
            $bookingTarget = $bookingPackage ?? $schedule->umrahPackage;
            $canBook = $showAction
                && $bookingTarget
                && $bookingTarget->is_active
                && $schedule->quota > 0
                && ! $schedule->departure_date->lt(today());
        @endphp
        <article class="schedule-card">
            <div class="schedule-card-header">
                <span>{{ $schedule->departure_date->translatedFormat('d F Y') }}</span>
                @if ($showStatus)
                    <span class="status">{{ $schedule->status }}</span>
                @endif
            </div>

            @if ($showPackage)
                <h3>{{ $schedule->umrahPackage?->name ?? 'Paket umrah' }}</h3>
            @endif

            <dl class="schedule-card-meta">
                <div>
                    <dt>Kuota</dt>
                    <dd>{{ $schedule->capacity }}</dd>
                </div>
                <div>
                    <dt>Tersedia</dt>
                    <dd>{{ $schedule->quota }}</dd>
                </div>
            </dl>

            @if ($showAction)
                <div class="schedule-card-action">
                    @if ($canBook)
                        <a class="btn btn-pink" href="{{ route('bookings.package', $bookingTarget) }}">Booking</a>
                    @else
                        <span class="muted-text">Tidak tersedia</span>
                    @endif
                </div>
            @endif
        </article>
    @empty
        <p class="schedule-empty">Belum ada jadwal yang tersedia.</p>
    @endforelse
</div>
