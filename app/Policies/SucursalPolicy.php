<?php

namespace App\Policies;

use App\Models\Sucursal;
use App\Models\User;
use Filament\Facades\Filament; // <--- 1. Importante
use Illuminate\Auth\Access\Response;

class SucursalPolicy
{
    /**
     * Helper para detectar si estamos en Admin o PDV
     */
    private function getSuffix(): string
    {
        return Filament::getCurrentPanel()->getId() === 'admin' ? '_admin' : '_pdv';
    }

    /**
     * Ver listado (Index)
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('ver_sucursales' . $this->getSuffix());
    }

    /**
     * Ver detalle
     */
    public function view(User $user, Sucursal $sucursal): bool
    {
        // 1. Chequeo de Permiso básico
        if (! $user->hasPermissionTo('ver_sucursales' . $this->getSuffix())) {
            return false;
        }

        // 2. SEGURIDAD PDV:
        if ($this->getSuffix() === '_pdv') {
            // CORRECCIÓN: Verificamos en la tabla pivote si el usuario tiene acceso a ESTA sucursal
            $tieneAcceso = $user->sucursals()
                ->where('sucursals.id', $sucursal->id)
                ->exists();

            if (! $tieneAcceso) {
                return false;
            }
        }

        return true;
    }

    /**
     * Crear
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('crear_sucursales' . $this->getSuffix());
    }

    /**
     * Editar
     */
    public function update(User $user, Sucursal $sucursal): bool
    {
        // 1. Permiso
        if (! $user->hasPermissionTo('editar_sucursales' . $this->getSuffix())) {
            return false;
        }

        // 2. SEGURIDAD PDV:
        if ($this->getSuffix() === '_pdv') {
            // CORRECCIÓN: Solo editar si pertenece a mis sucursals asignadas
            $tieneAcceso = $user->sucursals()
                ->where('sucursals.id', $sucursal->id)
                ->exists();

            if (! $tieneAcceso) {
                return false;
            }
        }

        return true;
    }

    /**
     * Eliminar
     */
    public function delete(User $user, Sucursal $sucursal): bool
    {
        if (! $user->hasPermissionTo('eliminar_sucursales' . $this->getSuffix())) {
            return false;
        }

        // SEGURIDAD PDV:
        if ($this->getSuffix() === '_pdv') {
            $tieneAcceso = $user->sucursals()
                ->where('sucursals.id', $sucursal->id)
                ->exists();

            if (! $tieneAcceso) {
                return false;
            }
        }

        return true;
    }

    public function restore(User $user, Sucursal $sucursal): bool
    {
        return $user->hasPermissionTo('restaurar_sucursales_admin');
    }

    public function forceDelete(User $user, Sucursal $sucursal): bool
    {
        return $user->hasPermissionTo('eliminar_sucursales_admin');
    }
}