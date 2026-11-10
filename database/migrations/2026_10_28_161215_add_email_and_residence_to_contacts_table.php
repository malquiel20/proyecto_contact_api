<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $tabla) {
            $tabla->string('email')->nullable()->after('number_phone');
            $tabla->string('residence')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $tabla) {
            $tabla->dropColumn(['email', 'residence']);
        });
    }
};
