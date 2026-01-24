<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission; // <--- IMPORTANTE: Tu modelo extendido
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
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
                    'ver_panel_admin' => 'Acceso al panel de administración principal',
                ]
            ],

            // --- MÓDULO: SUCURSALES ---
            'sucursales' => [
                'label' => 'Administración de Sucursales',
                'permissions' => [
                    'ver_sucursales'      => 'Ver listado y detalles de sucursales',
                    'crear_sucursales'    => 'Registrar nuevas sucursales',
                    'editar_sucursales'   => 'Modificar datos de sucursales existentes',
                    'eliminar_sucursales' => 'Eliminar o desactivar sucursales',
                    'nuevo_usuario_sucursal' => 'Agregar nuevos empleados a una sucursal',
                    'vincular_usuario_exitente_sucursal' => 'Vincular empleados existentes a una sucursal',
                    'editar_usuario_sucursal' => 'Editar empleado de una sucursal',
                    'eliminar_usuario_sucursal' => 'Eliminar empleados de una sucursal',
                ]
            ],

            // --- MÓDULO: USUARIOS ---
            'usuarios' => [
                'label' => 'Gestión de Personal y Accesos',
                'permissions' => [
                    // Gestión básica
                    'ver_usuarios'      => 'Ver listado de empleados/usuarios',
                    'crear_usuarios'    => 'Registrar nuevos usuarios en el sistema',
                    'editar_usuarios'   => 'Editar información de usuarios',
                    'eliminar_usuarios' => 'Eliminar usuarios del sistema',
                    
                    // Seguridad avanzada
                    'ver_roles_usuario'        => 'Ver los roles asignados a un usuario',
                    'asignar_roles_usuario'    => 'Cambiar/Asignar roles a usuarios',
                    'ver_permisos_usuario'     => 'Ver los permisos directos asignados a un usuario',
                    'asignar_permisos_usuario' => 'Asignar permisos directos (excepciones)',
                ]
            ],

            // --- MÓDULO: ROLES ---
            'roles' => [
                'label' => 'Configuración de Roles y Seguridad',
                'permissions' => [
                    'ver_roles'      => 'Visualizar roles configurados',
                    'crear_roles'    => 'Crear nuevos tipos de roles',
                    'editar_roles'   => 'Modificar permisos de un rol',
                    'eliminar_roles' => 'Eliminar roles del sistema',
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
                        'module'       => $keyModule,   // Ej: 'usuarios'
                        'module_label' => $moduleLabel, // Ej: 'Gestión de Personal...' (NUEVO CAMPO)
                        'guard_name'   => 'web'
                    ]
                );
                
            }
        }
    }
}