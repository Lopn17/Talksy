<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --ink: #1a1a18;
            --ink-2: #5a5a55;
            --ink-3: #9a9a94;
            --surface: #faf9f6;
            --surface-2: #f0ede6;
            --border: rgba(26,26,24,0.12);
            --border-focus: #1a1a18;
            --accent: #2d5a3d;
            --accent-light: #e8f0eb;
            --danger: #b03a2e;
            --danger-light: #fdf0ee;
            --radius: 3px;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--surface);
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 2rem;
        }

        .wrapper {
            width: 100%;
            max-width: 420px;
        }

        .brand {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .brand-mark {
            display: inline-block;
            width: 40px;
            height: 40px;
            background: var(--ink);
            border-radius: 50%;
            margin-bottom: 1rem;
        }

        .brand h1 {
            font-family: 'Instrument Serif', serif;
            font-size: 1.75rem;
            color: var(--ink);
            font-weight: 400;
            letter-spacing: -0.02em;
        }

        .brand p {
            font-size: 0.875rem;
            color: var(--ink-2);
            margin-top: 0.25rem;
            font-weight: 300;
        }

        .card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 2.5rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 500;
            color: var(--ink-2);
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9375rem;
            color: var(--ink);
            background: var(--surface);
            transition: border-color 0.15s, background 0.15s;
            outline: none;
            appearance: none;
        }

        input:focus {
            border-color: var(--border-focus);
            background: #fff;
        }

        input::placeholder {
            color: var(--ink-3);
        }

        /* @if(session('error')) */
        .alert {
            background: var(--danger-light);
            border: 1px solid rgba(176,58,46,0.2);
            border-radius: var(--radius);
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            color: var(--danger);
            margin-bottom: 1.5rem;
        }
        /* @endif */

        .input-wrapper {
            position: relative;
        }

        .input-wrapper input {
            padding-right: 2.75rem;
        }

        .toggle-pw {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            padding: 0.25rem;
            cursor: pointer;
            color: var(--ink-3);
            display: flex;
            align-items: center;
            transition: color 0.15s;
        }

        .toggle-pw:hover { color: var(--ink); }

        .toggle-pw svg { display: block; }

        .input-error {
            font-size: 0.8125rem;
            color: var(--danger);
            margin-top: 0.375rem;
        }

        .forgot {
            text-align: right;
            margin-top: -0.75rem;
            margin-bottom: 1.5rem;
        }

        .forgot a {
            font-size: 0.8125rem;
            color: var(--ink-2);
            text-decoration: none;
            transition: color 0.15s;
        }

        .forgot a:hover { color: var(--ink); }

        .btn-primary {
            width: 100%;
            padding: 0.875rem 1.5rem;
            background: var(--ink);
            color: #fff;
            border: none;
            border-radius: var(--radius);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9375rem;
            font-weight: 500;
            cursor: pointer;
            transition: opacity 0.15s, transform 0.1s;
            letter-spacing: 0.01em;
        }

        .btn-primary:hover { opacity: 0.88; }
        .btn-primary:active { transform: scale(0.99); }

        .divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .divider span {
            font-size: 0.8125rem;
            color: var(--ink-3);
        }

        .register-link {
            text-align: center;
            font-size: 0.9rem;
            color: var(--ink-2);
        }

        .register-link a {
            color: var(--ink);
            font-weight: 500;
            text-decoration: none;
            border-bottom: 1px solid var(--ink);
            padding-bottom: 1px;
            transition: opacity 0.15s;
        }

        .register-link a:hover { opacity: 0.6; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="brand">
            <div style="
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                margin-bottom: 1rem;
            ">
                <div class="brand-mark">
                    <span style="font-family:'Instrument Serif',serif;font-size:2rem;color:#fff;font-weight:400;">T</span>
                </div>
                <span style="font-family:'Instrument Serif',serif;font-size:1.375rem;color:#1a1a18;letter-spacing:-0.02em;font-weight:400;">Talksy</span>
            </div>
            <h1>Login to Talksy</h1>
            <p>Let's Talk Together</p>
        </div>

        <div class="card">
            @if(session('error'))
                <div class="alert">{{ session('error') }}</div>
            @endif

            <form action="/login" method="POST" novalidate>
                @csrf

                <div class="form-group">
                    <label for="email">Email address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="you@example.com"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        autofocus
                    required>
                    @error('email')
                        <p class="input-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="••••••••"
                            autocomplete="current-password"
                        required>
                        <button type="button" class="toggle-pw" onclick="togglePassword('password', this)" aria-label="Show password">
                            <svg id="password-eye-show" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg id="password-eye-hide" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-10-7-10-7a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 10 7 10 7a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="input-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- <div class="forgot">
                    <a href="/forgot-password">Forgot password?</a>
                </div> --}}

                <button type="submit" class="btn-primary">Sign in</button>
            </form>

            {{-- <div class="divider"><span>or</span></div>

            <p class="register-link">
                Don't have an account? <a href="/register">Create one</a>
            </p> --}}
        </div>
    </div>
</body>
<script>
    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        btn.querySelector('[id$="-eye-show"]').style.display = isHidden ? 'none' : 'block';
        btn.querySelector('[id$="-eye-hide"]').style.display = isHidden ? 'block' : 'none';
        btn.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
    }
</script>
</html>