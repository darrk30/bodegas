<?php

namespace App\Filament\Resources\Sucursals\Pages;

use App\Filament\Resources\Sucursals\SucursalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListSucursals extends ListRecords
{
    protected static string $resource = SucursalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nuevo')->icon(Heroicon::OutlinedPlus),
        ];
    }
}
