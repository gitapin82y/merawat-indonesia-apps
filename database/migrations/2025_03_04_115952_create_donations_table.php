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
            $table->string('name')->nullable();
            $table->string('doa')->nullable();
            $table->boolean('is_anonymous')->default(false); //jika orang baik di ceklis maka menjadi true dan nama donatur menjadi "orang baik"
            $table->string('phone');
            $table->string('email');
            $table->string('snap_token')->unique();
            $table->integer('amount');
            $table->enum('payment_type', ['payment_gateway', 'manual']);
            $table->string('payment_method');
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
