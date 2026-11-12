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
        if (Schema::hasTable('delegados') && !Schema::hasColumn('delegados', 'orden')) {
            Schema::table('delegados', function (Blueprint $table) {
                $table->integer('orden')->default(0)->after('estado');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('delegados') && Schema::hasColumn('delegados', 'orden')) {
            Schema::table('delegados', function (Blueprint $table) {
                $table->dropColumn('orden');
            });
        }
    }
};

