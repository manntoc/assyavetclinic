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
        Schema::create('estimasi_biayas', function (Blueprint $table) {
            $table->id();
            $table->string('kode_estimasi')->unique();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->string('nama_owner')->nullable();
            $table->string('telepon')->nullable();
            $table->string('nama_hewan')->nullable();
            $table->string('jenis_hewan')->nullable();
            $table->string('berat_hewan')->nullable();
            $table->string('dokter')->nullable();
            $table->decimal('subtotal_layanan', 15, 2)->default(0);
            $table->decimal('subtotal_obat', 15, 2)->default(0);
            $table->decimal('diskon', 15, 2)->default(0);
            $table->decimal('pajak', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->json('items')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estimasi_biayas');
    }
};
