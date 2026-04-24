<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Smart Warehouse') }}</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @if(app()->getLocale() == 'ar')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    @endif

    <style>
        :root {
            --bg-body: #f8fafc;
            --text-main: #1e293b;
            --card-bg: rgba(255, 255, 255, 0.7);
            --nav-bg: rgba(255, 255, 255, 0.8);
            --border-color: rgba(0,0,0,0.05);
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            --card-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
            --main-color: #6366f1;
        }

        [data-theme="dark"] {
            --bg-body: #080c14;
            --text-main: #f1f5f9;
            --card-bg: rgba(20, 25, 35, 0.8);
            --nav-bg: rgba(10, 15, 25, 0.85);
            --border-color: rgba(255, 255, 255, 0.08);
            --primary-gradient: linear-gradient(135deg, #818cf8 0%, #c084fc 100%);
            --card-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.4);
            --main-color: #818cf8;
            color-scheme: dark;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            transition: background-color 0.3s, color 0.3s;
        }

        [dir="rtl"] body {
            font-family: 'Cairo', sans-serif;
        }

        .navbar {
            z-index: 1050 !important;
            position: relative !important;
        }

        .dropdown-menu {
            z-index: 1060 !important;
        }

        .premium-nav-custom {
            background: var(--nav-bg) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-color);
        }

        [data-theme="dark"] .premium-nav-custom {
            background-color: #080c14 !important;
            border-color: rgba(255, 255, 255, 0.05) !important;
        }

        [data-theme="dark"] .navbar {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        [data-theme="dark"] .navbar-brand {
            color: #fff !important;
        }

        [data-theme="dark"] .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
        }

        [data-theme="dark"] .nav-link:hover {
            color: #fff !important;
        }
        
        [data-theme="dark"] .lang-switch-auth {
            background-color: rgba(255,255,255,0.05) !important;
            border-color: rgba(255,255,255,0.1) !important;
            color: #fff !important;
        }

        [data-theme="dark"] .lang-switch-auth a.text-muted {
            color: rgba(255, 255, 255, 0.5) !important;
        }

        [data-theme="dark"] .text-muted {
            color: rgba(255, 255, 255, 0.75) !important;
        }

        [data-theme="dark"] .text-slate-800, 
        [data-theme="dark"] .text-slate-700 {
            color: #f1f5f9 !important;
        }

        [data-theme="dark"] .btn-dark-soft {
            background-color: rgba(255, 255, 255, 0.1) !important;
            color: #f1f5f9 !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
        }

        [data-theme="dark"] .btn-dark-soft:hover {
            background-color: rgba(255, 255, 255, 0.15) !important;
            color: #fff !important;
        }

        [data-theme="dark"] .btn-outline-premium {
            border-color: #818cf8 !important;
            color: #818cf8 !important;
        }

        [data-theme="dark"] .btn-outline-premium:hover {
            background-color: #818cf8 !important;
            color: #fff !important;
        }

        [data-theme="dark"] .table {
            color: var(--text-main) !important;
        }

        [data-theme="dark"] .table tr, 
        [data-theme="dark"] .table td, 
        [data-theme="dark"] .table th {
            background-color: transparent !important;
            border-color: var(--border-color) !important;
            color: var(--text-main) !important;
        }

        [data-theme="dark"] .table-hover tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.03) !important;
        }

        [data-theme="dark"] thead th {
            color: rgba(255, 255, 255, 0.6) !important;
        }

        [data-theme="dark"] .form-control,
        [data-theme="dark"] .form-select {
            background-color: rgba(255, 255, 255, 0.05) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            color: #fff !important;
            color-scheme: dark;
        }

        [data-theme="dark"] .form-control::placeholder {
            color: rgba(255, 255, 255, 0.45) !important;
        }

        [data-theme="dark"] .form-control:focus {
            background-color: rgba(255, 255, 255, 0.08) !important;
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15) !important;
        }

        [data-theme="dark"] .modal-content {
            background-color: #0f172a !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }

        [data-theme="dark"] .modal-header,
        [data-theme="dark"] .modal-footer {
            border-color: rgba(255, 255, 255, 0.1) !important;
        }

        [data-theme="dark"] .input-group-text {
            background-color: rgba(255, 255, 255, 0.05) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            color: rgba(255, 255, 255, 0.8) !important;
        }

        .theme-toggle-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            background: var(--border-color);
            color: var(--text-main);
            border: none;
        }
        .theme-toggle-btn:hover {
            transform: scale(1.1);
            background: rgba(99, 102, 241, 0.1);
        }
        .btn-dispatch {
            background: linear-gradient(135deg, #6366f1, #8b5cf6) !important;
            color: white !important;
            border: none !important;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3) !important;
        }
        .btn-dispatch:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4) !important;
            color: white !important;
        }
    </style>

    <script>
        // Theme initialization
        const currentTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', currentTheme);

        function toggleTheme() {
            const theme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
            updateThemeIcon(theme);
        }

        function updateThemeIcon(theme) {
            const icon = document.getElementById('theme-icon');
            if (!icon) return;
            if (theme === 'dark') {
                icon.className = 'bi bi-sun-fill text-warning';
            } else {
                icon.className = 'bi bi-moon-stars-fill text-primary';
            }
        }

        window.addEventListener('DOMContentLoaded', () => {
            updateThemeIcon(localStorage.getItem('theme') || 'light');
        });
    </script>
    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light shadow-sm premium-nav-custom">
            <div class="container">
                <a class="navbar-brand fw-bold" href="{{ url('/') }}" style="background: linear-gradient(135deg, #6366f1, #a855f7); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-size: 1.25rem;">
                    Store.
                </a>
                
                <div class="d-flex align-items-center ms-auto order-md-2 gap-3">
                    <!-- Theme Toggle -->
                    <button class="theme-toggle-btn" onclick="toggleTheme()" id="theme-toggle-btn">
                        <i id="theme-icon" class="bi bi-moon-stars-fill"></i>
                    </button>

                    <!-- Language Switch -->
                    <div class="lang-switch-auth px-3 py-1 bg-light rounded-pill border small fw-bold d-none d-md-flex">
                        <a href="?lang=en" class="text-decoration-none {{ app()->getLocale() == 'en' ? 'text-primary' : 'text-muted' }}">EN</a>
                        <span class="mx-2 text-muted">|</span>
                        <a href="?lang=ar" class="text-decoration-none {{ app()->getLocale() == 'ar' ? 'text-primary' : 'text-muted' }}">AR</a>
                    </div>
                </div>

                <button class="navbar-toggler ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        @auth
                        <li class="nav-item">
                            <a class="nav-link fw-bold {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                                <i class="bi bi-speedometer2 me-1"></i> {{ __('Dashboard') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold {{ request()->routeIs('parcels.index') ? 'active' : '' }}" href="{{ route('parcels.index') }}">
                                <i class="bi bi-arrow-left-right me-1"></i> {{ __('Movements') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold {{ request()->get('type') == 'sender' ? 'active' : '' }}" href="{{ route('contacts.index', ['type' => 'sender']) }}">
                                <i class="bi bi-person-up me-1"></i> {{ __('Senders') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold {{ request()->get('type') == 'recipient' ? 'active' : '' }}" href="{{ route('contacts.index', ['type' => 'recipient']) }}">
                                <i class="bi bi-person-down me-1"></i> {{ __('Recipients') }}
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link fw-bold {{ request()->routeIs('parcel-statuses.*') ? 'active' : '' }}" href="{{ route('parcel-statuses.index') }}">
                                <i class="bi bi-gear me-1"></i> {{ __('Statuses') }}
                            </a>
                        </li>
                        @endauth
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto gap-2">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link fw-bold" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">

                                <a id="navbarDropdown" class="nav-link dropdown-toggle fw-bold" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item py-2" href="{{ route('home') }}">
                                        <i class="bi bi-speedometer2 me-2"></i> {{ __('Dashboard') }}
                                    </a>
                                    <a class="dropdown-item py-2" href="{{ route('parcels.index') }}">
                                        <i class="bi bi-arrow-left-right me-2"></i> {{ __('Movements') }}
                                    </a>
                                    <a class="dropdown-item py-2" href="{{ route('contacts.index', ['type' => 'sender']) }}">
                                        <i class="bi bi-person-up me-2"></i> {{ __('Senders') }}
                                    </a>
                                    <a class="dropdown-item py-2" href="{{ route('contacts.index', ['type' => 'recipient']) }}">
                                        <i class="bi bi-person-down me-2"></i> {{ __('Recipients') }}
                                    </a>
                                    <a class="dropdown-item py-2" href="{{ route('parcel-statuses.index') }}">
                                        <i class="bi bi-gear me-2"></i> {{ __('Status Settings') }}
                                    </a>
                                    <hr class="dropdown-divider">
                                    <a class="dropdown-item py-2" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        <i class="bi bi-box-arrow-right me-2"></i> {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main>
            @yield('content')
        </main>

        <!-- Toast Container -->
        <div id="toast-container" class="position-fixed bottom-0 end-0 p-3" style="z-index: 2000;"></div>
    </div>

    <style>
        .toast-premium {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            color: var(--text-main);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            min-width: 300px;
            overflow: hidden;
            display: flex;
            align-items: center;
            padding: 1rem;
            margin-bottom: 1rem;
            animation: slideIn 0.3s ease-out;
            position: relative;
        }

        .toast-premium.success { border-left: 4px solid #22c55e; }
        .toast-premium.error { border-left: 4px solid #ef4444; }

        .toast-icon {
            font-size: 1.5rem;
            margin-right: 1rem;
        }
        [dir="rtl"] .toast-icon { margin-right: 0; margin-left: 1rem; }

        .toast-content { flex: 1; }
        .toast-title { font-weight: 700; font-size: 0.95rem; }
        .toast-msg { font-size: 0.85rem; opacity: 0.8; }

        .toast-info-trigger {
            cursor: pointer;
            opacity: 0.5;
            transition: opacity 0.2s;
            margin-left: 0.5rem;
        }
        .toast-info-trigger:hover { opacity: 1; }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .error-details-popup {
            background: rgba(15, 23, 42, 0.95);
            color: #fff;
            padding: 0.75rem;
            border-radius: 0.75rem;
            font-size: 0.75rem;
            margin-top: 0.5rem;
            display: none;
        }
    </style>

    <!-- Custom Confirmation Modal -->
    <div id="premium-confirm-modal" class="position-fixed top-0 start-0 w-100 h-100 d-none" style="z-index: 3000; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px); transition: all 0.3s ease;">
        <div class="d-flex align-items-center justify-content-center w-100 h-100 p-3">
            <div class="glass-card rounded-4 p-4 text-center shadow-lg border-0" style="max-width: 400px; animation: modalPop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);">
                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px; background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                    <i class="bi bi-exclamation-triangle-fill fs-1"></i>
                </div>
                <h5 class="fw-bold mb-2 modal-title" style="color: var(--text-main);"></h5>
                <p class="text-muted small mb-4 modal-message"></p>
                <div class="d-flex gap-2">
                    <button id="modal-cancel-btn" class="btn btn-lg rounded-pill px-4 flex-grow-1" style="background: var(--border-color); color: var(--text-main); border: none;">{{ __('Cancel') }}</button>
                    <button id="modal-confirm-btn" class="btn btn-lg rounded-pill px-4 flex-grow-1 fw-bold" style="background: #ef4444; color: white; border: none; box-shadow: 0 10px 20px -5px rgba(239, 68, 68, 0.4);">{{ __('Delete') }}</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes modalPop {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
    </style>

    <script>
        window.showConfirm = function(title, message, onConfirm) {
            const modal = document.getElementById('premium-confirm-modal');
            const titleEl = modal.querySelector('.modal-title');
            const messageEl = modal.querySelector('.modal-message');
            const confirmBtn = document.getElementById('modal-confirm-btn');
            const cancelBtn = document.getElementById('modal-cancel-btn');

            titleEl.innerText = title;
            messageEl.innerText = message;
            modal.classList.remove('d-none');

            const closeModal = () => modal.classList.add('d-none');

            // Handle confirm
            confirmBtn.onclick = () => {
                onConfirm();
                closeModal();
            };

            // Handle cancel
            cancelBtn.onclick = closeModal;
            modal.onclick = (e) => { if(e.target === modal) closeModal(); };
        };

        window.showToast = function(title, msg, type = 'success', details = null) {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast-premium ${type}`;
            
            const icon = type === 'success' ? 'bi-check-circle-fill text-success' : 'bi-exclamation-triangle-fill text-danger';
            
            let detailsHtml = '';
            if (details) {
                detailsHtml = `
                    <i class="bi bi-info-circle toast-info-trigger" onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'block' ? 'none' : 'block'"></i>
                    <div class="error-details-popup">${details}</div>
                `;
            }

            toast.innerHTML = `
                <div class="toast-icon"><i class="bi ${icon}"></i></div>
                <div class="toast-content">
                    <div class="toast-title">${title} ${detailsHtml}</div>
                    <div class="toast-msg">${msg}</div>
                </div>
            `;

            container.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                toast.style.transition = 'all 0.3s';
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        };
    </script>
    @include('parcels.partials.dispatch_modal')
    @stack('modals')
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @include('parcels.partials.dispatch_scripts')
</body>
</html>
