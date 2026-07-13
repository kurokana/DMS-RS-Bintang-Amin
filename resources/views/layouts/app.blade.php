<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'DMS Middleware Dashboard' }}</title>
    
    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    @livewireStyles

    <!-- Premium Custom Vanilla CSS -->
    <style>
        :root {
            --bg-base: #0f172a;
            --bg-surface: rgba(30, 41, 59, 0.7);
            --bg-card: rgba(15, 23, 42, 0.6);
            --border-glow: rgba(56, 189, 248, 0.2);
            --color-primary: #38bdf8;
            --color-primary-rgb: 56, 189, 248;
            --color-secondary: #c084fc;
            --color-success: #34d399;
            --color-warning: #fbbf24;
            --color-danger: #f87171;
            --color-text-main: #f8fafc;
            --color-text-muted: #94a3b8;
            --font-main: 'Outfit', sans-serif;
            --transition-fast: 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-normal: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: var(--font-main);
        }

        body {
            background-color: var(--bg-base);
            color: var(--color-text-main);
            overflow-x: hidden;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 280px;
            background-color: rgba(15, 23, 42, 0.95);
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 2rem;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            z-index: 100;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 800;
            font-size: 1.5rem;
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.05em;
        }

        .logo i {
            color: var(--color-primary);
            background: none;
            -webkit-text-fill-color: initial;
        }

        .nav-links {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            list-style: none;
        }

        .nav-item a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 1rem;
            color: var(--color-text-muted);
            text-decoration: none;
            border-radius: 0.75rem;
            font-weight: 500;
            transition: var(--transition-fast);
        }

        .nav-item a:hover, .nav-item.active a {
            color: var(--color-text-main);
            background-color: rgba(56, 189, 248, 0.1);
            border: 1px solid var(--border-glow);
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.05);
        }

        .nav-item a i {
            width: 20px;
            height: 20px;
        }

        /* User profile area in sidebar */
        .user-profile {
            margin-top: auto;
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
            color: white;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            gap: 0.1rem;
            min-width: 0;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.9rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-role {
            font-size: 0.75rem;
            color: var(--color-text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Main Content Styling */
        .main-content {
            margin-left: 280px;
            width: calc(100% - 280px);
            padding: 2.5rem;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            gap: 2rem;
            background: radial-gradient(circle at 80% 20%, rgba(120, 119, 198, 0.05), transparent 50%);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-weight: 800;
            font-size: 2rem;
            letter-spacing: -0.02em;
        }

        .page-subtitle {
            font-size: 0.9rem;
            color: var(--color-text-muted);
            margin-top: 0.25rem;
        }

        /* Glassmorphic Cards */
        .card {
            background: var(--bg-surface);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 1.25rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
            padding: 1.75rem;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.2);
            transition: var(--transition-normal);
        }

        .card:hover {
            border-color: rgba(56, 189, 248, 0.15);
            box-shadow: 0 12px 40px 0 rgba(56, 189, 248, 0.05);
        }

        /* Premium Form Elements */
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 1.25rem;
        }

        .form-label {
            font-weight: 500;
            font-size: 0.875rem;
            color: var(--color-text-muted);
        }

        .form-control {
            background-color: rgba(15, 23, 42, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--color-text-main);
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            outline: none;
            transition: var(--transition-fast);
            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.15);
        }

        /* Custom buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition-fast);
            border: none;
            font-size: 0.95rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--color-primary), #0284c7);
            color: #0f172a;
        }

        .btn-primary:hover {
            filter: brightness(1.1);
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.3);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            color: var(--color-text-main);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        /* Tables */
        .table-responsive {
            overflow-x: auto;
            width: 100%;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .table th {
            padding: 1rem;
            color: var(--color-text-muted);
            font-weight: 600;
            font-size: 0.875rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .table td {
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.02);
            font-size: 0.95rem;
            vertical-align: middle;
        }

        .table tr:hover td {
            background-color: rgba(255, 255, 255, 0.01);
        }

        /* Status badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.75rem;
            border-radius: 50rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-success {
            background-color: rgba(52, 211, 153, 0.1);
            color: var(--color-success);
            border: 1px solid rgba(52, 211, 153, 0.2);
        }

        .badge-danger {
            background-color: rgba(248, 113, 113, 0.1);
            color: var(--color-danger);
            border: 1px solid rgba(248, 113, 113, 0.2);
        }

        .badge-warning {
            background-color: rgba(251, 191, 36, 0.1);
            color: var(--color-warning);
            border: 1px solid rgba(251, 191, 36, 0.2);
        }

        /* Grid utilities */
        .grid-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 1.5rem;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <i data-lucide="monitor-play"></i>
            <span>DMS System</span>
        </div>

        <ul class="nav-links">
            <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <a href="{{ route('admin.dashboard') }}">
                    <i data-lucide="layout-dashboard"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item {{ request()->routeIs('admin.wards') ? 'active' : '' }}">
                <a href="{{ route('admin.wards') }}">
                    <i data-lucide="bed-double"></i>
                    <span>Kamar Rawat Inap</span>
                </a>
            </li>
            <li class="nav-item {{ request()->routeIs('admin.schedules') ? 'active' : '' }}">
                <a href="{{ route('admin.schedules') }}">
                    <i data-lucide="activity"></i>
                    <span>Jadwal Operasi</span>
                </a>
            </li>
            <li class="nav-item {{ request()->routeIs('admin.displays') ? 'active' : '' }}">
                <a href="{{ route('admin.displays') }}">
                    <i data-lucide="monitor"></i>
                    <span>Display Monitor</span>
                </a>
            </li>
            <li class="nav-item {{ request()->routeIs('admin.monitoring') ? 'active' : '' }}">
                <a href="{{ route('admin.monitoring') }}">
                    <i data-lucide="rss"></i>
                    <span>Monitoring Status</span>
                </a>
            </li>
            <li class="nav-item {{ request()->routeIs('admin.audit-logs') ? 'active' : '' }}">
                <a href="{{ route('admin.audit-logs') }}">
                    <i data-lucide="file-history"></i>
                    <span>Log Audit</span>
                </a>
            </li>
        </ul>

        <div class="user-profile">
            <div class="avatar">
                {{ strtoupper(substr(auth()->user()->email, 0, 2)) }}
            </div>
            <div class="user-info">
                <span class="user-name">{{ auth()->user()->email }}</span>
                <span class="user-role">{{ auth()->user()->role }}</span>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">
        {{ $slot }}
    </div>

    @livewireScripts
    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        document.addEventListener('livewire:navigated', () => {
            lucide.createIcons();
        });
    </script>
</body>
</html>
