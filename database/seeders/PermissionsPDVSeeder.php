<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission; // <--- IMPORTANTE: Tu modelo extendido
use Spatie\Permission\PermissionRegistrar;

class PermissionsPDVSeeder extends Seeder
{
    public function run()
    {
        // 1. Limpiar caché de permisos
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Definir Estructura con Labels
        // Formato: 'CLAVE_MODULO' => [ 'label' => 'Título Bonito', 'permissions' => [...] ]
        $estructura = [
            
            // --- MÓDULO: ACCESO ---
            'dashboard' => [
                'label' => 'Dashboard Principal', // <--- AQUÍ VA EL TÍTULO VISUAL
                'permissions' => [
                    'ver_dashboard_pdv' => 'Acceso dashboard principal',
                ]
            ],

            // --- MÓDULO: USUARIOS ---
            'usuarios' => [
                'label' => 'Gestión de Usuarios y Accesos',
                'permissions' => [
                    // Gestión básica
                    'ver_usuarios_pdv'      => 'Ver listado de usuarios',
                    'crear_usuarios_pdv'    => 'Registrar nuevos usuarios',
                    'editar_usuarios_pdv'   => 'Editar usuarios',
                    'eliminar_usuarios_pdv' => 'Eliminar usuarios',
                    
                    // Seguridad avanzada
                    'ver_roles_usuario_pdv'        => 'Ver los roles asignados a un usuario',
                    'asignar_roles_usuario_pdv'    => 'Editar roles a usuarios',
                    'ver_permisos_usuario_pdv'     => 'Ver permisos directos asignado a un usuario',
                    'asignar_permisos_usuario_pdv' => 'Asignar permiso directo a usuario',
                ]
            ],

            // --- MÓDULO: ROLES ---
            'roles' => [
                'label' => 'Configuración de Roles y Permisos',
                'permissions' => [
                    'ver_roles_pdv'      => 'Visualizar listado de roles',
                    'crear_roles_pdv'    => 'Crear nuevo rol',
                    'editar_roles_pdv'   => 'Modificar permisos de un rol',
                    'eliminar_roles_pdv' => 'Eliminar role',
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
                        'scope'        => 'sucursal',
                        'guard_name'   => 'web'
                    ]
                );
                
            }
        }
    }
}