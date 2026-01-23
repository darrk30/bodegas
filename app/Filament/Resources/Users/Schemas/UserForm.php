<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
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
                    ->relationship('roles', 'name') // Magia de Filament
                    ->multiple() // Un usuario puede tener varios roles
                    ->preload()
                    ->searchable(),

                Select::make('permissions')
                    ->label('Permisos Directos (Excepciones)')
                    ->relationship('permissions', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->helperText('Solo úsalo si necesitas dar un permiso específico sin asignar un rol completo.'),

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
                    ->visible(fn (string $operation, Get $get) => 
                        $operation === 'create' || $get('change_password')
                    )
                    ->required(fn (string $operation, Get $get) => 
                        $operation === 'create' || $get('change_password')
                    )
                    // ENCRIPTAR AL GUARDAR
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn (?string $state) => filled($state)),
            ]);
    }
}