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
        Schema::create('manual_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama bank atau e-wallet
            $table->string('account_number'); // Nomor rekening atau ID akun
            $table->string('account_name'); // Nama pemilik rekening
            $table->boolean('is_active')->default(true);
            $table->string('icon')->nullable(); // Path ke icon/logo metode pembayaran
            $table->text('instructions')->nullable(); // Instruksi tambahan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manual_payment_methods', function (Blueprint $table) {
            //
        });
    }
};
