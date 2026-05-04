<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — SI-Event</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/admin/style.css') }}">
    @stack('styles')
</head>

<body>
    <!-- Sidebar overlay (mobile) -->
    <div class="sidebar-overlay d-none position-fixed top-0 start-0 w-100 h-100 bg-black bg-opacity-50"
        style="z-index: 999;" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <nav id="sidebar">
        @include('layouts.sidebar')
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
