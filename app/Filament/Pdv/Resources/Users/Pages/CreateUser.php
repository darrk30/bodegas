<?php

namespace App\Filament\Pdv\Resources\Users\Pages;

use App\Filament\Pdv\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        // 1. Obtenemos la Sucursal del Gerente
        $sucursalId = Auth::user()->sucursals()->first()?->id;

        // 2. Recuperamos los datos del formulario (que Filament ignoró por dehydrated:false)
        $rolesIds = $this->data['roles'] ?? [];
        $permissionsIds = $this->data['permissions'] ?? [];

        if ($sucursalId) {
            // A. Vincular Usuario a Sucursal (Si no lo hace el form)
            $this->record->sucursals()->syncWithoutDetaching([$sucursalId]);

            // B. Guardar ROLES inyectando sucursal_id
            if (!empty($rolesIds)) {
                $this->record->roles()->attach($rolesIds, ['sucursal_id' => $sucursalId]);
            }

            // C. Guardar PERMISOS inyectando sucursal_id
            if (!empty($permissionsIds)) {
                // Asumiendo que tu tabla 'model_has_permissions' tiene la columna 'sucursal_id'
                $this->record->permissions()->attach($permissionsIds, ['sucursal_id' => $sucursalId]);
            }
            
        }
    }
}
