<?php

namespace App\Filament\Pdv\Resources\Roles\Schemas;

use App\Models\Permission; // <--- Importante: Tu modelo extendido
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        $components = [
            Section::make('Detalles del Rol')
                ->description('Configuración principal del rol.')
                ->schema([
                    TextInput::make('name')
                        ->label('Nombre del Rol')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    
                    Hidden::make('guard_name')->default('web'),
                    Hidden::make('sucursal_id')->default(null),
                ])->columns(2),
        ];
        $grupos = Permission::all()->groupBy('module');

        // 3. GENERAR SECCIONES DINÁMICAS
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
                        ->options(
                            $permisosDelGrupo->pluck('description', 'id')
                        )
                        ->formatStateUsing(function ($record) use ($moduloKey) {
                            if (! $record) return [];
                            return $record->permissions()
                                ->where('module', $moduloKey)
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