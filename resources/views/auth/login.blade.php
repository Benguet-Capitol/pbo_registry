<x-guest-layout>
    <style>
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes floatAnimation {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        .animate-slide-in-left {
            animation: slideInLeft 0.6s ease-out;
        }

        .animate-slide-in-right {
            animation: slideInRight 0.6s ease-out;
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out 0.2s both;
        }

        .animate-scale-in {
            animation: scaleIn 0.5s ease-out;
        }

        .animate-float {
            animation: floatAnimation 3s ease-in-out infinite;
        }

        .login-input:focus {
            animation: none;
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }

        .animate-fade-out {
            animation: fadeOut 0.6s ease-in forwards;
        }
    </style>

    <div class="flex items-center justify-center min-h-screen px-4 py-12">
        <div class="w-full max-w-6xl">
            <!-- Main Container with Two Columns -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12 items-center">
                
                <!-- Left Column: Login Form -->
                <div class="animate-slide-in-left">
                    <div class="bg-white dark:bg-dark-eval-1 rounded-lg shadow-lg p-8 md:p-12">
                        <!-- Session Status -->
                        <x-auth-session-status class="mb-6 animate-fade-in-up" :status="session('status')" />

                        <!-- Validation Errors -->
                        <x-auth-validation-errors class="mb-6 animate-fade-in-up" :errors="$errors" />

                        <!-- Form Header -->
                        <div class="mb-8 animate-fade-in-up">
                            <h1 class="text-4xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                                {{ __('Sign In') }}
                            </h1>
                            <p class="text-gray-600 dark:text-gray-400">
                                {{ __('Enter your credentials to continue') }}
                            </p>
                        </div>

                        <form method="POST" action="{{ route('login') }}" onsubmit="handleFormSubmit(event)">
                            @csrf

                            <div class="space-y-6">
                                <!-- Username -->
                                <div class="space-y-2 animate-fade-in-up" style="animation-delay: 0.3s;">
                                    <x-form.label
                                        for="username"
                                        :value="__('Username')" />

                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-user text-blue-500"></i>
                                        </x-slot>

                                        <x-form.input
                                            withicon
                                            id="username"
                                            class="block w-full login-input transition duration-300 focus:ring-2 focus:ring-blue-500"
                                            type="text"
                                            name="username"
                                            :value="old('username')"
                                            placeholder="{{ __('Enter your username') }}"
                                            autocomplete="off"
                                            autofocus />
                                    </x-form.input-with-icon-wrapper>
                                </div>

                                <!-- Password -->
                                <div class="space-y-2 animate-fade-in-up" style="animation-delay: 0.4s;">
                                    <x-form.label
                                        for="password"
                                        :value="__('Password')" />

                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-lock text-blue-500"></i>
                                        </x-slot>

                                        <x-form.input
                                            withicon
                                            id="password"
                                            class="block w-full pr-10 login-input transition duration-300 focus:ring-2 focus:ring-blue-500"
                                            type="password"
                                            name="password"
                                            autocomplete="current-password"
                                            placeholder="{{ __('Enter your password') }}" />
                                        <button type="button" onclick="togglePassword()" tabindex="-1" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition duration-200" style="background: none; border: none;">
                                            <i id="showIcon" class="fa fa-eye fa-lg" style="display: inline;"></i>
                                            <i id="hideIcon" class="fa fa-eye-slash fa-lg" style="display: none;"></i>
                                        </button>
                                    </x-form.input-with-icon-wrapper>
                                </div>

                                <!-- Login Button -->
                                <div class="animate-fade-in-up" style="animation-delay: 0.5s;">
                                    <x-button class="justify-center mt-4 gap-2 w-full py-3 text-base font-semibold transition duration-300 transform hover:scale-105 active:scale-95">
                                        <i class="fas fa-sign-in-alt fa-lg"></i>
                                        <span>{{ __('Sign In') }}</span>
                                    </x-button>
                                </div>

                                <!-- Register Info -->
                                @if (Route::has('register'))
                                <p class="text-sm text-gray-600 dark:text-gray-400 text-center animate-fade-in-up" style="animation-delay: 0.6s;">
                                    {{ __('For your account assistance,') }}<br>
                                    <span class="text-blue-600 dark:text-blue-400 font-medium">
                                        {{ __('Contact your administrator') }}
                                    </span>
                                </p>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Right Column: Logo and System Name -->
                <div class="hidden md:flex flex-col items-center justify-center animate-slide-in-right">
                    <!-- Logo Container -->
                    <div class="mb-8 animate-scale-in">
                        <div class="relative w-40 h-40 animate-float">
                            <img 
                                src="{{ asset('benguetlogo.ico') }}" 
                                alt="System Logo"
                                class="w-full h-full object-contain drop-shadow-lg"
                            />
                        </div>
                    </div>

                    <!-- System Name -->
                    <div class="text-center animate-fade-in-up">
                        <h2 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-gray-100 mb-3">
                            PBO | REGISTRY
                        </h2>
                        <p class="text-lg text-gray-600 dark:text-gray-400 font-medium">
                            Provincial Budget Office
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-500 mt-4">
                            Registry of Appropriations, Allotments, Obligations, Disbursements and Balances
                        </p>
                    </div>

                    <!-- Decorative Elements -->
                    <div class="mt-12 flex gap-4 justify-center">
                        <div class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></div>
                        <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse" style="animation-delay: 0.2s;"></div>
                        <div class="w-2 h-2 rounded-full bg-purple-500 animate-pulse" style="animation-delay: 0.4s;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const showIcon = document.getElementById('showIcon');
            const hideIcon = document.getElementById('hideIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                showIcon.style.display = 'none';
                hideIcon.style.display = 'inline';
            } else {
                passwordInput.type = 'password';
                showIcon.style.display = 'inline';
                hideIcon.style.display = 'none';
            }
        }

        function handleFormSubmit(event) {
            const mainContainer = document.querySelector('.flex.items-center.justify-center');
            if (mainContainer) {
                mainContainer.classList.add('animate-fade-out');
            }
        }
    </script>
</x-guest-layout>
