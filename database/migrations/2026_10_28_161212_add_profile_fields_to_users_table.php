<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('url_avatar')->nullable()->after('id');
            $table->string('phone_number')->nullable()->unique()->after('name');
            $table->string('residence')->nullable()->after('email');
            $table->softDeletes()->after('residence');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['phone_number']);
            $table->dropColumn(['url_avatar', 'phone_number', 'residence', 'deleted_at']);
        });
    }
};
