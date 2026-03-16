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
        Schema::create('espay_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('Kode bank/produk dari Espay');
            $table->string('name', 100)->comment('Nama metode pembayaran');
            $table->string('pay_method', 50)->nullable()->comment('Kode metode pembayaran (bank code)');
            $table->string('pay_option', 50)->nullable()->comment('Kode produk pembayaran');
            $table->enum('category', ['virtual_account', 'qris', 'ewallet', 'bank_transfer', 'credit_card', 'other'])
                ->default('other')
                ->comment('Kategori metode pembayaran');
            $table->decimal('fee_amount', 15, 2)->default(0)->comment('Biaya transaksi');
            $table->string('fee_type', 10)->default('flat')->comment('Tipe biaya: flat atau percent');
            $table->boolean('is_active')->default(true)->comment('Status aktif metode pembayaran');
            $table->text('icon_url')->nullable()->comment('URL icon metode pembayaran');
            $table->text('description')->nullable()->comment('Deskripsi metode pembayaran');
            $table->json('additional_info')->nullable()->comment('Informasi tambahan dalam format JSON');
            $table->timestamps();
            
            // Index untuk performa
            $table->index('code');
            $table->index('is_active');
            $table->index(['is_active', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('espay_payment_methods');
    }
};