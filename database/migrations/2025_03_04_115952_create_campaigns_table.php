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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('photo');
            $table->string('title');
            $table->text('description');
            $table->enum('status', ['aktif', 'selesai', 'ditolak', 'validasi', 'berakhir'])->default('validasi');
            $table->date('deadline')->nullable();
            $table->integer('total_donatur')->default(0);
            $table->integer('total_kabar_terbaru')->default(0);
            $table->integer('total_pencairan_dana')->default(0);
            $table->integer('jumlah_pencairan_dana')->default(0);
            $table->integer('jumlah_pencarian')->default(0);
            $table->integer('current_donation')->default(0);
            $table->integer('jumlah_donasi')->default(0);
            $table->integer('jumlah_target_donasi')->nullable();
            $table->string('document_rab');
            $table->string('bukti_pencairan_dana')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
