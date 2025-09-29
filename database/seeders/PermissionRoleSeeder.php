<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionRoleSeeder extends Seeder
{
    public function run(): void
    {
        // Define all permissions for all models
        $models = [
            'users', 'employees', 'account codes', 'offices', 'office allotment classes',
            'allotment classes', 'funds', 'fund sources', 'sectors', 'programs',
            'appropriations', 'obligations', 'obligation adjustments', 'purchase orders'
        ];
        // Add 'import' to actions
        $actions = ['view', 'create', 'edit', 'delete', 'manage', 'cancel', 'import'];
        $permissions = [];
        foreach ($models as $model) {
            foreach ($actions as $action) {
                $permissions[] = $action . ' ' . $model;
            }
        }
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Define roles
        $admin = Role::firstOrCreate(['name' => 'Administrator']);
        $developer = Role::firstOrCreate(['name' => 'Developer']);
        $obligation = Role::firstOrCreate(['name' => 'Obligation']);
        $payment = Role::firstOrCreate(['name' => 'Payment']);
        $user = Role::firstOrCreate(['name' => 'User']);

        // Assign all permissions to Administrator and Developer
        $admin->syncPermissions(Permission::all());
        $developer->syncPermissions(Permission::all());

        // Obligation role: all permissions except delete for all models
        $obligationPermissions = Permission::where(function($q) {
            $q->where(function($sub) {
                $sub->where('name', 'like', '%')
                    ->where('name', 'not like', 'delete %');
            });
        })->pluck('id')->toArray();
        $obligation->syncPermissions($obligationPermissions);

        // User role: only view permissions (no import)
        $viewPermissions = Permission::where('name', 'like', 'view %')->get();
        $user->syncPermissions($viewPermissions);
    }
}
