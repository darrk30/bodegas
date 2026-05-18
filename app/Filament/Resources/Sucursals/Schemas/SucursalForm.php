<?php

namespace App\Filament\Resources\Sucursals\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SucursalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre del Negocio')
                    ->required()
                    ->maxLength(255),

                TextInput::make('slug')
                    ->label('Identificador (Slug)')
                    ->required()
                    ->unique(ignoreRecord: true),
                
                // YA NO PONEMOS EL SELECT DE USUARIOS AQUÍ
            ]);
    }
}