<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Facades\Filament;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected array $permissionsToSync = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->permissionsToSync = [];
        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'permissions_') && is_array($value)) {
                $idsNumericos = array_map('intval', $value);
                $this->permissionsToSync = array_merge($this->permissionsToSync, $idsNumericos);
                unset($data[$key]);
            }
        }
        $data['sucursal_id'] = Filament::getTenant()?->id;
        return $data;
    }

    protected function afterCreate(): void
    {
        if (!empty($this->permissionsToSync)) {
            $this->record->syncPermissions($this->permissionsToSync);
        }
    }
}
