<?php

namespace App\Filament\Pdv\Resources\Users\Pages;

use App\Filament\Pdv\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function afterSave(): void
    {
        $sucursalId = Auth::user()->sucursals()->first()?->id;
        
        // Datos del formulario
        $rolesIds = $this->data['roles'] ?? [];
        $permissionsIds = $this->data['permissions'] ?? [];

        if ($sucursalId) {
            // --- ROLES ---
            // 1. Borrar roles viejos SOLO de esta sucursal
            $this->record->roles()->wherePivot('sucursal_id', $sucursalId)->detach();
            
            // 2. Insertar roles nuevos con el ID
            if (!empty($rolesIds)) {
                $this->record->roles()->attach($rolesIds, ['sucursal_id' => $sucursalId]);
            }

            // --- PERMISOS ---
            // 1. Borrar permisos viejos SOLO de esta sucursal
            $this->record->permissions()->wherePivot('sucursal_id', $sucursalId)->detach();

            // 2. Insertar permisos nuevos con el ID
            if (!empty($permissionsIds)) {
                $this->record->permissions()->attach($permissionsIds, ['sucursal_id' => $sucursalId]);
            }

        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
