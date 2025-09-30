<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionRoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset roles and permissions
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('role_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();
        Role::truncate();
        Permission::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Define all models that have full action sets
        $models = [
            'users', 'employees', 'account codes', 'offices', 'office allotment classes',
            'allotment classes', 'funds', 'fund sources', 'sectors', 'programs',
            'appropriations', 'obligations', 'obligation adjustments', 'purchase orders',
            'realignments', 'supplementals', 'disbursement'
        ];

        // Define actions
        $actions = ['view', 'create', 'edit', 'delete', 'manage', 'cancel', 'import'];

        // Generate permissions for models
        foreach ($models as $model) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => $action . ' ' . $model]);
            }
        }

        // Special case: dashboard (only view)
        Permission::firstOrCreate(['name' => 'view dashboard']);

        // Define roles
        $admin = Role::firstOrCreate(['name' => 'Administrator']);
        $developer = Role::firstOrCreate(['name' => 'Developer']);
        $obligation = Role::firstOrCreate(['name' => 'Obligation']);
        $payment = Role::firstOrCreate(['name' => 'Disbursement']);
        $user = Role::firstOrCreate(['name' => 'User']);

        // Assign all permissions to Administrator and Developer
        $admin->syncPermissions(Permission::all());
        $developer->syncPermissions(Permission::all());

        // Obligation role: all permissions except delete, but no disbursement
        $obligationPermissions = Permission::where('name', 'not like', 'delete %')
            ->where('name', 'not like', '%disbursement%')
            ->get();
        $obligation->syncPermissions($obligationPermissions);

        // Payment role: all permissions for disbursement except delete
        $paymentPermissions = Permission::where('name', 'like', '%disbursement%')
            ->where('name', 'not like', 'delete %')
            ->get();
        $payment->syncPermissions($paymentPermissions);

        // User role: only dashboard
        $user->syncPermissions(['view dashboard']);

        // Re-sync all users based on their usertype field
        User::all()->each(function ($user) {
            if ($user->usertype) {
                $user->syncRoles([$user->usertype]);
            }
        });
    }
}
