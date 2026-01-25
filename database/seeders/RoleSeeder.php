<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
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
        $permisosGlobales = Permission::where('scope', 'global')->get();
        $superAdmin->syncPermissions($permisosGlobales);

        /* =====================================================
         |  ROL ADMIN (Administrador general)
         |=====================================================*/

        $admin = Role::firstOrCreate([
            'name'        => 'Admin',
            'guard_name' => 'web',
            'sucursal_id' => null,
        ]);

        // Permisos granulares del Admin
        // ... en RoleSeeder.php

        // Permisos granulares del Admin
        $admin->syncPermissions([
            // 🔐 Acceso
            'ver_panel_admin', // Este estaba bien

            // 🏢 Sucursales (AGREGAR _admin)
            'ver_sucursales_admin',
            'crear_sucursales_admin',
            'editar_sucursales_admin',
            // 'eliminar_sucursales_admin', 

            // 👥 Usuarios (AGREGAR _admin)
            'ver_usuarios_admin',
            'crear_usuarios_admin',
            'editar_usuarios_admin',
            'eliminar_usuarios_admin',

            // 🛡 Roles (AGREGAR _admin)
            'ver_roles_admin',
            'crear_roles_admin',
            'editar_roles_admin',
            // 'eliminar_roles_admin',

            // Y no olvides los de seguridad de usuarios si los necesita el Admin
            'ver_roles_usuario_admin',
            'asignar_roles_usuario_admin',
            // etc...
        ]);
    }
}
