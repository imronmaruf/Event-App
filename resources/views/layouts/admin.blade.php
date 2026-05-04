<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — SiPresensi</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        :root {
            --sidebar-w: 260px;
            --primary: #c62828;
            --primary-dk: #8e0000;
            --primary-lt: #ffebee;
            --accent: #f4b846;
            --sidebar-bg: #1a1a2e;
            --sidebar-text: rgba(255, 255, 255, 0.75);
            --sidebar-active: rgba(198, 40, 40, 0.15);
            --sidebar-hover: rgba(255, 255, 255, 0.06);
        }

        body {
            background: #f4f6f9;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        /* ── Sidebar ─────────────────────────────────────────── */
        #sidebar {
            width: var(--sidebar-w);
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: var(--sidebar-bg);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
        }

        .sidebar-brand {
            padding: 20px 20px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar-brand h1 {
            font-size: 18px;
            font-weight: 800;
            color: #fff;
            margin: 0;
        }

        .sidebar-brand span {
            font-size: 11px;
            color: var(--accent);
            font-weight: 600;
        }

        .nav-section {
            padding: 10px 0;
        }

        .nav-section-title {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, 0.3);
            padding: 12px 20px 6px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            color: var(--sidebar-text);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            border-radius: 0;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .nav-link:hover {
            background: var(--sidebar-hover);
            color: #fff;
        }

        .nav-link.active {
            background: var(--sidebar-active);
            color: #fff;
            border-left-color: var(--primary);
            font-weight: 700;
        }

        .nav-link i {
            font-size: 17px;
            width: 22px;
            text-align: center;
            flex-shrink: 0;
        }

        /* ── Main Content ─────────────────────────────────────── */
        #main-wrap {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
        }

        .topbar {
            background: #fff;
            padding: 14px 24px;
            border-bottom: 1px solid #e8e8e8;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .topbar-title {
            font-size: 17px;
            font-weight: 700;
            color: #1a1a1a;
        }

        .content {
            padding: 24px;
        }

        /* ── Cards ───────────────────────────────────────────── */
        .stat-card {
            background: #fff;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.07);
            border: 1px solid #f0f0f0;
        }

        .stat-card .icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .stat-card .num {
            font-size: 28px;
            font-weight: 900;
            line-height: 1;
        }

        .stat-card .lbl {
            font-size: 12px;
            color: #888;
            margin-top: 3px;
            font-weight: 500;
        }

        /* ── Badge status event ───────────────────────────────── */
        .badge-active {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .badge-inactive {
            background: #fff3e0;
            color: #e65100;
        }

        .badge-archived {
            background: #eceff1;
            color: #546e7a;
        }

        /* ── Alert flash ─────────────────────────────────────── */
        .flash-container {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
        }

        /* ── Responsive ──────────────────────────────────────── */
        @media (max-width: 768px) {
            #sidebar {
                transform: translateX(-100%);
            }

            #sidebar.open {
                transform: translateX(0);
            }

            #main-wrap {
                margin-left: 0;
            }

            .sidebar-overlay {
                display: block !important;
            }
        }
    </style>
    @stack('styles')
</head>

<body>
    <!-- Sidebar overlay (mobile) -->
    <div class="sidebar-overlay d-none position-fixed top-0 start-0 w-100 h-100 bg-black bg-opacity-50"
        style="z-index: 999;" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="sidebar-brand">
            <h1>🏆 SiPresensi</h1>
            <span>Sistem Presensi Perlombaan</span>
        </div>

        <div class="nav-section flex-grow-1 overflow-auto">
            <div class="nav-section-title">Menu Utama</div>
            <a href="{{ route('admin.dashboard') }}"
                class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
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
    </nav>

    <!-- Main Content -->
    <div id="main-wrap">
        <!-- Topbar -->
        <div class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm d-lg-none" onclick="toggleSidebar()" style="color:#555;">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <span class="topbar-title">@yield('page-title', 'Dashboard')</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                @yield('topbar-actions')
            </div>
        </div>

        <!-- Flash Messages -->
        <div class="flash-container">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible shadow-sm d-flex align-items-center gap-2"
                    role="alert">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>{{ session('success') }}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible shadow-sm d-flex align-items-center gap-2"
                    role="alert">
                    <i class="bi bi-x-circle-fill"></i>
                    <span>{{ session('error') }}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>

        <!-- Page Content -->
        <div class="content">
            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            const sb = document.getElementById('sidebar');
            const ov = document.querySelector('.sidebar-overlay');
            sb.classList.toggle('open');
            ov.classList.toggle('d-none');
        }
        // Auto-close flash after 4s
        setTimeout(() => {
            document.querySelectorAll('.flash-container .alert').forEach(el => {
                new bootstrap.Alert(el).close();
            });
        }, 4000);
    </script>
    @stack('scripts')
</body>

</html>
