<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

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
                ->unique(ignoreRecord: true), // Importante para editar

            // --- LÓGICA DE CONTRASEÑA ---
            
            Toggle::make('change_password')
                ->label('¿Cambiar contraseña?')
                ->onColor('success')
                ->offColor('gray')
                ->dehydrated(false) 
                ->live()
                ->hiddenOn('create'), // Oculto al crear (siempre es obligatoria)

            TextInput::make('password')
                ->password()
                ->label('Contraseña')
                ->maxLength(255)
                // Visible si es CREAR o si activaron el Toggle
                ->visible(fn (string $operation, Get $get) => 
                    $operation === 'create' || $get('change_password')
                )
                // Requerido con la misma lógica
                ->required(fn (string $operation, Get $get) => 
                    $operation === 'create' || $get('change_password')
                )
                // Solo se guarda si escribieron algo
                ->dehydrated(fn (?string $state) => filled($state)),
            ]);
    }
}
