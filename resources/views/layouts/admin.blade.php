<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VetClinic OS : AssyaVetClinic</title>

    <!-- EARLY SIDEBAR STATE RESOLVER (PREVENT FLICKER / FOUC) -->
    <script>
        (function () {
            try {
                const state = localStorage.getItem('sidebar_state') || 'collapsed';
                if (state !== 'expanded') {
                    document.documentElement.classList.add('sidebar-collapsed');
                }
            } catch (e) {}
        })();
    </script>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 & Icons -->
    <link rel="stylesheet" href="{{ asset('admin/css/bootstrap.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Vendors -->
    <link rel="stylesheet" href="{{ asset('admin/vendors/perfect-scrollbar/perfect-scrollbar.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/vendors/choices.js/choices.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/app.css') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="48x48" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('logo-web.png') }}">
    <!-- FontAwesome -->
    <link href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" rel="stylesheet">

    <!-- COMPANY BRAND COLOR PATTERN & DESIGN SYSTEM SYSTEM -->
    <style>
        :root {
            /* Perusahaan Color Pattern */
            --brand-primary: #2d8aba;
            --brand-primary-hover: #246e96;
            --brand-primary-light: #eef7fc;
            --brand-secondary: #546e7a;
            --brand-secondary-hover: #37474f;
            --brand-bg: #ffffff;
            --brand-border: #e2e8f0;
            --brand-text-dark: #1e293b;
            --brand-text-muted: #64748b;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            background-color: var(--brand-bg) !important;
            color: var(--brand-text-dark) !important;
            -webkit-font-smoothing: antialiased;
        }

        /* TOGGLEABLE & COLLAPSIBLE SIDEBAR BAR */
        #sidebar {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 280px;
            z-index: 1050;
            background: #ffffff;
            border-right: 1px solid var(--brand-border);
            overflow-x: hidden;
        }

        #main {
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-left: 280px;
            background-color: #ffffff;
            min-height: 100vh;
        }

        /* Collapsed Sidebar State */
        html.sidebar-collapsed #sidebar,
        body.sidebar-collapsed #sidebar,
        #sidebar:not(.active) {
            margin-left: -280px !important;
        }

        html.sidebar-collapsed #main,
        body.sidebar-collapsed #main,
        #sidebar:not(.active) + #main,
        #sidebar:not(.active) ~ #main {
            margin-left: 0 !important;
        }

        /* Responsive Sidebar Brand Logo: Tampil saat dibuka, hilang saat ditutup */
        .sidebar-brand-logo {
            max-height: 40px;
            max-width: 170px;
            width: auto;
            height: auto;
            object-fit: contain;
            display: block;
            margin: 0 auto;
            transition: opacity 0.25s ease, transform 0.25s ease;
        }

        #sidebar.active .sidebar-brand-logo {
            opacity: 1;
            visibility: visible;
            transform: scale(1);
        }

        html.sidebar-collapsed .sidebar-brand-logo,
        body.sidebar-collapsed .sidebar-brand-logo,
        #sidebar:not(.active) .sidebar-brand-logo {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transform: scale(0.92);
        }

        @media (max-width: 1199px) {
            #main {
                margin-left: 0 !important;
            }
        }

        /* Custom Brand Color Overrides */
        .text-primary, .kreloses-owner-link {
            color: var(--brand-primary) !important;
        }

        .bg-primary, .btn-primary {
            background-color: var(--brand-primary) !important;
            border-color: var(--brand-primary) !important;
        }

        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--brand-primary-hover) !important;
            border-color: var(--brand-primary-hover) !important;
        }

        .text-secondary {
            color: var(--brand-secondary) !important;
        }

        .bg-secondary {
            background-color: var(--brand-secondary) !important;
        }

        /* Header & Navigation */
        .kreloses-header-title {
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
        }

        .kreloses-tabs-wrapper {
            border-bottom: 1px solid var(--brand-border);
            margin-bottom: 20px;
        }

        .kreloses-tabs {
            display: flex;
            gap: 28px;
            overflow-x: auto;
            margin-bottom: -1px;
            width: 100%;
            border-bottom: 1px solid var(--brand-border);
        }

        .kreloses-tabs .tab-item {
            font-size: 14px;
            font-weight: 500;
            color: var(--brand-secondary);
            padding: 10px 2px 12px 2px;
            border-bottom: 2px solid transparent;
            text-decoration: none;
            cursor: pointer;
            white-space: nowrap;
            background: none;
            border-top: none;
            border-left: none;
            border-right: none;
        }

        .kreloses-tabs .tab-item.tab-item-right,
        .kreloses-tabs .tab-item.ms-auto {
            margin-left: auto !important;
        }

        .kreloses-tabs .tab-item:hover {
            color: var(--brand-primary);
        }

        .kreloses-tabs .tab-item.active {
            color: var(--brand-primary) !important;
            font-weight: 600 !important;
            border-bottom: 2px solid var(--brand-primary) !important;
        }

        /* White Card Container */
        .kreloses-card {
            background: #ffffff;
            border: 1px solid var(--brand-border);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            padding: 24px;
        }

        /* Table Standard */
        .kreloses-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13.5px;
        }

        .kreloses-table th {
            text-align: left;
            padding: 12px 16px;
            font-weight: 600;
            color: var(--brand-text-muted);
            border-bottom: 1px solid var(--brand-border);
            background: #ffffff;
        }

        .kreloses-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .kreloses-table tr:hover {
            background: var(--brand-primary-light);
        }

        /* Badges & Buttons */
        .badge-langsung-datang {
            background-color: var(--brand-secondary);
            color: #ffffff;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 4px;
            text-transform: lowercase;
        }

        .btn-kreloses-blue {
            background-color: var(--brand-primary);
            color: #ffffff !important;
            font-weight: 600;
            font-size: 13px;
            padding: 6px 20px;
            border-radius: 6px;
            border: none;
            transition: background 0.15s ease;
            display: inline-block;
            text-decoration: none;
        }

        .btn-kreloses-blue:hover {
            background-color: var(--brand-primary-hover);
        }

        .btn-kreloses-secondary {
            background-color: var(--brand-secondary);
            color: #ffffff !important;
            font-weight: 600;
            font-size: 13px;
            padding: 6px 20px;
            border-radius: 6px;
            border: none;
            transition: background 0.15s ease;
            display: inline-block;
            text-decoration: none;
        }

        .btn-kreloses-secondary:hover {
            background-color: var(--brand-secondary-hover);
            color: #ffffff !important;
        }

        /* Keyboard Accessibility & Focus Indicator (R-32) */
        :focus-visible {
            outline: 2px solid var(--brand-primary) !important;
            outline-offset: 2px !important;
        }

        .btn-kreloses-blue:focus-visible,
        .btn-kreloses-secondary:focus-visible,
        .sidebar-toggle-btn:focus-visible,
        .kreloses-tabs .tab-item:focus-visible {
            outline: 2px solid var(--brand-primary) !important;
            outline-offset: 2px !important;
            box-shadow: 0 0 0 3px rgba(45, 138, 186, 0.25) !important;
        }

        .service-dot {
            height: 8px;
            width: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
            background-color: var(--brand-primary);
        }

        .sidebar-toggle-btn {
            background: #ffffff;
            border: 1px solid var(--brand-border);
            border-radius: 8px;
            padding: 6px 12px;
            color: var(--brand-primary);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .sidebar-toggle-btn:hover {
            background: var(--brand-primary-light);
            color: var(--brand-primary-hover);
        }

        /* Sidebar Styling Refinement */
        .sidebar-wrapper .menu .sidebar-link {
            border-radius: 8px;
            padding: 10px 14px;
            font-weight: 500;
            color: #475569;
        }

        .sidebar-wrapper .menu .sidebar-item.active > .sidebar-link {
            background-color: var(--brand-primary) !important;
            color: #ffffff !important;
        }

        .sidebar-wrapper .menu .sidebar-item.active > .sidebar-link i {
            color: #ffffff !important;
        }

        /* CHOICES.JS SEARCHABLE DROPDOWN INTEGRATION */
        .choices {
            margin-bottom: 0 !important;
            font-size: 0.875rem !important;
        }
        .choices__inner {
            min-height: 38px !important;
            padding: 4px 10px !important;
            background-color: #ffffff !important;
            border: 1px solid #ced4da !important;
            border-radius: 0.375rem !important;
            font-size: 0.875rem !important;
        }
        .choices.is-focused .choices__inner,
        .choices.is-open .choices__inner {
            border-color: #2d8aba !important;
            box-shadow: 0 0 0 0.2rem rgba(45, 138, 186, 0.2) !important;
        }
        .choices__list--dropdown {
            z-index: 1070 !important;
            border: 1px solid #ced4da !important;
            border-radius: 0.375rem !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
            background-color: #ffffff !important;
        }
        .choices__list--dropdown .choices__item--selectable.is-highlighted {
            background-color: #eef7fc !important;
            color: #2d8aba !important;
        }
        .choices__input {
            background-color: #ffffff !important;
            font-size: 0.875rem !important;
            margin-bottom: 4px !important;
            border: 1px solid #ced4da !important;
            border-radius: 4px !important;
            padding: 6px 10px !important;
        }
        .input-group > .choices {
            position: relative;
            flex: 1 1 auto;
            width: 1%;
            min-width: 0;
        }
        .input-group > .choices .choices__inner {
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
        }
    </style>
</head>

<body>
    @yield('content')

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('admin/vendors/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('admin/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('admin/vendors/choices.js/choices.min.js') }}"></script>
    <script src="{{ asset('admin/js/main.js') }}"></script>

    <!-- SEARCHABLE DROPDOWNS (CHOICES.JS) INITIALIZATION -->
    <script>
        function initSearchableDropdowns(root = document) {
            if (typeof Choices === 'undefined') return;
            root.querySelectorAll('.choices-search, .choices:not([data-choice="active"])').forEach(function(el) {
                if (el.dataset.choiceActive) return;
                el.dataset.choiceActive = 'true';
                new Choices(el, {
                    searchEnabled: true,
                    searchPlaceholderValue: 'Ketik untuk mencari...',
                    itemSelectText: '',
                    shouldSort: false,
                    noResultsText: 'Tidak ditemukan hasil yang cocok',
                    noChoicesText: 'Tidak ada pilihan',
                });
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            initSearchableDropdowns();
        });

        // Auto initialize on modals when opened
        document.addEventListener('shown.bs.modal', function (e) {
            initSearchableDropdowns(e.target);
        });
    </script>

    <!-- SIDEBAR PERSISTENCE (LOCALSTORAGE) & NOTIFICATION SCRIPT -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const toggleBtns = document.querySelectorAll('.sidebar-toggle-btn, .burger-btn, .sidebar-hide');
            
            // Resolve initial state: default 'collapsed', unless user set 'expanded'
            const savedState = localStorage.getItem('sidebar_state') || 'collapsed';
            const shouldBeOpen = savedState === 'expanded';

            if (sidebar) {
                sidebar.classList.toggle('active', shouldBeOpen);
            }
            document.body.classList.toggle('sidebar-collapsed', !shouldBeOpen);
            document.documentElement.classList.toggle('sidebar-collapsed', !shouldBeOpen);

            toggleBtns.forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    if (!sidebar) return;
                    const isNowOpen = sidebar.classList.toggle('active');
                    document.body.classList.toggle('sidebar-collapsed', !isNowOpen);
                    document.documentElement.classList.toggle('sidebar-collapsed', !isNowOpen);
                    localStorage.setItem('sidebar_state', isNowOpen ? 'expanded' : 'collapsed');
                }, true);
            });
        });

        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 1600
            });
        @elseif(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Perhatian!',
                text: '{{ session('error') }}',
                showConfirmButton: true
            });
        @endif
    </script>
    <!-- FLOATING AI ASSISTANT WIDGET -->
    @include('components.ai_assistant')
</body>

</html>