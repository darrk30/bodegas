<?php

namespace App\Observers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Sucursal;

class SucursalObserver
{
    /**
     * Handle the Sucursal "created" event.
     */
    public function created(Sucursal $sucursal): void
    {
        $rolesEstandar = ['Administrador'];
        foreach ($rolesEstandar as $nombreRol) {
            $rol = Role::firstOrCreate([
                'name' => $nombreRol,
                'guard_name' => 'web',
                'sucursal_id' => $sucursal->id
            ]);
            if ($nombreRol === 'Administrador') {
                $permisos = Permission::all();
                if ($permisos->count() > 0) {
                    $rol->syncPermissions($permisos);
                }
            }
        }
    }

    /**
     * Handle the Sucursal "updated" event.
     */
    public function updated(Sucursal $sucursal): void
    {
        //
    }

    /**
     * Handle the Sucursal "deleted" event.
     */
    public function deleted(Sucursal $sucursal): void
    {
        //
    }

    /**
     * Handle the Sucursal "restored" event.
     */
    public function restored(Sucursal $sucursal): void
    {
        //
    }

    /**
     * Handle the Sucursal "force deleted" event.
     */
    public function forceDeleted(Sucursal $sucursal): void
    {
        //
    }
}
