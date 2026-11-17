<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>VetClinic OS: Autentikasi Sistem</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 & Icons -->
    <link rel="stylesheet" href="{{ asset('admin/css/bootstrap.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- FontAwesome -->
    <link href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" rel="stylesheet">

    <!-- COMPANY BRAND COLOR PATTERN & AUTH DESIGN SYSTEM -->
    <style>
        :root {
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
            background: linear-gradient(135deg, #eef7fc 0%, #ffffff 50%, #e0f2fe 100%) !important;
            color: var(--brand-text-dark) !important;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 24px 16px;
            -webkit-font-smoothing: antialiased;
        }

        .auth-container {
            width: 100%;
            max-width: 450px;
        }

        .auth-container.wide {
            max-width: 500px;
        }

        .kreloses-auth-card {
            background: #ffffff;
            border: 1px solid var(--brand-border);
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(45, 138, 186, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.03);
            padding: 36px 32px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .auth-brand-logo {
            width: 64px;
            height: 64px;
            background: var(--brand-primary-light);
            color: var(--brand-primary);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            margin-bottom: 16px;
            box-shadow: inset 0 0 0 1px rgba(45, 138, 186, 0.2);
        }

        .auth-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--brand-primary);
            letter-spacing: -0.5px;
        }

        .auth-subtitle {
            font-size: 13.5px;
            color: var(--brand-secondary);
            margin-top: 4px;
        }

        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }

        .input-group-text {
            background-color: #f8fafc;
            border-color: var(--brand-border);
            color: var(--brand-text-muted);
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
            padding-left: 14px;
            padding-right: 14px;
        }

        .form-control {
            border-color: var(--brand-border);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 14px;
            transition: all 0.15s ease-in-out;
        }

        .input-group .form-control:not(:first-child) {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        .input-group .form-control:not(:last-child) {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .form-control:focus {
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 3px rgba(45, 138, 186, 0.15);
        }

        .password-toggle-btn {
            background-color: #f8fafc;
            border: 1px solid var(--brand-border);
            border-left: none;
            color: var(--brand-text-muted);
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
            padding: 0 14px;
            cursor: pointer;
            transition: color 0.15s ease;
        }

        .password-toggle-btn:hover {
            color: var(--brand-primary);
        }

        .btn-kreloses-blue {
            background-color: var(--brand-primary);
            color: #ffffff !important;
            font-weight: 600;
            font-size: 14px;
            padding: 11px 24px;
            border-radius: 8px;
            border: none;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            width: 100%;
        }

        .btn-kreloses-blue:hover {
            background-color: var(--brand-primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(45, 138, 186, 0.25);
        }

        .form-check-input:checked {
            background-color: var(--brand-primary);
            border-color: var(--brand-primary);
        }

        .auth-link {
            color: var(--brand-primary);
            font-weight: 600;
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .auth-link:hover {
            color: var(--brand-primary-hover);
            text-decoration: underline;
        }

        .auth-footer {
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px solid #f1f5f9;
            text-align: center;
            font-size: 13.5px;
            color: var(--brand-text-muted);
        }
    </style>
</head>
<body>
    <div class="auth-container {{ $attributes->get('class') }}">
        {{ $slot }}
    </div>

    <!-- Scripts -->
    <script src="{{ asset('admin/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye-fill');
                icon.classList.add('bi-eye-slash-fill');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash-fill');
                icon.classList.add('bi-eye-fill');
            }
        }
    </script>
</body>
</html>

