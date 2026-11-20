<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
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
        // 1. Agregar columna actas_asignadas en tabla delegados si no existe
        if (Schema::hasTable('delegados') && !Schema::hasColumn('delegados', 'actas_asignadas')) {
            Schema::table('delegados', function (Blueprint $table) {
                $table->json('actas_asignadas')->nullable()->after('firma');
            });
        }

        // 2. Crear rol 'Supervisor de Evento' y 'Jurado de Evento' y asignar permisos específicos
        try {
            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            $permisosSupervisor = [
                'eventos-listar',
                'analisis-listar',
                'auditoria',
                'actas-comision-listar',
                'actas-comision-ver',
                'actas-comision-exportar',
            ];

            foreach ($permisosSupervisor as $perm) {
                Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
            }

            // Crear o sincronizar rol Supervisor de Evento
            $rolSupervisorEvento = Role::firstOrCreate([
                'name' => 'Supervisor de Evento',
                'guard_name' => 'web'
            ]);
            $rolSupervisorEvento->syncPermissions($permisosSupervisor);

            // Crear o sincronizar rol Jurado de Evento
            $rolJuradoEvento = Role::firstOrCreate([
                'name' => 'Jurado de Evento',
                'guard_name' => 'web'
            ]);
            $rolJuradoEvento->syncPermissions($permisosSupervisor);

            // Asegurar que Administrador y Supervisor general conserven todos los permisos
            $rolAdmin = Role::where('name', 'Administrador')->first();
            if ($rolAdmin) {
                $rolAdmin->givePermissionTo($permisosSupervisor);
            }
        } catch (\Throwable $th) {
            // Manejo silencioso en caso de entorno temporal sin BD
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('delegados') && Schema::hasColumn('delegados', 'actas_asignadas')) {
            Schema::table('delegados', function (Blueprint $table) {
                $table->dropColumn('actas_asignadas');
            });
        }

        try {
            app()[PermissionRegistrar::class]->forgetCachedPermissions();
            $rol = Role::where('name', 'Supervisor de Evento')->first();
            if ($rol) $rol->delete();
            $rolJ = Role::where('name', 'Jurado de Evento')->first();
            if ($rolJ) $rolJ->delete();
        } catch (\Throwable $th) {}
    }
};
