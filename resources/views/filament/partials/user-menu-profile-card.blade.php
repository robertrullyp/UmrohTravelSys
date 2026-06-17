@php
    $user = filament()->auth()->user();
    $roles = $user && method_exists($user, 'getRoleNames')
        ? $user->getRoleNames()->map(fn (string $role): string => str($role)->headline()->toString())->join(', ')
        : null;
@endphp

@if ($user)
    <div class="admin-user-menu-card">
        <x-filament-panels::avatar.user :user="$user" class="admin-user-menu-card-avatar" />

        <div class="admin-user-menu-card-body">
            <strong>{{ filament()->getUserName($user) }}</strong>
            <span>{{ $user->email }}</span>
            @if ($roles)
                <span class="admin-user-menu-card-role">{{ $roles }}</span>
            @endif
        </div>
    </div>
@endif
