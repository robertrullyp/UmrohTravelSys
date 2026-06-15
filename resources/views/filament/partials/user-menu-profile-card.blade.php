@php
    $user = filament()->auth()->user();
@endphp

@if ($user)
    <div class="admin-user-menu-card">
        <x-filament-panels::avatar.user :user="$user" class="admin-user-menu-card-avatar" />

        <div class="admin-user-menu-card-body">
            <strong>{{ filament()->getUserName($user) }}</strong>
            <span>{{ $user->email }}</span>
        </div>
    </div>
@endif
