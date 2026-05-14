<div class="sidebar-brand">
    <h1>🏆 SI-EventGO</h1>
    <span>SI Manajemen Event Perlombaan GO Aceh</span>
</div>

<div class="nav-section flex-grow-1 overflow-auto">
    <div class="nav-section-title">Menu Utama</div>
    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('admin.events.index') }}"
        class="nav-link {{ request()->routeIs('admin.events.*') ? 'active' : '' }}">
        <i class="bi bi-calendar-event"></i> Manajemen Event
    </a>

    @auth
        @if (auth()->user()->isSuperAdmin())
            <div class="nav-section-title">Superadmin</div>
            <a href="{{ route('admin.units.index') }}"
                class="nav-link {{ request()->routeIs('admin.units.*') ? 'active' : '' }}">
                <i class="bi bi-building"></i> Manajemen Unit
            </a>
            <a href="{{ route('admin.users.index') }}"
                class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Manajemen Admin
            </a>
            <a href="{{ route('admin.cities.index') }}"
                class="nav-link {{ request()->routeIs('admin.cities.*') ? 'active' : '' }}">
                <i class="bi bi-geo-alt"></i> Manajemen Kota
            </a>
        @endif
    @endauth
</div>

<!-- User info -->
<div class="p-3 border-top border-white border-opacity-10">
    @auth
        <div class="d-flex align-items-center gap-2 mb-2">
            <div
                style="width:36px;height:36px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:14px;">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div>
                <div style="font-size:13px;font-weight:700;color:#fff;">{{ auth()->user()->name }}</div>
                <div style="font-size:11px;color:var(--accent);">
                    {{ ucfirst(auth()->user()->role) }}{{ auth()->user()->unit ? ' · ' . auth()->user()->unit->name : '' }}
                </div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-light w-100" style="font-size:12px;">
                <i class="bi bi-box-arrow-left"></i> Logout
            </button>
        </form>
    @endauth
</div>
