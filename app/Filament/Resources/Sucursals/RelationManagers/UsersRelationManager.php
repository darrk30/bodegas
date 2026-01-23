<?php

namespace App\Filament\Resources\Sucursals\RelationManagers;

// --- TUS IMPORTACIONES NUEVAS ---
use Filament\Forms;
use Filament\Schemas\Schema; // Como pediste
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

// --- ACCIONES GENÉRICAS (SEGÚN TU INDICACIÓN) ---
use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachBulkAction;

use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
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
                Forms\Components\TextInput::make('name')
                    ->label('Nombre Completo')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('password')
                    ->password()
                    ->label('Contraseña')
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->required(fn($livewire) => $livewire instanceof CreateAction)
                    ->visibleOn('create'),

                // SELECTOR DE ROL (Sin dehydrated false para que viaje al using)
                Forms\Components\Select::make('role_id')
                    ->label('Rol en esta Sucursal')
                    ->options(function (RelationManager $livewire) {
                        return Role::where('sucursal_id', $livewire->getOwnerRecord()->id)
                            ->pluck('name', 'id');
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

            // --- HEADER ACTIONS (Botones Superiores) ---
            ->headerActions([
                // --- CREAR ---
                CreateAction::make()
                    ->label('Nuevo Empleado')
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

                // --- VINCULAR ---
                AttachAction::make()
                    ->label('Vincular Existente')
                    ->preloadRecordSelect()

                    // 1. PERSONALIZACIÓN VISUAL
                    // Esto mostrará: "USUARIO: Juan Perez (juan@gmail.com)"
                    ->recordTitle(fn($record) => "USUARIO: {$record->name} ({$record->email})")

                    // 2. BUSQUEDA
                    // Permite buscar escribiendo el nombre o el correo
                    ->recordSelectOptionsQuery(fn($query) => $query->where('users.id', '!=', 1)) // Opcional: Ocultar Super Admin

                    ->schema(fn(AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->searchable(['name', 'email']), // Activa la búsqueda por ambos campos

                        Forms\Components\Select::make('role_id')
                            ->label('Rol a asignar')
                            ->options(function (RelationManager $livewire) {
                                // CORRECCIÓN: 'sucursal_id' (sin espacio) o 'team_id' según tu BD
                                // Si usas Spatie por defecto, debería ser 'team_id'. Si personalizaste, 'sucursal_id'.
                                return Role::where('sucursal_id', $livewire->getOwnerRecord()->id)
                                    ->pluck('name', 'id');
                            })
                            ->required()
                            // ->dehydrated(false)
                    ])
                    ->after(function ($record, array $data, RelationManager $livewire) {
                        if (!empty($data['role_id'])) {
                            setPermissionsTeamId($livewire->getOwnerRecord()->id);
                            $role = Role::find($data['role_id']);
                            if ($role) $record->assignRole($role);
                        }
                    }),
            ])

            // --- RECORD ACTIONS (Acciones de Fila - Antes 'actions') ---
            ->recordActions([
                // EDITAR
                EditAction::make()
                    ->label('Editar')
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

                // QUITAR (Detach)
                DetachAction::make()
                    ->label('Quitar'),
            ])

            // --- TOOLBAR ACTIONS (Acciones Masivas - Antes 'bulkActions') ---
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
