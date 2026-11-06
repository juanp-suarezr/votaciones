<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ValidadorCandidatosRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Permisos específicos para validación de candidatos
        $permisos = [
            'validador-candidatos-listar',
            'validador-candidatos-aprobar',
            'validador-candidatos-rechazar',
            'validador-candidatos-historial',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        // 2. Rol específico 'validador-candidatos'
        $rol = Role::firstOrCreate(['name' => 'validador-candidatos', 'guard_name' => 'web']);
        $rol->syncPermissions($permisos);

        // 3. Asignar también al Administrador
        $admin = Role::where('name', 'Administrador')->first();
        if ($admin) {
            $admin->givePermissionTo($permisos);
        }
    }
}

