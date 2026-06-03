<section>
    <header class="mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white transition-colors duration-200">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 transition-colors duration-200">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form
        method="post"
        action="{{ route('password.update') }}"
        class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div class="space-y-2 transform transition-all duration-300 ease-in-out hover:scale-[1.01]">
            <x-form.label
                for="current_password"
                :value="__('Current Password')" />

            <x-form.input
                id="current_password"
                name="current_password"
                type="password"
                class="block w-full transition-all duration-200 ease-in-out focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400"
                autocomplete="current-password" />

            <x-form.error :messages="$errors->updatePassword->get('current_password')" />
        </div>

        <div class="space-y-2 transform transition-all duration-300 ease-in-out hover:scale-[1.01]">
            <x-form.label
                for="password"
                :value="__('New Password')" />

            <x-form.input
                id="password"
                name="password"
                type="password"
                class="block w-full transition-all duration-200 ease-in-out focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400"
                autocomplete="new-password" />

            <x-form.error :messages="$errors->updatePassword->get('password')" />
        </div>

        <div class="space-y-2 transform transition-all duration-300 ease-in-out hover:scale-[1.01]">
            <x-form.label
                for="password_confirmation"
                :value="__('Confirm Password')" />

            <x-form.input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                class="block w-full transition-all duration-200 ease-in-out focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400"
                autocomplete="new-password" />

            <x-form.error :messages="$errors->updatePassword->get('password_confirmation')" />
        </div>

        <div class="flex items-center gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <button
                type="submit"
                class="text-blue-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                <i class="fas fa-lock text-xl mr-1 -ml-1 w-5 h-5"></i> {{ __('Save') }}
            </button>

            @if (session('status') === 'password-updated')
            <p
                x-data="{ show: true }"
                x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
                x-init="setTimeout(() => show = false, 2000)"
                class="text-sm text-green-600 dark:text-green-400 font-medium flex items-center gap-2">
                <i class="fas fa-check-circle text-green-500 dark:text-green-400"></i>
                {{ __('Saved.') }}
            </p>
            @endif
        </div>
    </form>
</section>