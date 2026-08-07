{{-- Role Login Restriction Modal --}}
<div id="roleRestrictionModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                <i class="fas fa-user-lock text-xl mr-3 -ml-1 w-5 h-5"></i>Role Login Access
            </h3>
            <button onclick="closeRoleRestrictionModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-2xl leading-none">&times;</button>
        </div>

        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
            Toggle a role to temporarily restrict login for all users assigned that role.
        </p>

        <div class="space-y-2">
            @foreach ($roles as $role)
            <div class="flex items-center justify-between py-2 px-3 rounded-lg bg-gray-50 dark:bg-gray-700">
                <span class="text-sm text-gray-700 dark:text-gray-200">{{ $role->name }}</span>
                <form method="POST" action="{{ route('roles.toggle-restriction', $role) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                        class="px-3 py-1 rounded-full text-xs font-semibold transition
                               {{ $role->is_login_restricted
                                    ? 'bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900 dark:text-red-200'
                                    : 'bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900 dark:text-green-200' }}">
                        {{ $role->is_login_restricted ? 'Restricted' : 'Active' }}
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
</div>