<?php

namespace App\Filament\Resources\Roles\Schemas;

use App\Models\Permission; 
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema; 
use Illuminate\Support\Str;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        // 1. AQUÍ ESTÁ LA CLAVE: 
        // Las llaves del array deben coincidir con el final de tus permisos en BD.
        // Tú tienes: 'ver_usuarios', 'crear_roles', etc.
        $modulos = [
            'usuarios'    => 'Gestión de Usuarios', // Busca %_usuarios
            'roles'       => 'Gestión de Roles',    // Busca %_roles
            'sucursales'  => 'Gestión de Sucursales', // Busca %_sucursales
            'panel_admin' => 'Acceso al Sistema',     // Busca %_panel_admin
        ];

        // 2. COMPONENTES FIJOS
        $components = [
            Section::make('Detalles del Rol')
                ->description('Configuración principal del rol.')
                ->schema([
                    TextInput::make('name')
                        ->label('Nombre del Rol')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    
                    Hidden::make('guard_name')->default('web'),
                    Hidden::make('sucursal_id')->default(null),
                ])->columns(2),
        ];

        // 3. GENERAR SECCIONES DINÁMICAS
        foreach ($modulos as $key => $label) {
            $components[] = Section::make($label)
                ->compact()
                ->collapsible()
                ->collapsed(false) // Los dejamos abiertos para que veas que sí cargan
                ->schema([
                    CheckboxList::make('permissions_' . $key)
                        ->label('')
                        ->options(function () use ($key) {
                            // Busca permisos que terminen en la clave (ej: %_usuarios)
                            return Permission::where('name', 'LIKE', "%_{$key}")
                                ->pluck('name', 'id')
                                ->map(function ($name) use ($key) {
                                    // Limpieza visual: 'crear_usuarios' -> 'Crear'
                                    // Quitamos el sufijo '_usuarios' y ponemos mayúscula
                                    $accion = str_replace('_' . $key, '', $name);
                                    return Str::headline($accion); 
                                });
                        })
                        ->formatStateUsing(function ($record) use ($key) {
                            if (! $record) return [];
                            // Cargar los permisos que el rol ya tiene guardados
                            return $record->permissions()
                                ->where('name', 'LIKE', "%_{$key}")
                                ->pluck('id')
                                ->toArray();
                        })
                        ->columns(2)
                        ->bulkToggleable()
                        ->searchable(), 
                ]);
        }

        // 4. RETORNO
        return $schema->components($components);
    }
}