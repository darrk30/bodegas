<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; // Importante para encriptar

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Nombre Completo'),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                // --- SECCIÓN DE ROLES Y PERMISOS ---
                Select::make('roles')
                    ->label('Roles Asignados')
                    ->relationship('roles', 'name', function ($query) {
                        if (!Auth::user()->hasRole('Super Admin')) {
                            return $query->where('name', '!=', 'Super Admin');
                        }
                        return $query;
                    })
                    ->multiple()
                    ->preload()
                    ->searchable()

                    // 1. CUANDO EL CAMPO ESTÁ VACÍO (Cerrado)
                    // Esto es lo que ves si el usuario no tiene roles asignados
                    ->placeholder('Sin roles asignados (Sin datos)')

                    // 2. CUANDO ABRES LA LISTA PERO NO BUSCAS (Texto de ayuda)
                    ->searchPrompt('Escriba para buscar un rol...')

                    // 3. CUANDO BUSCAS Y NO ENCUENTRAS NADA
                    ->noSearchResultsMessage('No existe ningún rol con ese nombre.')

                    // Permisos y Visibilidad
                    ->visible(fn() => Auth::user()->can('ver_roles_usuario'))
                    ->disabled(fn() => !Auth::user()->can('asignar_roles_usuario')),


                // --- SECCIÓN PERMISOS (Con tu descripción bonita) ---
                Select::make('permissions')
                    ->label('Permisos Directos (Excepciones)')
                    // Usamos 'description' para que se vea el título largo que creamos en BD
                    ->relationship('permissions', 'description')
                    ->multiple()
                    ->preload()
                    ->searchable()

                    // 1. CUANDO ESTÁ VACÍO
                    ->placeholder('Sin permisos extras asignados')

                    // 2. CUANDO BUSCAS
                    ->searchPrompt('Busque por nombre o descripción...')
                    ->noSearchResultsMessage('Permiso no encontrado.')

                    ->helperText('Solo usar en casos excepcionales.')
                    ->visible(fn() => Auth::user()->can('ver_permisos_usuario'))
                    ->disabled(fn() => !Auth::user()->can('asignar_permisos_usuario')),

                // --- LÓGICA DE CONTRASEÑA ---
                Toggle::make('change_password')
                    ->label('¿Cambiar contraseña?')
                    ->onColor('success')
                    ->offColor('gray')
                    ->dehydrated(false)
                    ->live()
                    ->hiddenOn('create'),

                TextInput::make('password')
                    ->password()
                    ->label('Contraseña')
                    ->maxLength(255)
                    ->visible(
                        fn(string $operation, Get $get) =>
                        $operation === 'create' || $get('change_password')
                    )
                    ->required(
                        fn(string $operation, Get $get) =>
                        $operation === 'create' || $get('change_password')
                    )
                    // ENCRIPTAR AL GUARDAR
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->dehydrated(fn(?string $state) => filled($state)),
            ]);
    }
}
