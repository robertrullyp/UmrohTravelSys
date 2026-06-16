<div class="admin-sidebar-brand">
    <a class="admin-sidebar-brand-link" href="{{ filament()->getHomeUrl() ?? url('/admin') }}" aria-label="Dashboard admin">
        <img class="admin-sidebar-logo admin-sidebar-logo-expanded" src="{{ \App\Models\SiteSetting::assetUrl('brand_logo_path', 'images/site/logo.png') }}" alt="PT Amara Al Medina Travel" x-show="$store.sidebar.isOpen" x-cloak>
        <img class="admin-sidebar-logo admin-sidebar-logo-collapsed" src="{{ \App\Models\SiteSetting::assetUrl('brand_logo_path', 'images/site/logo.png') }}" alt="PT Amara Al Medina Travel" x-show="! $store.sidebar.isOpen" x-cloak>
    </a>
</div>
