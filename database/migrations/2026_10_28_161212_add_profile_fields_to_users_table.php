<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $tabla) {
            $tabla->string('url_avatar')->nullable()->after('id');
            $tabla->string('phone_number')->nullable()->unique()->after('name');
            $tabla->string('residence')->nullable()->after('email');
            $tabla->softDeletes()->after('residence');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $tabla) {
            $tabla->dropUnique(['phone_number']);
            $tabla->dropColumn(['url_avatar', 'phone_number', 'residence', 'deleted_at']);
        });
    }
};
