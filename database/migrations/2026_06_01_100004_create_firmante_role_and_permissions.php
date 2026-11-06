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

            // 1. Crear permiso específico para el panel de firma
            $permisoPanel = Permission::firstOrCreate([
                'name' => 'firmante-panel',
                'guard_name' => 'web'
            ]);

            // 2. Permisos que debe tener el rol Firmante
            $permisosFirmante = [
                'firmante-panel',
                'actas-comision-listar',
                'actas-comision-ver',
                'actas-comision-exportar',
            ];

            foreach ($permisosFirmante as $perm) {
                Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
            }

            // 3. Crear el rol Firmante
            $rolFirmante = Role::firstOrCreate([
                'name' => 'Firmante',
                'guard_name' => 'web'
            ]);

            $rolFirmante->syncPermissions($permisosFirmante);

            // 4. Asignar el nuevo permiso también a Administrador y Supervisor
            $rolAdmin = Role::where('name', 'Administrador')->first();
            if ($rolAdmin) {
                $rolAdmin->givePermissionTo('firmante-panel');
            }

            $rolSupervisor = Role::where('name', 'Supervisor')->first();
            if ($rolSupervisor) {
                $rolSupervisor->givePermissionTo('firmante-panel');
            }
        } catch (\Throwable $th) {
            // Manejar silenciosamente en caso de que la conexión a BD no esté disponible
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            $rol = Role::where('name', 'Firmante')->first();
            if ($rol) {
                $rol->delete();
            }

            $permiso = Permission::where('name', 'firmante-panel')->first();
            if ($permiso) {
                $permiso->delete();
            }
        } catch (\Throwable $th) {
        }
    }
};

