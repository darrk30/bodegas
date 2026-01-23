<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class AdminPermissionsSeeder extends Seeder
{
    public function run()
    {
        // 1. Limpiar caché de permisos
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Permisos ADMIN (granulares)
        $permisos = [

            // 🔐 Acceso panel
            'ver_panel_admin',

            // 🏢 Sucursales
            'ver_sucursales',
            'crear_sucursales',
            'editar_sucursales',
            'eliminar_sucursales',

            // 👥 Usuarios
            'ver_usuarios',
            'crear_usuarios',
            'editar_usuarios',
            'eliminar_usuarios',

            // 🛡 Roles y permisos
            'ver_roles',
            'crear_roles',
            'editar_roles',
            'eliminar_roles',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate([
                'name' => $permiso,
                'guard_name' => 'web',
            ]);
        }
    }
}
