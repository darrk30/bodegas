<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission; // <--- IMPORTANTE: Tu modelo extendido
use Spatie\Permission\PermissionRegistrar;

class PermissionsAdminSeeder extends Seeder
{
    public function run()
    {
        // 1. Limpiar caché de permisos
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Definir Estructura con Labels
        // Formato: 'CLAVE_MODULO' => [ 'label' => 'Título Bonito', 'permissions' => [...] ]
        $estructura = [
            
            // --- MÓDULO: ACCESO ---
            'panel_admin' => [
                'label' => 'Dashboard Principal', // <--- AQUÍ VA EL TÍTULO VISUAL
                'permissions' => [
                    'ver_panel_admin' => 'Acceso al dashboard',
                ]
            ],

            // --- MÓDULO: SUCURSALES ---
            'sucursales' => [
                'label' => 'Administración de Sucursales',
                'permissions' => [
                    'ver_sucursales_admin'      => 'Ver listado y detalles de sucursales',
                    'crear_sucursales_admin'    => 'Registrar sucursales',
                    'editar_sucursales_admin'   => 'Editar sucursales',
                    'eliminar_sucursales_admin' => 'Eliminar sucursales',
                    'nuevo_usuario_sucursal_admin' => 'Agregar nuevos usuarios a una sucursal',
                    'vincular_usuario_exitente_sucursal_admin' => 'Vincular usuarios existentes a una sucursal',
                    'editar_usuario_sucursal_admin' => 'Editar usuario de una sucursal',
                    'eliminar_usuario_sucursal_admin' => 'Eliminar usuario de una sucursal',
                ]
            ],

            // --- MÓDULO: USUARIOS ---
            'usuarios' => [
                'label' => 'Gestión de Personal y Accesos',
                'permissions' => [
                    // Gestión básica
                    'ver_usuarios_admin'      => 'Ver listado de usuarios',
                    'crear_usuarios_admin'    => 'Registrar usuarios',
                    'editar_usuarios_admin'   => 'Editar usuarios',
                    'eliminar_usuarios_admin' => 'Eliminar usuarios',

                    // Seguridad avanzada
                    'ver_roles_usuario_admin'        => 'Ver los roles asignados a un usuario',
                    'asignar_roles_usuario_admin'    => 'Cambiar/Asignar roles a usuarios',
                    'ver_permisos_usuario_admin'     => 'Ver los permisos directos asignados a un usuario',
                    'asignar_permisos_usuario_admin' => 'Asignar permisos directos (excepciones)',
                ]
            ],

            // --- MÓDULO: ROLES ---
            'roles' => [
                'label' => 'Configuración de Roles y Seguridad',
                'permissions' => [
                    'ver_roles_admin'      => 'Visualizar roles configurados',
                    'crear_roles_admin'    => 'Crear nuevos tipos de roles',
                    'editar_roles_admin'   => 'Modificar permisos de un rol',
                    'eliminar_roles_admin' => 'Eliminar roles del sistema',
                ]
            ],
        ];

        // 3. Crear o Actualizar en la BD (Doble bucle)
        foreach ($estructura as $keyModule => $data) {
            
            // Extraemos el Label y el array de permisos
            $moduleLabel = $data['label'];
            $permissions = $data['permissions'];

            foreach ($permissions as $permissionName => $permissionDesc) {
                
                Permission::updateOrCreate(
                    ['name' => $permissionName], // Busca por nombre técnico
                    [
                        'description'  => $permissionDesc,
                        'module'       => $keyModule,
                        'module_label' => $moduleLabel,
                        'scope'        => 'global',
                        'guard_name'   => 'web'
                    ]
                );
                
            }
        }
    }
}