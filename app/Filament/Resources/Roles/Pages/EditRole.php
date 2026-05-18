<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected array $permissionsToSync = [];

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->permissionsToSync = [];
        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'permissions_') && is_array($value)) {
                $idsNumericos = array_map('intval', $value);
                $this->permissionsToSync = array_merge($this->permissionsToSync, $idsNumericos);
                unset($data[$key]);
            }
        }
        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->syncPermissions($this->permissionsToSync);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
