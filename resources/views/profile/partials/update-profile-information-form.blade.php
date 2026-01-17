<section>
    <header class="mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white transition-colors duration-200">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 transition-colors duration-200">
            {{ __("Update your account's profile information and Username.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form
        method="post"
        action="{{ route('profile.update') }}"
        class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="space-y-2 transform transition-all duration-300 ease-in-out hover:scale-[1.01]">
            <x-form.label
                for="name"
                :value="__('Name')" />

            <x-form.input
                id="name"
                name="name"
                type="text"
                class="block w-full transition-all duration-200 ease-in-out focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400"
                :value="old('name', $user->name)"
                required
                autofocus
                autocomplete="name" />

            <x-form.error :messages="$errors->get('name')" />
        </div>

        <div class="space-y-2 transform transition-all duration-300 ease-in-out hover:scale-[1.01]">
            <x-form.label
                for="username"
                :value="__('Username')" />

            <x-form.input
                id="username"
                name="username"
                type="text"
                class="block w-full transition-all duration-200 ease-in-out focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400"
                :value="old('username', $user->username)"
                required
                autocomplete="username" />

            <x-form.error :messages="$errors->get('username')" />
        </div>

        <div class="flex items-center gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <button
                type="submit"
                class="text-green-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                <i class="fas fa-save text-xl mr-1 -ml-1 w-5 h-5"></i> {{ __('Save') }}
            </button>

            @if (session('status') === 'profile-updated')
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