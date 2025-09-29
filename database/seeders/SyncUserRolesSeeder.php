<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class SyncUserRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // For each user, sync the Spatie role with the usertype field and output debug info
        \App\Models\User::all()->each(function ($user) {
            if ($user->usertype) {
                $roleName = $user->usertype;
                echo "Assigning role '{$roleName}' to user ID {$user->id}... ";
                $user->syncRoles([$roleName]);
                echo "Done\n";
            } else {
                echo "User ID {$user->id} has no usertype set.\n";
            }
        });
    }
}
