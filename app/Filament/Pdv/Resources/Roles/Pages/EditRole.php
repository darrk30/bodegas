<?php

namespace App\Filament\Pdv\Resources\Roles\Pages;

use App\Filament\Pdv\Resources\Roles\RoleResource; // Ajusta namespace
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected array $permissionsToSync = [];

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
    
    protected function beforeFill(): void
    {
        if ($this->record->sucursal_id === null) {
            
            Notification::make()
                ->title('Acceso Denegado')
                ->body('No puedes editar Roles Globales desde la Sucursal.')
                ->danger()
                ->send();
            $this->redirect($this->getResource()::getUrl('index'));
        }
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->permissionsToSync = [];

        // 1. Extraer y limpiar permisos
        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'permissions_') && is_array($value)) {
                $idsNumericos = array_map('intval', $value);
                $this->permissionsToSync = array_merge($this->permissionsToSync, $idsNumericos);
                unset($data[$key]);
            }
        }
        $data['sucursal_id'] = Auth::user()->sucursals()->first()?->id;
        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->syncPermissions($this->permissionsToSync);
    }
}