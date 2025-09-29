<x-guest-layout>
    <x-auth-card>
        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <!-- Validation Errors -->
        <x-auth-validation-errors class="mb-4" :errors="$errors" />

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="grid gap-6">
                <!-- Username -->
                <div class="space-y-2">
                    <x-form.label
                        for="username"
                        :value="__('Username')" />

                    <x-form.input-with-icon-wrapper>
                        <x-slot name="icon">
                            <i class="fas fa-user"></i>
                        </x-slot>

                        <x-form.input
                            withicon
                            id="username"
                            class="block w-full"
                            type="text"
                            name="username"
                            :value="old('username')"
                            placeholder="{{ __('Username') }}"
                            autocomplete="off"
                            autofocus />
                    </x-form.input-with-icon-wrapper>
                </div>

                <!-- Password -->
                    <div class="space-y-2">
                        <x-form.label
                            for="password"
                            :value="__('Password')" />

                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <i class="fas fa-lock"></i>
                            </x-slot>

                            <x-form.input
                                withicon
                                id="password"
                                class="block w-full pr-10"
                                type="password"
                                name="password"
                                autocomplete="current-password"
                                placeholder="{{ __('Password') }}" />
                            <button type="button" onclick="togglePassword()" tabindex="-1" class="absolute inset-y-0 right-0 flex items-center px-2 text-gray-500" style="background: none; border: none;">
                                <i id="showIcon" class="fa fa-eye fa-lg" style="display: inline;"></i>
                                <i id="hideIcon" class="fa fa-eye-slash fa-lg" style="display: none;"></i>
                            </button>
                        </x-form.input-with-icon-wrapper>
                    </div>

                <!-- Remember Me -->
                <!-- <div class="flex items-center justify-between">
                    <label for="remember_me" class="inline-flex items-center">
                        <input
                            id="remember_me"
                            type="checkbox"
                            class="text-gray-500 border-gray-300 rounded focus:border-gray-300 focus:ring focus:ring-gray-500 dark:border-gray-600 dark:bg-dark-eval-1 dark:focus:ring-offset-dark-eval-1"
                            name="remember"
                        >

                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Remember me') }}
                        </span>
                    </label>

                </div> -->

                <div>
                    <x-button class="justify-center mt-4 gap-2">
                        <i class="fas fa-key fa-lg"></i>
                        <span>{{ __('Log in') }}</span>
                    </x-button>
                </div>

                @if (Route::has('register'))
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Don’t have an account? Contact your administrator') }}
                </p>
                @endif
            </div>
        </form>
    </x-auth-card>
</x-guest-layout>

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
    </script>