<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'DMS Login' }}</title>
    
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
            --bg-base: #090d16;
            --bg-surface: rgba(17, 24, 39, 0.7);
            --border-glow: rgba(56, 189, 248, 0.2);
            --color-primary: #38bdf8;
            --color-secondary: #c084fc;
            --color-text-main: #f8fafc;
            --color-text-muted: #94a3b8;
            --font-main: 'Outfit', sans-serif;
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
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: radial-gradient(circle at 50% 50%, rgba(120, 119, 198, 0.15), transparent 60%);
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 1.5rem;
        }

        .card {
            background: var(--bg-surface);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
            padding: 2.5rem;
            box-shadow: 0 10px 40px 0 rgba(0, 0, 0, 0.5);
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .header {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
        }

        .logo-icon {
            width: 54px;
            height: 54px;
            border-radius: 1rem;
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 20px rgba(56, 189, 248, 0.2);
            color: #0f172a;
        }

        .logo-icon i {
            width: 28px;
            height: 28px;
        }

        .title {
            font-size: 1.75rem;
            font-weight: 800;
            background: linear-gradient(135deg, #fff, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.03em;
        }

        .subtitle {
            font-size: 0.85rem;
            color: var(--color-text-muted);
        }

        /* Form elements */
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
            background-color: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: var(--color-text-main);
            padding: 0.8rem 1.1rem;
            border-radius: 0.85rem;
            outline: none;
            transition: 0.2s ease;
            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.15);
        }

        .btn {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.85rem;
            border-radius: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s ease;
            border: none;
            font-size: 1rem;
            background: linear-gradient(135deg, var(--color-primary), #0284c7);
            color: #0f172a;
            margin-top: 1rem;
        }

        .btn:hover {
            filter: brightness(1.1);
            box-shadow: 0 0 20px rgba(56, 189, 248, 0.35);
        }

        .error-message {
            color: #f87171;
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>

    <div class="login-container">
        {{ $slot }}
    </div>

    @livewireScripts
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
