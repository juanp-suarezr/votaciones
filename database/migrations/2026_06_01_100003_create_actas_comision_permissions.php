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
        try {
            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            $permisosActas = [
                'actas-comision-listar',
                'actas-comision-ver',
                'actas-comision-exportar',
                'actas-comision-firmantes',
            ];

            foreach ($permisosActas as $permiso) {
                Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
            }

            // Asignar al rol validador-candidatos
            $rolValidador = Role::where('name', 'validador-candidatos')->first();
            if ($rolValidador) {
                $rolValidador->givePermissionTo($permisosActas);
            }

            // Asignar al rol Supervisor (si existe)
            $rolSupervisor = Role::where('name', 'Supervisor')->first();
            if ($rolSupervisor) {
                $rolSupervisor->givePermissionTo($permisosActas);
            }

            // Asignar al rol Administrador
            $rolAdmin = Role::where('name', 'Administrador')->first();
            if ($rolAdmin) {
                $rolAdmin->givePermissionTo($permisosActas);
            }
        } catch (\Throwable $th) {
            // Log si la base de datos no está activa en el momento de crear el archivo
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            $permisosActas = [
                'actas-comision-listar',
                'actas-comision-ver',
                'actas-comision-exportar',
                'actas-comision-firmantes',
            ];

            foreach ($permisosActas as $permiso) {
                Permission::where('name', $permiso)->delete();
            }
        } catch (\Throwable $th) {
            // Ignore
        }
    }
};

