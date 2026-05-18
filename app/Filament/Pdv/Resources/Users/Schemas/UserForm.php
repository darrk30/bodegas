<?php

namespace App\Filament\Pdv\Resources\Users\Schemas;

use App\Models\Permission;
use App\Models\Role;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        // Obtener el ID de la sucursal actual del usuario logueado (Gerente/Admin de tienda)
        $miSucursalId = Auth::user()->sucursals()->first()?->id;

        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre del Empleado')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                // --- SECCIÓN DE ROLES (FILTRADO POR SUCURSAL) ---
                Select::make('roles')
                    ->label('Rol en Tienda')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->required()
                    // A. Cargar opciones filtradas
                    ->options(function () use ($miSucursalId) {
                        return Role::where('sucursal_id', $miSucursalId)->pluck('name', 'id');
                    })
                    // B. Cargar datos existentes al editar (Hydrate)
                    ->afterStateHydrated(function ($component, $record) use ($miSucursalId) {
                        if (! $record) return;
                        // Recuperar solo los roles de ESTA sucursal
                        $rolesIds = $record->roles()
                            ->wherePivot('sucursal_id', $miSucursalId)
                            ->pluck('roles.id')
                            ->toArray();
                        $component->state($rolesIds);
                    })
                    // C. IMPORTANTE: NO DEJAR QUE FILAMENT GUARDE ESTO
                    ->dehydrated(false)
                    ->visible(fn() => Auth::user()->can('ver_roles_usuario_pdv'))
                    ->disabled(fn() => !Auth::user()->can('asignar_roles_usuario_pdv')),

                // --- SECCIÓN SUCURSAL (OCULTA Y AUTOMÁTICA) ---
                // Esto vincula al nuevo usuario con la sucursal actual automáticamente
                Select::make('sucursals')
                    ->relationship('sucursals', 'name')
                    ->default([$miSucursalId])
                    ->hidden() // El usuario no necesita ver esto, es automático
                    ->required(),

                // --- SECCIÓN PERMISOS EXTRAS (Opcional) ---
               Select::make('permissions')
                    ->label('Permisos Adicionales')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->placeholder('Sin permisos extras')
                    // A. Cargar opciones
                    ->options(function () {
                        return Permission::where('scope', 'sucursal')->pluck('description', 'id');
                    })
                    // B. Cargar datos existentes
                    ->afterStateHydrated(function ($component, $record) use ($miSucursalId) {
                        if (! $record) return;
                        $permIds = $record->permissions()
                             // Asumiendo que permissions también tiene pivote con sucursal_id?
                             // Si no usas sucursal_id en permission_user, usa relationship normal.
                             // PERO si usas 'team_id' o 'sucursal_id' en permissions, haz esto:
                            ->wherePivot('sucursal_id', $miSucursalId) 
                            ->pluck('permissions.id')
                            ->toArray();
                        $component->state($permIds);
                    })
                    // C. NO GUARDAR AUTOMÁTICO
                    ->dehydrated(false)
                    ->visible(fn() => Auth::user()->can('ver_permisos_usuario_pdv'))
                    ->disabled(fn() => !Auth::user()->can('asignar_permisos_usuario_pdv')),

                // --- LÓGICA DE CONTRASEÑA (Igual que Admin) ---
                Toggle::make('change_password')
                    ->label('¿Asignar/Cambiar contraseña?')
                    ->onColor('success')
                    ->offColor('gray')
                    ->dehydrated(false)
                    ->live()
                    ->default(fn(string $operation) => $operation === 'create') // Activado por defecto al crear
                    ->hiddenOn('create'), // Oculto al crear (siempre se pide), visible al editar

                TextInput::make('password')
                    ->password()
                    ->label('Contraseña')
                    ->maxLength(255)
                    ->visible(fn(string $operation, Get $get) => $operation === 'create' || $get('change_password'))
                    ->required(fn(string $operation, Get $get) => $operation === 'create' || $get('change_password'))
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->dehydrated(fn(?string $state) => filled($state)),
            ]);
    }
}
