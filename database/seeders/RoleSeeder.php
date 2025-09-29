<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::create(['name' => 'admin']);
        $user = Role::create(['name' => 'user']);
        $guest = Role::create(['name' => 'guest']);

        $permissions = [
            'create-users', 'edit-users', 'delete-users', 'view-users',
            'create-roles', 'edit-roles', 'delete-roles', 'view-roles',
            'create-permissions', 'edit-permissions', 'delete-permissions', 'view-permissions',
        ];

        foreach ($permissions as $permission) {
            $perm = Permission::create(['name' => $permission]);
            $admin->permissions()->attach($perm);
        }

        $userPermissions = ['view-users', 'view-roles', 'view-permissions'];
        foreach ($userPermissions as $permission) {
            $perm = Permission::where('name', $permission)->first();
            $user->permissions()->attach($perm);
        }

        $guestPermissions = ['view-users'];
        foreach ($guestPermissions as $permission) {
            $perm = Permission::where('name', $permission)->first();
            $guest->permissions()->attach($perm);
        }
    }
}