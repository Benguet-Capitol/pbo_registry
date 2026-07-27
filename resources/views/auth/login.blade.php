<x-guest-layout>
    <style>
        @keyframes fadeInDown { from { opacity: 0; transform: translateY(-24px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeInUp   { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes scaleIn    { from { opacity: 0; transform: scale(0.96); } to { opacity: 1; transform: scale(1); } }
        @keyframes floatLogo  { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-14px); } }
        @keyframes fadeOut    { from { opacity: 1; } to { opacity: 0; } }
        @keyframes gradientShift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
    </style>

    <div id="pageRoot"
        class="flex-1 flex flex-col items-center justify-center px-4 pt-4 pb-8
                bg-gradient-to-r from-blue-100 via-white to-blue-100 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">

        {{-- Logos + Title --}}
        <div class="text-center mb-8 animate-[fadeInDown_0.55s_cubic-bezier(.22,.68,0,1.2)_both]">
            <div class="flex items-center justify-center gap-6 mb-4">
                <img src="{{ asset('benguetlogo.png') }}" alt="Province of Benguet"
                    class="w-[88px] h-[88px] sm:w-32 sm:h-32 object-contain drop-shadow-md
                            animate-[floatLogo_2s_ease-in-out_infinite]">
                <img src="{{ asset('bagongpilipinaslogo.png') }}" alt="Bagong Pilipinas"
                    class="w-[88px] h-[88px] sm:w-32 sm:h-32 object-contain drop-shadow-md
                            animate-[floatLogo_2s_ease-in-out_0.4s_infinite]">
            </div>
            <h1 class="text-[36px] sm:text-[38px] font-bold text-[#080b13] dark:text-slate-100 leading-[1.1]">
                PBO | REGISTRY
            </h1>
            <p class="text-sm text-[#0e1015] dark:text-slate-300 mt-1">Provincial Budget Office</p>
        </div>

        {{-- Login Card --}}
        <div class="w-full max-w-[450px] bg-white dark:bg-gray-800 rounded-[12px] sm:rounded-[14px]
                    sm:px-9 sm:py-8
                    border border-blue-200 dark:border-gray-700
                    animate-[scaleIn_0.50s_cubic-bezier(.22,.68,0,1.2)_0.15s_both]">

            @if (session('status'))
                <div class="bg-blue-50 border border-blue-200 text-blue-600 rounded-lg px-3.5 py-2.5 text-[13px] mb-4
                            dark:bg-blue-950 dark:border-blue-900 dark:text-blue-300" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-600 rounded-lg px-3.5 py-2.5 text-[13px] mb-4
                            dark:bg-red-950 dark:border-red-900 dark:text-red-300" role="alert">
                    <ul class="list-none space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-6 animate-[fadeInUp_0.45s_ease-out_0.25s_both]">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Sign In</h2>
                <p class="text-sm text-gray-700 dark:text-gray-300 mt-1">Enter your credentials to continue</p>
            </div>

            <form method="POST" action="{{ route('login') }}" onsubmit="return handleSubmit(event)">
                @csrf

                {{-- Username --}}
                <div class="mb-5 animate-[fadeInUp_0.45s_ease-out_0.35s_both]">
                    <label for="username" class="block text-[15px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Username
                    </label>
                    <div class="group relative flex items-center">
                        <span class="absolute left-3.5 text-[13px] pointer-events-none z-10 transition-colors
                                     text-blue-500 dark:text-blue-500
                                     group-focus-within:text-blue-600 dark:group-focus-within:text-blue-400">
                            <i class="fas fa-user text-base"></i>
                        </span>
                        <input
                            id="username" type="text" name="username"
                            value="{{ old('username') }}"
                            placeholder="Enter your username"
                            autocomplete="username" autofocus
                            class="w-full pl-10 pr-3.5 py-2 border rounded-lg text-base outline-none transition-all
                                border-gray-300 text-gray-900 bg-white placeholder:text-gray-500
                                focus:border-2 focus:border-blue-600
                                dark:bg-slate-900 dark:border-gray-700 dark:text-gray-50 dark:focus:border-blue-400
                                [&:invalid:not(:placeholder-shown)]:border-red-600">
                    </div>
                </div>

                {{-- Password --}}
                <div class="mb-7 animate-[fadeInUp_0.45s_ease-out_0.45s_both]">
                    <label for="password" class="block text-[15px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Password
                    </label>
                    <div class="group relative flex items-center">
                        <span class="absolute left-3.5 text-[13px] pointer-events-none z-10 transition-colors
                                     text-blue-500 dark:text-blue-500
                                     group-focus-within:text-blue-600 dark:group-focus-within:text-blue-400">
                            <i class="fas fa-lock text-base"></i>
                        </span>
                        <input
                            id="password" type="password" name="password"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            class="w-full pl-10 pr-[42px] py-2 border rounded-lg text-base outline-none transition-all
                                border-gray-300 text-gray-900 bg-white placeholder:text-gray-500
                                focus:border-2 focus:border-blue-600
                                dark:bg-slate-900 dark:border-gray-700 dark:text-slate-100 dark:focus:border-blue-400
                                [&:invalid:not(:placeholder-shown)]:border-red-600">
                        <button type="button" onclick="togglePassword()" aria-label="Show password"
                                class="absolute right-3 p-1 flex items-center transition-colors
                                    text-gray-500 hover:text-blue-600 focus-visible:text-blue-600
                                    dark:hover:text-blue-400 dark:focus-visible:text-blue-400">
                            <i id="eyeShow" class="fas fa-eye text-sm"></i>
                            <i id="eyeHide" class="fas fa-eye-slash text-sm" style="display:none;"></i>
                        </button>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="animate-[fadeInUp_0.45s_ease-out_0.55s_both]">
                    <button type="submit" id="signinBtn"
                            class="w-full py-2 rounded-lg text-[13px] font-bold tracking-[0.08em] uppercase text-white
                                   bg-gradient-to-r from-blue-700 to-blue-600 dark:from-blue-600 dark:to-blue-500
                                   flex items-center justify-center gap-2
                                   shadow-[0_2px_10px_rgba(37,99,235,0.35)]
                                   transition-[opacity,transform,box-shadow] duration-200
                                   hover:opacity-90 hover:-translate-y-px hover:shadow-[0_6px_20px_rgba(37,99,235,0.45)]
                                   active:translate-y-0 active:opacity-100
                                   disabled:opacity-75 disabled:cursor-not-allowed disabled:translate-y-0">
                        <span id="btnSpinner"
                              class="hidden w-3.5 h-3.5 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>
                        <i id="btnIcon" class="fas fa-sign-in-alt"></i>
                        <span id="btnText">Sign In</span>
                    </button>
                </div>

                @if (Route::has('register'))
                <p class="text-center text-xs text-gray-600 dark:text-gray-400 mt-8">
                    For account assistance,<br>
                    <span class="font-semibold text-gray-600 dark:text-gray-300">Contact your administrator</span>
                </p>
                @endif

            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input   = document.getElementById('password');
            const eyeShow = document.getElementById('eyeShow');
            const eyeHide = document.getElementById('eyeHide');
            const btn     = document.getElementById('password').nextElementSibling;

            const showing = input.type === 'password';
            input.type = showing ? 'text' : 'password';
            eyeShow.style.display = showing ? 'none' : 'inline';
            eyeHide.style.display = showing ? 'inline' : 'none';
            btn.setAttribute('aria-label', showing ? 'Hide password' : 'Show password');
        }

        function handleSubmit(e) {
            const root    = document.getElementById('pageRoot');
            const btn     = document.getElementById('signinBtn');
            const spinner = document.getElementById('btnSpinner');
            const icon    = document.getElementById('btnIcon');
            const text    = document.getElementById('btnText');

            if (btn.disabled) {
                e.preventDefault();
                return false;
            }

            btn.disabled = true;
            icon.classList.add('hidden');
            text.textContent = 'Signing In...';

            if (root) root.classList.add('animate-[fadeOut_0.5s_ease-in_forwards]');
            return true;
        }
    </script>
</x-guest-layout>