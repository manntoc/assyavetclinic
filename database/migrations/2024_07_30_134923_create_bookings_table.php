<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('namahewan');
            $table->integer('berat')->nullable();
            $table->string('jenishewan')->nullable();
            $table->string('alamat');
            $table->string('telpon');
            $table->date('tanggal');
            $table->date('keluar')->nullable();
            $table->text('keluhan')->nullable();
            $table->string('karyawan1')->default('belum ada karyawan yang mengerjakan');
            $table->string('karyawan2')->nullable()->default('belum ada karyawan yang mengerjakan');
            $table->string('karyawan3')->nullable()->default('belum ada karyawan yang mengerjakan');
            $table->string('vaksin')->default('belum ada')->nullable();
            $table->integer('vaksin_kuantitas')->default(0)->nullable();
            $table->integer('vaksin_harga')->default(0)->nullable();
            $table->string('grooming')->default('belum ada')->nullable();
            $table->integer('grooming_kuantitas')->default(0)->nullable();
            $table->integer('grooming_harga')->default(0)->nullable();
            $table->string('operasi')->default('belum ada')->nullable();
            $table->integer('operasi_kuantitas')->default(0)->nullable();
            $table->integer('operasi_harga')->default(0)->nullable();
            $table->string('lainnya')->default('belum ada')->nullable();
            $table->integer('lainnya_kuantitas')->default(0)->nullable();
            $table->integer('lainnya_harga')->default(0)->nullable();
            $table->string('jasa_dokter')->default('tidak ada')->nullable();
            $table->integer('jasa_dokter_kuantitas')->default(0)->nullable();
            $table->integer('jasa_dokter_harga')->default(0)->nullable();
            $table->string('diagnosa_penunjang')->default('tidak ada')->nullable();
            $table->integer('diagnosa_penunjang_kuantitas')->default(0)->nullable();
            $table->integer('diagnosa_penunjang_harga')->default(0)->nullable();
            $table->string('transportasi')->default('tidak ada')->nullable();
            $table->integer('transportasi_kuantitas')->default(0)->nullable();
            $table->integer('transport_harga')->default(0)->nullable();
            $table->integer('total_trasport')->default(0)->nullable();
            $table->integer('dp')->default(0)->nullable();
            $table->integer('total')->default(0)->nullable();
            $table->string('status')->default('belum dikerjakan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bookings');
    }
}
