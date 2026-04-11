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
        Schema::create('moota_banks', function (Blueprint $table) {
            $table->id();
            $table->string('bank_id', 50)->unique()->comment('ID bank dari Moota (bank_id/token)');
            $table->string('bank_type', 50)->comment('Tipe bank: bca, mandiri, bni, dll');
            $table->string('account_number', 50)->comment('Nomor rekening');
            $table->string('account_name', 100)->comment('Nama pemilik rekening (atas nama)');
            $table->string('label', 100)->nullable()->comment('Label tampilan bank');
            $table->decimal('balance', 15, 2)->default(0)->comment('Saldo terakhir dari Moota');
            $table->boolean('is_active')->default(true)->comment('Status aktif untuk ditampilkan ke donatur');
            $table->boolean('moota_active')->default(true)->comment('Status aktif di sisi Moota');
            $table->timestamp('last_synced_at')->nullable()->comment('Waktu terakhir sync dari Moota');
            $table->timestamps();
 
            $table->index('is_active');
            $table->index('bank_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('moota_banks');
    }
};
