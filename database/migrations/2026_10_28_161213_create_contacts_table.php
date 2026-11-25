<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->foreignId('user_id')->constrained()->cascadeOnDelete();
            $tabla->string('name');
            $tabla->string('number_phone')->nullable();
            $tabla->softDeletes();
            $tabla->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
