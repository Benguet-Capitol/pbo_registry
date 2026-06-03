<x-guest-layout>
    <style>
        /* ── Animations ─────────────────────────────────────────────── */
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-24px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.96); }
            to   { opacity: 1; transform: scale(1); }
        }
        @keyframes floatLogo {
            0%, 100% { transform: translateY(0); }
            50%      { transform: translateY(-6px); }
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to   { opacity: 0; }
        }
        @keyframes gradientShift {
            0%   { background-position: 0% 50%; }
            50%  { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .anim-header  { animation: fadeInDown 0.55s cubic-bezier(.22,.68,0,1.2) both; }
        .anim-card    { animation: scaleIn    0.50s cubic-bezier(.22,.68,0,1.2) 0.15s both; }
        .anim-field-1 { animation: fadeInUp   0.45s ease-out 0.25s both; }
        .anim-field-2 { animation: fadeInUp   0.45s ease-out 0.35s both; }
        .anim-field-3 { animation: fadeInUp   0.45s ease-out 0.45s both; }
        .anim-field-4 { animation: fadeInUp   0.45s ease-out 0.55s both; }
        .animate-fade-out { animation: fadeOut 0.5s ease-in forwards; }

        .logo-float         { animation: floatLogo 4s ease-in-out infinite; }
        .logo-float-delayed { animation: floatLogo 4s ease-in-out 0.8s infinite; }

        /*
         * The guest layout already provides:
         *   .flex.flex-col.min-h-screen  →  full-height flex column
         *   <x-footer />                 →  flex-shrink-0 at the bottom
         *
         * So the $slot (this file) only needs to fill the remaining space
         * between the top of the column and the footer.
         * We do that with flex-1 on the page-root div.
         */
        .login-page-root {
            flex: 1;                    /* grow to fill space above the footer */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 16px 16px 32px;   /* breathing room top/sides/bottom */
            background: linear-gradient(135deg, #e8eef7 0%, #dce6f5 40%, #e4ebf5 70%, #edf1f8 100%);
            background-size: 400% 400%;
            animation: gradientShift 12s ease infinite;
        }
        .dark .login-page-root {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
        }

        /* ── Card ─────────────────────────────────────────────────── */
        .login-card {
            width: 100%;
            max-width: 480px;
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 4px 32px rgba(59, 80, 130, 0.12), 0 1px 4px rgba(0,0,0,0.06);
            padding: 40px 48px;
        }
        .dark .login-card {
            background: #1e293b;
            box-shadow: 0 4px 32px rgba(0,0,0,0.45);
        }

        /* ── Inputs ───────────────────────────────────────────────── */
        .input-wrap { position: relative; display: flex; align-items: center; }

        .input-icon {
            position: absolute;
            left: 14px;
            color: #6b7280;
            font-size: 13px;
            pointer-events: none;
            z-index: 1;
        }
        .dark .input-icon { color: #9ca3af; }

        .login-input {
            width: 100%;
            padding: 11px 14px 11px 40px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            color: #111827;
            background: #ffffff;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .login-input::placeholder { color: #9ca3af; }
        .login-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }
        .dark .login-input {
            background: #0f172a;
            border-color: #374151;
            color: #f1f5f9;
        }
        .dark .login-input:focus {
            border-color: #60a5fa;
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.15);
        }

        .eye-btn {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            cursor: pointer;
            color: #6b7280;
            padding: 4px;
            display: flex;
            align-items: center;
            transition: color 0.2s;
        }
        .eye-btn:hover { color: #374151; }
        .dark .eye-btn:hover { color: #cbd5e1; }

        /* ── Sign In button ───────────────────────────────────────── */
        .signin-btn {
            width: 100%;
            padding: 12px 0;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #ffffff;
            background: linear-gradient(90deg, #4b5a8a 0%, #3b6fd4 50%, #2563eb 100%);
            cursor: pointer;
            transition: opacity 0.2s, transform 0.15s, box-shadow 0.2s;
            box-shadow: 0 2px 10px rgba(37, 99, 235, 0.35);
        }
        .signin-btn:hover {
            opacity: 0.92;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.45);
        }
        .signin-btn:active { transform: translateY(0); opacity: 1; }

        /* ── Labels ───────────────────────────────────────────────── */
        .field-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }
        .dark .field-label { color: #d1d5db; }

        /* ── Title ────────────────────────────────────────────────── */
        .site-title {
            font-size: 34px;
            font-weight: 800;
            color: #111827;
            letter-spacing: -0.5px;
            line-height: 1.1;
        }
        .dark .site-title  { color: #f1f5f9; }
        .site-subtitle { font-size: 14px; color: #6b7280; margin-top: 4px; }
        .dark .site-subtitle { color: #94a3b8; }

        /* ── Alert boxes ──────────────────────────────────────────── */
        .auth-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            margin-bottom: 16px;
        }
        .dark .auth-error { background: #450a0a; border-color: #7f1d1d; color: #fca5a5; }

        .auth-status {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #16a34a;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            margin-bottom: 16px;
        }
    </style>

    {{--
        This div is the $slot inside the guest layout's flex column.
        flex-1 makes it grow to fill all space between the top of the page
        and the <x-footer /> component, so the footer is always visible
        without any scrolling.
    --}}
    <div id="pageRoot" class="login-page-root">

        {{-- Logos + Title --}}
        <div class="anim-header text-center mb-6">
            <div class="flex items-center justify-center gap-6 mb-5">
                <div class="logo-float">
                    <img src="{{ asset('benguetlogo.png') }}"
                         alt="Province of Benguet"
                         class="w-32 h-32 object-contain drop-shadow-md">
                </div>
                <div class="logo-float-delayed">
                    <img src="{{ asset('bagongpilipinaslogo.png') }}"
                         alt="Bagong Pilipinas"
                         class="w-32 h-32 object-contain drop-shadow-md">
                </div>
            </div>
            <h1 class="site-title">PBO | REGISTRY</h1>
            <p class="site-subtitle">Provincial Budget Office</p>
        </div>

        {{-- Login Card --}}
        <div class="login-card anim-card">

            @if (session('status'))
                <div class="auth-status">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="auth-error">
                    <ul class="list-none space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="anim-field-1 mb-7">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Sign In</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Enter your credentials to continue</p>
            </div>

            <form method="POST" action="{{ route('login') }}" onsubmit="handleSubmit(event)">
                @csrf

                {{-- Username --}}
                <div class="anim-field-2 mb-5">
                    <label for="username" class="field-label">Username</label>
                    <div class="input-wrap">
                        <span class="input-icon"><i class="fas fa-user"></i></span>
                        <input
                            id="username" type="text" name="username"
                            value="{{ old('username') }}"
                            placeholder="Enter your username"
                            autocomplete="off" autofocus
                            class="login-input">
                    </div>
                </div>

                {{-- Password --}}
                <div class="anim-field-3 mb-7">
                    <label for="password" class="field-label">Password</label>
                    <div class="input-wrap">
                        <span class="input-icon"><i class="fas fa-lock"></i></span>
                        <input
                            id="password" type="password" name="password"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            class="login-input" style="padding-right: 42px;">
                        <button type="button" class="eye-btn" onclick="togglePassword()" tabindex="-1">
                            <i id="eyeShow" class="fas fa-eye text-sm"></i>
                            <i id="eyeHide" class="fas fa-eye-slash text-sm" style="display:none;"></i>
                        </button>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="anim-field-4">
                    <button type="submit" class="signin-btn">
                        <i class="fas fa-sign-in-alt mr-2"></i>Sign In
                    </button>
                </div>

                @if (Route::has('register'))
                <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-6">
                    For your account assistance,<br>
                    <span class="font-semibold text-gray-600 dark:text-gray-300">Contact your administrator</span>
                </p>
                @endif

            </form>
        </div>{{-- /card --}}

    </div>{{-- /login-page-root --}}

    <script>
        function togglePassword() {
            const input   = document.getElementById('password');
            const eyeShow = document.getElementById('eyeShow');
            const eyeHide = document.getElementById('eyeHide');
            if (input.type === 'password') {
                input.type = 'text';
                eyeShow.style.display = 'none';
                eyeHide.style.display = 'inline';
            } else {
                input.type = 'password';
                eyeShow.style.display = 'inline';
                eyeHide.style.display = 'none';
            }
        }

        function handleSubmit(e) {
            const root = document.getElementById('pageRoot');
            if (root) root.classList.add('animate-fade-out');
        }
    </script>

</x-guest-layout>