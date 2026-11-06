<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Columnas en la tabla votantes si no existen
        if (Schema::hasTable('votantes')) {
            Schema::table('votantes', function (Blueprint $table) {
                if (!Schema::hasColumn('votantes', 'is_funcionario')) {
                    $table->boolean('is_funcionario')->default(false)->after('identificacion');
                }
                if (!Schema::hasColumn('votantes', 'dependencia')) {
                    $table->string('dependencia', 255)->nullable()->after('is_funcionario');
                }
                if (!Schema::hasColumn('votantes', 'provision')) {
                    $table->string('provision', 255)->nullable()->after('dependencia');
                }
                if (!Schema::hasColumn('votantes', 'planta')) {
                    $table->string('planta', 100)->nullable()->after('provision');
                }
            });
        }

        // 2. Crear tabla de postulaciones de candidatos
        if (!Schema::hasTable('postulaciones_candidatos')) {
            Schema::create('postulaciones_candidatos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_votante')->nullable()->index();
                $table->unsignedBigInteger('id_evento')->index();
                $table->string('identificacion', 30)->index();
                $table->string('tipo_documento', 20)->default('CC');
                $table->string('nombre', 255);
                $table->string('email', 191)->nullable();
                $table->string('celular', 30)->nullable();
                $table->string('dependencia', 255)->nullable();
                $table->string('provision', 255)->nullable();
                $table->string('planta', 100)->nullable();
                $table->string('tipo_planta', 50)->default('PLANTA CENTRAL'); // PLANTA CENTRAL o SGP
                $table->date('fecha_postulacion');
                $table->boolean('cumple_requisitos')->default(true);
                $table->string('archivo_procuraduria', 255);
                $table->string('archivo_cedula', 255);
                $table->string('foto', 255)->nullable();
                $table->enum('estado', ['Pendiente', 'Aprobado', 'Rechazado'])->default('Pendiente');
                $table->json('motivos_rechazo')->nullable();
                $table->text('observaciones')->nullable();
                $table->unsignedBigInteger('validador_id')->nullable()->index();
                $table->timestamp('fecha_validacion')->nullable();
                $table->string('radicado', 50)->unique();
                $table->timestamps();
            });
        }

        // 3. Crear tabla para auditoria de candidatos
        if (!Schema::hasTable('auditoria_candidatos')) {
            Schema::create('auditoria_candidatos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('postulacion_id')->index();
                $table->unsignedBigInteger('id_votante')->nullable()->index();
                $table->unsignedBigInteger('id_evento')->index();
                $table->unsignedBigInteger('usuario_id')->index(); // Validador
                $table->enum('accion', ['Aprobado', 'Rechazado']);
                $table->json('motivos_rechazo')->nullable();
                $table->text('observaciones')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();
            });
        }

        // 4. Crear el rol validador-candidatos si no existe
        try {
            Role::firstOrCreate(['name' => 'validador-candidatos', 'guard_name' => 'web']);
        } catch (\Throwable $th) {
            // Silencioso si ocurre algún error de conexión
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auditoria_candidatos');
        Schema::dropIfExists('postulaciones_candidatos');

        if (Schema::hasTable('votantes')) {
            Schema::table('votantes', function (Blueprint $table) {
                if (Schema::hasColumn('votantes', 'is_funcionario')) {
                    $table->dropColumn('is_funcionario');
                }
                if (Schema::hasColumn('votantes', 'dependencia')) {
                    $table->dropColumn('dependencia');
                }
                if (Schema::hasColumn('votantes', 'provision')) {
                    $table->dropColumn('provision');
                }
                if (Schema::hasColumn('votantes', 'planta')) {
                    $table->dropColumn('planta');
                }
            });
        }
    }
};
