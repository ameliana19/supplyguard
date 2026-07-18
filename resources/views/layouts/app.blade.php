<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'SupplyGuard')</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <!-- Google Font: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

    <!-- Custom Modern Theme CSS -->
    <style>
        :root {
            --bg-main: #F9FAFB;
            --sidebar-width: 260px;
            --primary-gradient: linear-gradient(135deg, #D4AF37 0%, #B8860B 100%);
            --danger-gradient: linear-gradient(135deg, #EF4444 0%, #1F2937 100%);
            --warning-gradient: linear-gradient(135deg, #F59E0B 0%, #E5E7EB 100%);
            --success-gradient: linear-gradient(135deg, #10B981 0%, #F9FAFB 100%);
            --info-gradient: linear-gradient(135deg, #3B82F6 0%, #F8E7A1 100%);

            /* Bootstrap CSS variables overrides */
            --bs-primary: #D4AF37;
            --bs-primary-rgb: 212, 175, 55;
            --bs-secondary: #1F2937;
            --bs-secondary-rgb: 31, 41, 55;
            --bs-info: #3B82F6;
            --bs-info-rgb: 59, 130, 246;
            --bs-success: #10B981;
            --bs-success-rgb: 16, 185, 129;
            --bs-warning: #F59E0B;
            --bs-warning-rgb: 245, 158, 11;
            --bs-danger: #EF4444;
            --bs-danger-rgb: 239, 68, 68;
            
            --bs-link-color: #D4AF37;
            --bs-link-hover-color: #B8860B;
            
            --bs-body-font-family: 'Poppins', sans-serif;
        }

        body {
            font-family: 'Poppins', sans-serif !important;
            background-color: #F9FAFB !important;
            min-height: 100vh;
            color: #111827;
            overflow-x: hidden;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Sidebar Styling */
        .sidebar {
            width: var(--sidebar-width);
            min-width: var(--sidebar-width);
            background-color: #1F2937 !important;
            color: #ffffff !important;
            height: 100vh;
            position: sticky;
            top: 0;
            z-index: 100;
            transition: all 0.3s ease;
            box-shadow: 4px 0 25px rgba(0, 0, 0, 0.08);
        }
        .sidebar-logo {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 1.25rem;
            font-weight: 700;
            color: #ffffff !important;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar-logo i {
            color: #D4AF37 !important;
        }
        .sidebar-menu {
            padding: 1rem 0;
            height: calc(100vh - 75px);
            overflow-y: auto;
        }
        .sidebar-section-title {
            padding: 0.5rem 1.5rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6B7280 !important;
            font-weight: 600;
            margin-top: 1rem;
        }
        .sidebar-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: #ffffff !important;
            opacity: 0.85;
            text-decoration: none;
            transition: all 0.2s ease;
            gap: 12px;
            font-weight: 500;
            border-left: 3px solid transparent;
            border-radius: 0 10px 10px 0;
            margin-right: 10px;
        }
        .sidebar-item i {
            color: #D4AF37 !important;
            transition: transform 0.2s ease;
        }
        .sidebar-item:hover {
            color: #ffffff !important;
            opacity: 1;
            background-color: #374151 !important;
            border-left-color: #D4AF37 !important;
        }
        .sidebar-item.active {
            color: #ffffff !important;
            opacity: 1;
            background-color: #D4AF37 !important;
            border-left-color: #ffffff !important;
        }
        .sidebar-item.active i {
            color: #ffffff !important;
        }
        .sidebar-item:hover i {
            transform: scale(1.15);
        }

        /* Navbar Styling */
        .main-navbar {
            background-color: #111827 !important;
            border-bottom: 1px solid #E5E7EB !important;
            padding: 1rem 1.5rem;
        }
        .main-navbar .navbar-brand, .main-navbar .nav-link, .main-navbar h4, .main-navbar span {
            color: #ffffff !important;
        }
        .main-navbar i {
            color: #D4AF37 !important;
        }
        .main-navbar .navbar-brand {
            font-weight: 700;
        }

        /* Modern Card Styling */
        .card, .card-stat, .card-premium {
            background-color: #ffffff !important;
            border: 1px solid #E5E7EB !important;
            border-radius: 16px !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08) !important;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-stat {
            border-left: 4px solid #E5E7EB !important;
        }
        .card-stat:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.12) !important;
        }
        .card-stat.primary { border-left-color: #D4AF37 !important; }
        .card-stat.success { border-left-color: #10B981 !important; }
        .card-stat.warning { border-left-color: #F59E0B !important; }
        .card-stat.danger { border-left-color: #EF4444 !important; }
        .card-stat.info { border-left-color: #3B82F6 !important; }

        .card-stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .card-stat-icon.primary { background: rgba(212, 175, 55, 0.15) !important; color: #D4AF37 !important; }
        .card-stat-icon.success { background: rgba(16, 185, 129, 0.15) !important; color: #10B981 !important; }
        .card-stat-icon.warning { background: rgba(245, 158, 11, 0.15) !important; color: #F59E0B !important; }
        .card-stat-icon.danger { background: rgba(239, 68, 68, 0.15) !important; color: #EF4444 !important; }
        .card-stat-icon.info { background: rgba(59, 130, 246, 0.15) !important; color: #3B82F6 !important; }

        /* Premium Panels & Tables */
        .card-premium-header {
            background-color: transparent;
            border-bottom: 1px solid #E5E7EB !important;
            padding: 1.25rem 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .card-premium-body {
            padding: 1.5rem;
        }

        .table-modern {
            width: 100%;
            margin-bottom: 0;
            vertical-align: middle;
        }
        .table-modern th, .table th, thead th {
            font-weight: 600;
            color: #ffffff !important;
            background-color: #1F2937 !important;
            border-bottom: 2px solid #E5E7EB !important;
            padding: 1rem 1.25rem;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }
        .table-modern td, .table td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #E5E7EB !important;
            color: #111827;
            font-size: 0.9rem;
            background-color: #FFFFFF !important;
        }
        .table-modern tr:hover td, .table tr:hover td {
            background-color: #F3F4F6 !important;
        }

        /* Forms & Inputs styling */
        .form-control, .form-select, .input-group-text {
            background-color: #FFFFFF !important;
            border: 1px solid #D1D5DB !important;
            border-radius: 16px !important;
            color: #111827 !important;
        }
        .form-control:focus, .form-select:focus {
            border-color: #D4AF37 !important;
            box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.2) !important;
        }

        /* Dropdown custom styles */
        .dropdown-menu {
            background-color: #FFFFFF !important;
            border: 1px solid #E5E7EB !important;
            border-radius: 12px !important;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08) !important;
            padding: 0.5rem 0 !important;
        }
        .dropdown-item {
            color: #111827 !important;
            transition: all 0.2s ease !important;
            padding: 0.5rem 1.25rem !important;
        }
        .dropdown-item:hover, .dropdown-item:focus {
            background-color: #F3F4F6 !important;
            color: #D4AF37 !important;
        }

        /* Buttons overrides */
        .btn {
            border-radius: 16px !important;
            font-weight: 500 !important;
            padding: 0.5rem 1.25rem !important;
            transition: all 0.2s ease-in-out !important;
        }
        .btn-primary {
            background-color: #D4AF37 !important;
            border-color: #D4AF37 !important;
            color: #ffffff !important;
        }
        .btn-primary:hover, .btn-primary:focus, .btn-primary:active {
            background-color: #B8860B !important;
            border-color: #B8860B !important;
            color: #ffffff !important;
        }
        .btn-secondary {
            background-color: #1F2937 !important;
            border-color: #1F2937 !important;
            color: #ffffff !important;
        }
        .btn-secondary:hover, .btn-secondary:focus, .btn-secondary:active {
            background-color: #111827 !important;
            border-color: #111827 !important;
            color: #ffffff !important;
        }
        .btn-success {
            background-color: #10B981 !important;
            border-color: #10B981 !important;
            color: #ffffff !important;
        }
        .btn-success:hover, .btn-success:focus, .btn-success:active {
            background-color: #059669 !important;
            border-color: #059669 !important;
            color: #ffffff !important;
        }
        .btn-warning {
            background-color: #F59E0B !important;
            border-color: #F59E0B !important;
            color: #ffffff !important;
        }
        .btn-warning:hover, .btn-warning:focus, .btn-warning:active {
            background-color: #d97706 !important;
            border-color: #d97706 !important;
            color: #ffffff !important;
        }

        .btn-outline-primary {
            border-color: #D4AF37 !important;
            color: #D4AF37 !important;
        }
        .btn-outline-primary:hover {
            background-color: #B8860B !important;
            border-color: #B8860B !important;
            color: #ffffff !important;
        }
        .btn-outline-secondary {
            border-color: #1F2937 !important;
            color: #1F2937 !important;
        }
        .btn-outline-secondary:hover {
            background-color: #111827 !important;
            border-color: #111827 !important;
            color: #ffffff !important;
        }
        .btn-outline-dark {
            border-color: #D4AF37 !important;
            color: #D4AF37 !important;
        }
        .btn-outline-dark:hover {
            background-color: #B8860B !important;
            border-color: #B8860B !important;
            color: #ffffff !important;
        }

        /* Link colors */
        a {
            color: #D4AF37;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        a:hover {
            color: #B8860B !important;
        }

        /* Badges */
        .badge {
            font-weight: 600 !important;
            padding: 0.45em 0.85em !important;
            border-radius: 10px !important;
        }
        .badge.bg-primary, .badge-primary {
            background-color: #D4AF37 !important;
            color: #ffffff !important;
        }
        .badge.bg-info, .badge-info {
            background-color: #3B82F6 !important;
            color: #ffffff !important;
        }
        .badge.bg-success, .badge-success {
            background-color: #10B981 !important;
            color: #ffffff !important;
        }
        .badge.bg-warning, .badge-warning {
            background-color: #F59E0B !important;
            color: #ffffff !important;
        }
        .badge.bg-secondary, .badge-secondary {
            background-color: #1F2937 !important;
            color: #ffffff !important;
        }
        .badge.bg-danger, .badge-danger {
            background-color: #EF4444 !important;
            color: #ffffff !important;
        }

        /* Color classes override */
        .text-primary { color: #D4AF37 !important; }
        .text-secondary { color: #6B7280 !important; }
        .text-info { color: #3B82F6 !important; }
        .text-success { color: #10B981 !important; }
        .text-warning { color: #F59E0B !important; }
        .text-danger { color: #EF4444 !important; }

        .bg-primary { background-color: #D4AF37 !important; }
        .bg-secondary { background-color: #1F2937 !important; }
        .bg-info { background-color: #3B82F6 !important; }
        .bg-success { background-color: #10B981 !important; }
        .bg-warning { background-color: #F59E0B !important; }

        /* Alerts / Notifications */
        .alert {
            border: 1px solid transparent !important;
            border-radius: 16px !important;
            font-weight: 500 !important;
        }
        .alert-success {
            background-color: rgba(16, 185, 129, 0.15) !important;
            border-color: #10B981 !important;
            color: #059669 !important;
        }
        .alert-warning {
            background-color: rgba(245, 158, 11, 0.15) !important;
            border-color: #F59E0B !important;
            color: #d97706 !important;
        }
        .alert-danger {
            background-color: rgba(239, 68, 68, 0.15) !important;
            border-color: #EF4444 !important;
            color: #EF4444 !important;
        }
        .alert-info {
            background-color: rgba(59, 130, 246, 0.15) !important;
            border-color: #3B82F6 !important;
            color: #3B82F6 !important;
        }

        /* Progress Bar */
        .progress-bar {
            background-color: #D4AF37 !important;
        }

        /* Pulsing Skeleton Loading */
        .skeleton {
            background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 200% 100%;
            animation: loading-skeleton 1.5s infinite;
            border-radius: 4px;
        }
        @keyframes loading-skeleton {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        .skeleton-text { height: 1rem; margin-bottom: 0.5rem; width: 100%; }
        .skeleton-title { height: 1.5rem; margin-bottom: 1rem; width: 50%; }
        .skeleton-card { height: 120px; border-radius: 16px; }

        /* Animation effects */
        .hover-lift {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .hover-lift:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.12) !important;
        }
    </style>

    @stack('styles')
</head>

<body>

    <div class="d-flex min-vh-100">
        <!-- Sidebar Panel -->
        @include('components.sidebar')

        <!-- Main Workspace -->
        <div class="d-flex flex-column flex-grow-1" style="min-height: 100vh; overflow-x: hidden;">
            <!-- Header Navbar -->
            @include('components.navbar')

            <!-- Main Content Area -->
            <main class="flex-grow-1 p-4" style="overflow-y: auto;">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- CSRF Token untuk semua request POST/PUT/DELETE -->
    <script>
        (function() {
            const originalFetch = window.fetch;
            window.fetch = function(url, options) {
                options = options || {};
                const method = (options.method || 'GET').toUpperCase();
                if (!['GET', 'HEAD', 'OPTIONS'].includes(method)) {
                    const token = document.querySelector('meta[name="csrf-token"]')?.content;
                    if (token) {
                        if (options.headers instanceof Headers) {
                            if (!options.headers.has('X-CSRF-TOKEN')) {
                                options.headers.set('X-CSRF-TOKEN', token);
                            }
                        } else {
                            options.headers = options.headers || {};
                            if (!options.headers['X-CSRF-TOKEN']) {
                                options.headers['X-CSRF-TOKEN'] = token;
                            }
                        }
                    }
                }
                return originalFetch(url, options);
            };
        })();
    </script>

    <!-- Leaflet JS Map -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    @stack('scripts')
</body>

</html>