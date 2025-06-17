<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVerificationCodesTable extends Migration
{
    public function up()
    {
        Schema::create('verification_codes', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->integer('code');
            $table->timestamp('expires_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('verification_codes');
    }
}
