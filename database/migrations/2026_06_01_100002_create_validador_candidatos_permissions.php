<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Permisos específicos para el módulo de validación de candidatos
        $permisosCandidatos = [
            'validador-candidatos-listar',
            'validador-candidatos-aprobar',
            'validador-candidatos-rechazar',
            'validador-candidatos-historial',
        ];

        foreach ($permisosCandidatos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        // 2. Rol específico 'validador-candidatos'
        $rolValidador = Role::firstOrCreate(['name' => 'validador-candidatos', 'guard_name' => 'web']);

        // Asignar los permisos específicos al rol validador-candidatos
        $rolValidador->syncPermissions($permisosCandidatos);

        // 3. También asegurar que el rol 'Administrador' tenga estos nuevos permisos
        $rolAdmin = Role::where('name', 'Administrador')->first();
        if ($rolAdmin) {
            $rolAdmin->givePermissionTo($permisosCandidatos);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permisosCandidatos = [
            'validador-candidatos-listar',
            'validador-candidatos-aprobar',
            'validador-candidatos-rechazar',
            'validador-candidatos-historial',
        ];

        // Quitar de los roles
        $rolValidador = Role::where('name', 'validador-candidatos')->first();
        if ($rolValidador) {
            $rolValidador->revokePermissionTo($permisosCandidatos);
        }

        $rolAdmin = Role::where('name', 'Administrador')->first();
        if ($rolAdmin) {
            $rolAdmin->revokePermissionTo($permisosCandidatos);
        }

        // Eliminar los permisos
        Permission::whereIn('name', $permisosCandidatos)->delete();
    }
};

