<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class FirmanteRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Permisos para el panel de firma y actas
        $permisos = [
            'firmante-panel',
            'actas-comision-listar',
            'actas-comision-ver',
            'actas-comision-exportar',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        // 2. Rol específico 'Firmante'
        $rolFirmante = Role::firstOrCreate(['name' => 'Firmante', 'guard_name' => 'web']);
        $rolFirmante->syncPermissions($permisos);

        // 3. Asignar permiso al Administrador y Supervisor
        $admin = Role::where('name', 'Administrador')->first();
        if ($admin) {
            $admin->givePermissionTo('firmante-panel');
        }

        $supervisor = Role::where('name', 'Supervisor')->first();
        if ($supervisor) {
            $supervisor->givePermissionTo('firmante-panel');
        }
    }
}

