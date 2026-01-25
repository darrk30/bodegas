<?php

namespace App\Policies;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    private function getSuffix(): string
    {
        return Filament::getCurrentPanel()->getId() === 'admin' ? '_admin' : '_pdv';
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('ver_usuarios' . $this->getSuffix());
    }

    public function view(User $user, User $model): bool
    {
        // 1. Permiso básico
        if (! $user->hasPermissionTo('ver_usuarios' . $this->getSuffix())) {
            return false;
        }

        // 2. SEGURIDAD PDV (Tenant Isolation)
        if ($this->getSuffix() === '_pdv') {
            
            // Obtenemos los IDs de las sucursals del Gerente (Usuario Logueado)
            $missucursalsIds = $user->sucursals()->pluck('sucursals.id');

            // Verificamos si el usuario objetivo ($model) pertenece a alguna de esas sucursals
            $esCompanero = $model->sucursals()
                ->whereIn('sucursals.id', $missucursalsIds)
                ->exists();

            // Si NO es mi compañero de sucursal -> NO LO PUEDO VER
            if (! $esCompanero) {
                return false;
            }
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('crear_usuarios' . $this->getSuffix());
    }

    public function update(User $user, User $model): bool
    {
        // 1. Permiso
        if (! $user->hasPermissionTo('editar_usuarios' . $this->getSuffix())) {
            return false;
        }

        // 2. SEGURIDAD PDV
        if ($this->getSuffix() === '_pdv') {
            
            // A. No permitir editar a Super Admins (que suelen tener rol, pero no sucursal)
            if ($model->hasRole('Super Admin') || $model->hasRole('Admin')) {
                return false;
            }

            // B. Verificar que pertenezca a mis sucursals (Igual que en view)
            $missucursalsIds = $user->sucursals()->pluck('sucursals.id');
            
            $esCompanero = $model->sucursals()
                ->whereIn('sucursals.id', $missucursalsIds)
                ->exists();

            if (! $esCompanero) {
                return false;
            }
        }

        return true;
    }

    public function delete(User $user, User $model): bool
    {
        if (! $user->hasPermissionTo('eliminar_usuarios' . $this->getSuffix())) {
            return false;
        }

        // Seguridad: No auto-eliminarse
        if ($user->id === $model->id) {
            return false;
        }

        // SEGURIDAD PDV
        if ($this->getSuffix() === '_pdv') {
            
            // A. No borrar admins globales
            if ($model->hasRole('Super Admin')) {
                return false;
            }

            // B. Solo borrar empleados de mi sucursal
            $missucursalsIds = $user->sucursals()->pluck('sucursals.id');
            
            $esCompanero = $model->sucursals()
                ->whereIn('sucursals.id', $missucursalsIds)
                ->exists();

            if (! $esCompanero) {
                return false;
            }
        }

        return true;
    }

    public function restore(User $user, User $model): bool
    {
        return $user->hasPermissionTo('restaurar_usuarios_admin');
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $user->hasPermissionTo('eliminar_usuarios_admin');
    }
}