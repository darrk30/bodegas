<?php

namespace App\Filament\Resources\Sucursals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn; // Importante importar los tipos de columna
use Filament\Tables\Table;

class SucursalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // 1. Nombre de la sucursal
                TextColumn::make('name')
                    ->label('Nombre Comercial')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                // 2. El Slug (útil para identificar la URL o ID único)
                TextColumn::make('slug')
                    ->label('ID / Slug')
                    ->icon('heroicon-m-globe-alt')
                    ->color('gray')
                    ->copyable(),

                // 4. Fecha de creación (siempre útil en auditoría)
                TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Aquí podrías filtrar por fecha más adelante
            ])
            ->recordActions ([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}