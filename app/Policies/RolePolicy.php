<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\Response;

class RolePolicy
{
    /**
     * Helper privado para decidir qué sufijo usar.
     */
    private function getSuffix(): string
    {
        return Filament::getCurrentPanel()->getId() === 'admin'
            ? '_admin'
            : '_pdv';
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('ver_roles' . $this->getSuffix());
    }

    public function view(User $user, Role $role): bool
    {
        // 1. Permiso base
        if (! $user->hasPermissionTo('ver_roles' . $this->getSuffix())) {
            return false;
        }

        // 2. SEGURIDAD PDV:
        if ($this->getSuffix() === '_pdv') {
            // Si el rol es global (del sistema), no debería verlo un usuario de sucursal
            // (Opcional: si quieres que vean roles globales pero no editarlos, quita este bloque)
            if ($role->sucursal_id === null) {
                return false;
            }

            // CORRECCIÓN IMPORTANTE:
            // Verificamos si la sucursal de este rol pertenece a las sucursals del usuario
            $tieneAccesoASucursal = $user->sucursals()
                ->where('sucursals.id', $role->sucursal_id)
                ->exists();

            if (! $tieneAccesoASucursal) {
                return false;
            }
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('crear_roles' . $this->getSuffix());
    }

    public function update(User $user, Role $role): bool
    {
        // 1. Permiso
        if (! $user->hasPermissionTo('editar_roles' . $this->getSuffix())) {
            return false;
        }

        // 2. SEGURIDAD PDV
        if ($this->getSuffix() === '_pdv') {
            
            // A. Protección contra edición de roles globales
            if ($role->sucursal_id === null) {
                return false;
            }

            // B. Protección de aislamiento (Tenant Isolation)
            // ¿La sucursal dueña de este rol, está en mi lista de sucursals permitidas?
            $tieneAccesoASucursal = $user->sucursals()
                ->where('sucursals.id', $role->sucursal_id)
                ->exists();

            if (! $tieneAccesoASucursal) {
                return false;
            }
        }

        return true;
    }

    public function delete(User $user, Role $role): bool
    {
        // 1. Permiso
        if (! $user->hasPermissionTo('eliminar_roles' . $this->getSuffix())) {
            return false;
        }

        // 2. SEGURIDAD PDV
        if ($this->getSuffix() === '_pdv') {
            
            // A. No borrar globales
            if ($role->sucursal_id === null) {
                return false;
            }

            // B. No borrar roles de otras sucursals
            $tieneAccesoASucursal = $user->sucursals()
                ->where('sucursals.id', $role->sucursal_id)
                ->exists();

            if (! $tieneAccesoASucursal) {
                return false;
            }
        }

        return true;
    }
}