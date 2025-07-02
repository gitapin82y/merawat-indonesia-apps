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
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); //tidak wajib login auth untuk donasi
            $table->foreignId('manual_payment_method_id')->nullable()->constrained('manual_payment_methods')->onDelete('set null');
            $table->foreignId('donation_source_id')->nullable()->constrained('donation_sources')->onDelete('set null');
            $table->string('payment_proof')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->boolean('is_contactable')->default(true);
            $table->string('referral_code')->nullable();
            $table->string('name')->nullable();
            $table->string('doa')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->string('phone');
            $table->string('email');
            $table->string('snap_token')->unique();
            $table->integer('unique_code')->nullable();
            $table->integer('amount');
            $table->enum('payment_type', ['payment_gateway', 'manual'])->nullable();
            $table->string('payment_method')->nullable();
            $table->enum('status', ['pending', 'sukses', 'gagal'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
