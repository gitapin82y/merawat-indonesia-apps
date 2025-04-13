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
        Schema::create('prioritas_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->unique()->constrained('campaigns')->onDelete('cascade');
            $table->integer('prioritas')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prioritas_campaigns');
    }
};
