<?php

namespace App\Filament\Resources\Sucursals\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachBulkAction;
use App\Models\Role;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';
    protected static ?string $title = 'Usuarios de la Sucursal';

    // 1. FORMULARIO (Usando Schema)
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre Completo')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                TextInput::make('password')
                    ->password()
                    ->label('Contraseña')
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->required(fn($livewire) => $livewire instanceof CreateAction)
                    ->visibleOn('create'),

                Select::make('role_id')
                    ->label('Rol en esta Sucursal')
                    ->options(function (RelationManager $livewire) {
                        return Role::where('sucursal_id', $livewire->getOwnerRecord()->id)->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->required()
            ]);
    }

    // 2. TABLA (Usando recordActions y toolbarActions)
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),

                TextColumn::make('email')
                    ->searchable(),

                TextColumn::make('roles_display')
                    ->label('Rol en Tienda')
                    ->getStateUsing(function ($record, RelationManager $livewire) {
                        setPermissionsTeamId($livewire->getOwnerRecord()->id);
                        $record->unsetRelation('roles');
                        return $record->roles->pluck('name')->join(', ');
                    })
                    ->badge()
                    ->color('info'),
            ])
            ->filters([])
            ->headerActions([
                // --- CREAR ---
                CreateAction::make()
                    ->icon('heroicon-o-user-plus') // Ícono de usuario con +
                    ->iconButton()                   // Convierte el botón en solo ícono
                    ->tooltip('Nuevo Empleado')
                    ->using(function (array $data, RelationManager $livewire): Model {
                        $roleId = $data['role_id'] ?? null;
                        unset($data['role_id']);
                        $sucursal = $livewire->getOwnerRecord();
                        $user = $sucursal->users()->create($data);
                        if ($roleId) {
                            setPermissionsTeamId($sucursal->id);
                            $role = Role::find($roleId);
                            if ($role) $user->assignRole($role);
                        }
                        return $user;
                    }),

                AttachAction::make()
                    ->icon('heroicon-o-link')        // Ícono de enlace/cadena
                    ->iconButton()                   // Convierte el botón en solo ícono
                    ->tooltip('Vincular Existente')
                    ->preloadRecordSelect()
                    ->recordTitle(fn($record) => "{$record->name} ({$record->email})")
                    ->recordSelectOptionsQuery(fn($query) => $query->where('users.id', '!=', 1))

                    ->schema(fn(AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->searchable(['name', 'email']),

                        Select::make('role_id')
                            ->label('Rol a asignar')
                            ->options(function (RelationManager $livewire) {
                                return Role::where('sucursal_id', $livewire->getOwnerRecord()->id)
                                    ->pluck('name', 'id');
                            })
                            ->required()
                    ])
                    ->after(function ($record, array $data, RelationManager $livewire) {
                        if (!empty($data['role_id'])) {
                            setPermissionsTeamId($livewire->getOwnerRecord()->id);
                            $role = Role::find($data['role_id']);
                            if ($role) $record->assignRole($role);
                        }
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->icon('heroicon-o-pencil-square')
                    ->iconButton()
                    ->tooltip('Editar')
                    ->mutateRecordDataUsing(function (array $data, $record, RelationManager $livewire): array {
                        setPermissionsTeamId($livewire->getOwnerRecord()->id);
                        $record->unsetRelation('roles');
                        $data['role_id'] = $record->roles->first()?->id;
                        return $data;
                    })
                    ->using(function ($record, array $data, RelationManager $livewire): Model {
                        $roleId = $data['role_id'] ?? null;
                        unset($data['role_id']);
                        if (empty($data['password'])) unset($data['password']);
                        $record->update($data);
                        if ($roleId) {
                            setPermissionsTeamId($livewire->getOwnerRecord()->id);
                            $role = Role::find($roleId);
                            if ($role) $record->syncRoles($role);
                        }
                        return $record;
                    }),
                DetachAction::make()->icon('heroicon-o-trash') // Ícono de basura
                    ->iconButton()             // Convierte en botón redondo solo ícono
                    ->tooltip('Desvincular Usuario')
                    ->color('danger'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
