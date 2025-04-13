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
        Schema::create('adsense', function (Blueprint $table) {
            $table->id();
            $table->string('tiktok_pixel')->nullable();
            $table->string('facebook_pixel')->nullable();
            $table->string('google_analytics_tag')->nullable();
            $table->string('meta_token')->nullable();
            $table->string('meta_endpoint')->nullable();
            $table->string('google_ads_id')->nullable();
            $table->string('google_ads_label')->nullable();
            $table->string('tiktok_token')->nullable();
            $table->json('tiktok_endpoint')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adsense');
    }
};
