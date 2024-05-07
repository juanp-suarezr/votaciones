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
        Schema::create('votos', function (Blueprint $table) {
            $table->primary('id');
            $table->unsignedBigInteger('id_votante');
            $table->unsignedBigInteger('id_candidato');
            $table->unsignedBigInteger('id_evento');
            $table->char('tipo', 100);
            $table->char('estado', 20)->default('activo');
            $table->timestamps();

            $table->foreign('id_votante')->references('id')->on('votantes');
            $table->foreign('id_candidato')->references('id')->on('votantes');
            $table->foreign('id_evento')->references('id')->on('eventos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votos');
    }
};
