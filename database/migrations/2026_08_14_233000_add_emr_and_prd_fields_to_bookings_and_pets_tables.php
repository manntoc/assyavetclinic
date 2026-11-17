<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmrAndPrdFieldsToBookingsAndPetsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'tipe_kedatangan')) {
                $table->string('tipe_kedatangan')->default('Walk-in')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'jam')) {
                $table->time('jam')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'suhu_tubuh')) {
                $table->decimal('suhu_tubuh', 4, 1)->nullable();
            }
            if (!Schema::hasColumn('bookings', 'denyut_jantung')) {
                $table->integer('denyut_jantung')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'crt')) {
                $table->string('crt')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'subjective')) {
                $table->text('subjective')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'objective')) {
                $table->text('objective')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'assessment')) {
                $table->text('assessment')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'plan')) {
                $table->text('plan')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'foto_klinis')) {
                $table->string('foto_klinis')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'tanggal_kontrol')) {
                $table->date('tanggal_kontrol')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'diskon')) {
                $table->integer('diskon')->default(0)->nullable();
            }
            if (!Schema::hasColumn('bookings', 'pajak')) {
                $table->integer('pajak')->default(0)->nullable();
            }
            if (!Schema::hasColumn('bookings', 'metode_pembayaran')) {
                $table->string('metode_pembayaran')->default('Tunai')->nullable();
            }
        });

        Schema::table('owner_pets', function (Blueprint $table) {
            if (!Schema::hasColumn('owner_pets', 'spesies')) {
                $table->string('spesies')->nullable();
            }
            if (!Schema::hasColumn('owner_pets', 'jenis_kelamin')) {
                $table->string('jenis_kelamin')->nullable();
            }
            if (!Schema::hasColumn('owner_pets', 'tanggal_lahir')) {
                $table->date('tanggal_lahir')->nullable();
            }
            if (!Schema::hasColumn('owner_pets', 'umur')) {
                $table->string('umur')->nullable();
            }
            if (!Schema::hasColumn('owner_pets', 'warna')) {
                $table->string('warna')->nullable();
            }
            if (!Schema::hasColumn('owner_pets', 'foto_hewan')) {
                $table->string('foto_hewan')->nullable();
            }
        });

        Schema::table('owners', function (Blueprint $table) {
            if (!Schema::hasColumn('owners', 'catatan_khusus')) {
                $table->text('catatan_khusus')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'tipe_kedatangan', 'suhu_tubuh', 'denyut_jantung', 'crt',
                'subjective', 'objective', 'assessment', 'plan',
                'foto_klinis', 'tanggal_kontrol', 'diskon', 'pajak', 'metode_pembayaran'
            ]);
        });

        Schema::table('owner_pets', function (Blueprint $table) {
            $table->dropColumn([
                'spesies', 'jenis_kelamin', 'tanggal_lahir', 'umur', 'warna', 'foto_hewan'
            ]);
        });

        Schema::table('owners', function (Blueprint $table) {
            $table->dropColumn(['catatan_khusus']);
        });
    }
}
