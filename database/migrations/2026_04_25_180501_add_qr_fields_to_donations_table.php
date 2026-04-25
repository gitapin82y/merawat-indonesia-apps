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
   Schema::table('donations', function (Blueprint $table) {
        $table->text('qr_image')->nullable()->after('checkout_url');
        $table->text('qr_content')->nullable()->after('qr_image');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    Schema::table('donations', function (Blueprint $table) {
        $table->dropColumn(['qr_image', 'qr_content']);
    });
    }
};
