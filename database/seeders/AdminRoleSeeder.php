<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminRoleSeeder extends Seeder
{
    public function run()
    {
        /* =====================================================
         |  ROL SUPER ADMIN (Dueño del sistema)
         |=====================================================*/

        // Rol global (sucursal_id = null)
        $superAdmin = Role::firstOrCreate([
            'name'        => 'Super Admin',
            'guard_name' => 'web',
            'sucursal_id' => null,
        ]);

        // TODOS los permisos (actuales y futuros)
        $superAdmin->syncPermissions(Permission::all());

        /* =====================================================
         |  ROL ADMIN (Administrador general)
         |=====================================================*/

        $admin = Role::firstOrCreate([
            'name'        => 'Admin',
            'guard_name' => 'web',
            'sucursal_id' => null,
        ]);

        // Permisos granulares del Admin
        $admin->syncPermissions([
            // 🔐 Acceso
            'ver_panel_admin',

            // 🏢 Sucursales
            'ver_sucursales',
            'crear_sucursales',
            'editar_sucursales',
            // ❌ eliminar_sucursales (opcionalmente bloqueado)

            // 👥 Usuarios
            'ver_usuarios',
            'crear_usuarios',
            'editar_usuarios',
            'eliminar_usuarios',

            // 🛡 Roles
            'ver_roles',
            'crear_roles',
            'editar_roles',
            // ❌ eliminar_roles
        ]);
    }
}
