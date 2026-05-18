<?php

namespace App\Filament\Pdv\Resources\Roles\Schemas;

use App\Models\Permission;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        $scope = 'sucursal';
        $miSucursalId = Auth::user()->sucursals()->first()?->id;

        $components = [
            Section::make('Detalles del Rol')
                ->description('Configuración principal del rol.')
                ->schema([
                    TextInput::make('name')
                        ->label('Nombre del Rol')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule) use ($miSucursalId) {
                            return $rule->where('sucursal_id', $miSucursalId);
                        }),
                    
                    Hidden::make('guard_name')->default('web'),
                    Hidden::make('sucursal_id')->default($miSucursalId),
                ])->columns(2),
        ];

        // Filtramos permisos visualmente
        $grupos = Permission::where('scope', $scope)->get()->groupBy('module');

        foreach ($grupos as $moduloKey => $permisosDelGrupo) {
            if (empty($moduloKey)) continue;
            $tituloSeccion = $permisosDelGrupo->first()->module_label ?? Str::headline($moduloKey);
            $components[] = Section::make($tituloSeccion)
                ->compact()
                ->collapsible()
                ->collapsed(false)
                ->schema([
                    CheckboxList::make('permissions_' . $moduloKey)
                        ->label('')
                        ->options($permisosDelGrupo->pluck('name', 'id'))
                        ->formatStateUsing(function ($record) use ($moduloKey, $scope) {
                            if (! $record) return [];
                            return $record->permissions()
                                ->where('module', $moduloKey)
                                ->where('scope', $scope)
                                ->pluck('id')
                                ->toArray();
                        })
                        ->columns(2) 
                        ->bulkToggleable()
                        ->searchable(), 
                ]);
        }

        return $schema->components($components);
    }
}