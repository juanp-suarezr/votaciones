<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Columnas en tabla proyectos para trazabilidad con GREEN
        if (Schema::hasTable('proyectos')) {
            Schema::table('proyectos', function (Blueprint $table) {
                if (!Schema::hasColumn('proyectos', 'id_postulacion_green')) {
                    $table->unsignedBigInteger('id_postulacion_green')->nullable()->index()->after('id');
                }
                if (!Schema::hasColumn('proyectos', 'id_convocatoria_green')) {
                    $table->unsignedBigInteger('id_convocatoria_green')->nullable()->index()->after('id_postulacion_green');
                }
                if (!Schema::hasColumn('proyectos', 'estado_green')) {
                    $table->string('estado_green', 50)->nullable()->after('estado');
                }
            });
        }

        // 2. Tabla de Auditoría y Trazabilidad de Integración con GREEN
        if (!Schema::hasTable('pp_integracion_logs')) {
            Schema::create('pp_integracion_logs', function (Blueprint $table) {
                $table->id();
                $table->string('tipo', 50)->index(); // INBOUND_SYNC, OUTBOUND_RESULTADOS, WEBHOOK
                $table->unsignedBigInteger('id_convocatoria_green')->nullable()->index();
                $table->unsignedBigInteger('id_evento_vota')->nullable()->index();
                $table->unsignedBigInteger('id_proyecto_local')->nullable()->index();
                $table->unsignedBigInteger('id_postulacion_green')->nullable()->index();
                $table->string('endpoint', 255)->nullable();
                $table->string('metodo', 10)->default('GET');
                $table->json('payload_enviado')->nullable();
                $table->longText('respuesta_recibida')->nullable();
                $table->integer('http_status')->nullable();
                $table->string('estado', 30)->default('PENDIENTE')->index(); // EXITOSO, FALLIDO, REINTENTANDO, PENDIENTE
                $table->integer('intentos')->default(1);
                $table->integer('max_intentos')->default(5);
                $table->text('ultimo_error')->nullable();
                $table->timestamp('proximo_reintento_at')->nullable();
                $table->timestamps();
            });
        }

        // 3. Tabla de Homologación de Comunas / Corregimientos
        if (!Schema::hasTable('pp_homologacion_comunas')) {
            Schema::create('pp_homologacion_comunas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('green_comuna_id')->unique();
                $table->string('green_nombre', 150);
                $table->string('green_tipo', 50)->nullable(); // Urbano, Rural
                $table->integer('green_grupo')->nullable();
                $table->unsignedBigInteger('vota_comuna_id')->nullable()->index(); // ID en parametros_detalle
                $table->string('vota_nombre', 150)->nullable();
                $table->boolean('homologado_manual')->default(false);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pp_homologacion_comunas');
        Schema::dropIfExists('pp_integracion_logs');

        if (Schema::hasTable('proyectos')) {
            Schema::table('proyectos', function (Blueprint $table) {
                if (Schema::hasColumn('proyectos', 'id_postulacion_green')) {
                    $table->dropColumn('id_postulacion_green');
                }
                if (Schema::hasColumn('proyectos', 'id_convocatoria_green')) {
                    $table->dropColumn('id_convocatoria_green');
                }
                if (Schema::hasColumn('proyectos', 'estado_green')) {
                    $table->dropColumn('estado_green');
                }
            });
        }
    }
};

