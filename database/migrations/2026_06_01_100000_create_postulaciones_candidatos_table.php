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
        // 1. Asegurar la columna is_funcionario en la tabla votantes
        if (Schema::hasTable('votantes') && !Schema::hasColumn('votantes', 'is_funcionario')) {
            Schema::table('votantes', function (Blueprint $table) {
                $table->boolean('is_funcionario')->default(false)->after('candidato');
            });
        }

        // 2. Crear tabla de postulaciones de candidatos
        if (!Schema::hasTable('postulaciones_candidatos')) {
            Schema::create('postulaciones_candidatos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_votante')->nullable()->index();
                $table->unsignedBigInteger('id_evento')->nullable()->index();
                $table->string('identificacion', 30)->index();
                $table->string('tipo_documento', 20)->default('CC');
                $table->string('nombre', 255);
                $table->string('email', 191)->nullable();
                $table->string('celular', 30)->nullable();
                $table->string('tipo_planta', 50)->default('PLANTA CENTRAL'); // PLANTA CENTRAL o SGP
                $table->date('fecha_postulacion');
                $table->boolean('cumple_requisitos')->default(true);
                $table->string('archivo_procuraduria', 255);
                $table->string('archivo_cedula', 255);
                $table->string('foto', 255)->nullable();
                $table->enum('estado', ['Pendiente', 'Aprobado', 'Rechazado'])->default('Pendiente');
                $table->text('observaciones')->nullable();
                $table->string('radicado', 50)->unique();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('postulaciones_candidatos');

        if (Schema::hasTable('votantes') && Schema::hasColumn('votantes', 'is_funcionario')) {
            Schema::table('votantes', function (Blueprint $table) {
                $table->dropColumn('is_funcionario');
            });
        }
    }
};

