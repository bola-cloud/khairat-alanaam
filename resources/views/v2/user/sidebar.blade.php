@php
    $currentRoute = Route::currentRouteName();
    $nameParts = explode(' ', auth()->user()->name ?? 'U');
    $initials = '';
    if (count($nameParts) >= 2) {
        $initials = mb_substr($nameParts[0], 0, 1) . mb_substr($nameParts[1], 0, 1);
    } else {
        $initials = mb_substr($nameParts[0], 0, 2);
    }
    $initials = mb_strtoupper($initials);
@endphp

<div class="card border-0 mb-4" style="background: transparent;">
    <div class="card-body p-0">
        <div class="d-flex align-items-center p-3 bg-white shadow-sm border border-light" style="border-radius: 12px; justify-content: flex-start;">
            <div class="d-flex align-items-center justify-content-center fw-bold text-white shadow-sm" style="width: 50px; height: 50px; border-radius: 50%; background-color: #d92624; font-size: 20px;">
                {{ $initials }}
            </div>
            <div class="text-end me-3">
                <h6 class="fw-bold mb-1" style="color: #333;">{{ auth()->user()->name }}</h6>
                <small class="text-muted" style="font-size: 13px;">{{ auth()->user()->email }}</small>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 bg-transparent mb-4">
    <div class="card-body p-0">
        <h6 class="text-muted mb-2 text-end" style="font-size: 13px; padding-inline-end: 15px; font-weight: 600;">@lang('v2_home.more')</h6>
        <div class="list-group list-group-flush border-0 bg-white shadow-sm p-2" style="border-radius: 12px;">
            <a href="{{ route('user.profile.orders') }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-start rounded-3 mb-1 border-0 p-3 {{ $currentRoute == 'user.profile.orders' || $currentRoute == 'user.profile.order.track' ? 'active-sidebar-item' : '' }}">
                <i class="fas fa-box ms-3" style="width: 20px; text-align: center; font-size: 18px;"></i>
                <span class="fw-medium">@lang('v2_home.orders')</span>
            </a>
            <a href="{{ route('user.profile.favorites') }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-start rounded-3 mb-1 border-0 p-3 {{ $currentRoute == 'user.profile.favorites' ? 'active-sidebar-item' : '' }}">
                <i class="far fa-heart ms-3" style="width: 20px; text-align: center; font-size: 18px;"></i>
                <span class="fw-medium">@lang('v2_layout.nav_favorites')</span>
            </a>
        </div>
        
        <h6 class="text-muted mb-2 text-end mt-4" style="font-size: 13px; padding-inline-end: 15px; font-weight: 600;">@lang('v2_layout.my_account')</h6>
        <div class="list-group list-group-flush border-0 bg-white shadow-sm p-2" style="border-radius: 12px;">
            <a href="{{ route('user.profile') }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-start rounded-3 mb-1 border-0 p-3 {{ $currentRoute == 'user.profile' ? 'active-sidebar-item' : '' }}">
                <i class="far fa-user ms-3" style="width: 20px; text-align: center; font-size: 18px;"></i>
                <span class="fw-medium">@lang('v2_home.contact_personal_info')</span>
            </a>
            <a href="{{ route('user.profile.addresses') }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-start rounded-3 mb-1 border-0 p-3 {{ $currentRoute == 'user.profile.addresses' ? 'active-sidebar-item' : '' }}">
                <i class="fas fa-map-marker-alt ms-3" style="width: 20px; text-align: center; font-size: 18px;"></i>
                <span class="fw-medium">@lang('v2_home.addresses')</span>
            </a>
        </div>
    </div>
</div>

<div class="card border-0 bg-transparent">
    <div class="card-body p-0">
        <div class="list-group list-group-flush border-0 bg-white shadow-sm p-2" style="border-radius: 12px;">
            <a href="{{ route('user.logout') }}" class="list-group-item list-group-item-action d-flex justify-content-center align-items-center rounded-3 border border-danger p-3 text-danger" style="background: transparent;">
                <i class="fas fa-power-off ms-2"></i>
                <span class="fw-bold">@lang('v2_layout.logout')</span>
            </a>
        </div>
    </div>
</div>

<style>
    .active-sidebar-item {
        background-color: #fce8e8 !important; /* Light red/pink */
        color: #d92624 !important; /* Theme primary red */
        font-weight: bold;
    }
    .active-sidebar-item i {
        color: #d92624 !important;
    }
    .list-group-item-action {
        color: #555;
        transition: all 0.3s ease;
    }
    .list-group-item-action:hover:not(.active-sidebar-item):not(.text-danger) {
        background-color: #f8f9fa;
        color: #000;
    }
    /* Revert reverse order since we fixed the DOM directly */
    .d-flex.justify-content-end, .d-flex.justify-content-start {
        flex-direction: row;
    }
</style>
