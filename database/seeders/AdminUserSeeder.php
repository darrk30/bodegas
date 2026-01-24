<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        // 🔥 IMPORTANTE: fijar team GLOBAL
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);

        // ============================
        // SUPER ADMIN
        // ============================
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make('123123123'),
            ]
        );

        $superAdminRole = Role::where('name', 'Super Admin')->where('sucursal_id', null)->first();

        if ($superAdminRole && ! $superAdmin->hasRole($superAdminRole)) {
            $superAdmin->assignRole($superAdminRole);
        }

        // ============================
        // ADMIN
        // ============================
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name'     => 'Admin Bryan',
                'password' => Hash::make('123123'),
            ]
        );

        $adminRole = Role::where('name', 'Admin Bryan')
            ->where('sucursal_id', null)
            ->first();

        if ($adminRole && ! $admin->hasRole($adminRole)) {
            $admin->assignRole($adminRole);
        }
    }
}
