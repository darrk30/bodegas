<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\QueryException;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->successNotification(null)
                ->action(function ($record) {
                    try {
                        $record->delete();
                        Notification::make()
                            ->title('Usuario eliminado')
                            ->success()
                            ->send();
                    } catch (QueryException $e) {
                        Notification::make()
                            ->title('No se puede eliminar')
                            ->body('El usuario tiene registros asociados. Archívelo en su lugar.')
                            ->danger()
                            ->send();
                    }
                })
        ];
    }
}
