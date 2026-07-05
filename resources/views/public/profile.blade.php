@extends('layouts.public')

@section('title', 'Profil - PT Amara Al Medina Travel')

@section('content')
<div class="public-profile">
    <section class="page-hero compact">
        <div class="container">
            <h1>Profil Perusahaan</h1>
            <p>Mengenal layanan dan komitmen PT Amara Al Medina Travel.</p>
        </div>
    </section>

    <section class="section">
        <div class="container profile-layout">
            <div class="info-stack">
                <article class="info-panel">
                    <h2>Tentang Perusahaan</h2>
                    <p>{{ $profile?->about }}</p>
                </article>
                <article class="info-panel">
                    <h2>Visi</h2>
                    <p>{{ $profile?->vision }}</p>
                </article>
                <article class="info-panel">
                    <h2>Misi</h2>
                    <ol class="number-list">
                        @foreach (($profile?->missions ?? []) as $mission)
                            <li>{{ is_array($mission) ? ($mission['item'] ?? '') : $mission }}</li>
                        @endforeach
                    </ol>
                </article>
            </div>
            <div class="profile-photo">
                <img src="{{ $profile?->photo_path ? asset('storage/' . $profile->photo_path) : asset('images/seed/profile-office.jpeg') }}" alt="Profil PT Amara Al Medina Travel" width="800" height="1000" loading="lazy" decoding="async">
            </div>
        </div>
    </section>
</div>
@endsection
