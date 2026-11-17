<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddServiceActionColumnsToBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'vaksin')) {
                $table->string('vaksin')->default('belum ada')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'vaksin_kuantitas')) {
                $table->integer('vaksin_kuantitas')->default(0)->nullable();
            }
            if (!Schema::hasColumn('bookings', 'vaksin_harga')) {
                $table->integer('vaksin_harga')->default(0)->nullable();
            }

            if (!Schema::hasColumn('bookings', 'grooming')) {
                $table->string('grooming')->default('belum ada')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'grooming_kuantitas')) {
                $table->integer('grooming_kuantitas')->default(0)->nullable();
            }
            if (!Schema::hasColumn('bookings', 'grooming_harga')) {
                $table->integer('grooming_harga')->default(0)->nullable();
            }

            if (!Schema::hasColumn('bookings', 'operasi')) {
                $table->string('operasi')->default('belum ada')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'operasi_kuantitas')) {
                $table->integer('operasi_kuantitas')->default(0)->nullable();
            }
            if (!Schema::hasColumn('bookings', 'operasi_harga')) {
                $table->integer('operasi_harga')->default(0)->nullable();
            }

            if (!Schema::hasColumn('bookings', 'lainnya')) {
                $table->string('lainnya')->default('belum ada')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'lainnya_kuantitas')) {
                $table->integer('lainnya_kuantitas')->default(0)->nullable();
            }
            if (!Schema::hasColumn('bookings', 'lainnya_harga')) {
                $table->integer('lainnya_harga')->default(0)->nullable();
            }

            if (!Schema::hasColumn('bookings', 'jasa_dokter')) {
                $table->string('jasa_dokter')->default('tidak ada')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'jasa_dokter_kuantitas')) {
                $table->integer('jasa_dokter_kuantitas')->default(0)->nullable();
            }
            if (!Schema::hasColumn('bookings', 'jasa_dokter_harga')) {
                $table->integer('jasa_dokter_harga')->default(0)->nullable();
            }

            if (!Schema::hasColumn('bookings', 'diagnosa_penunjang')) {
                $table->string('diagnosa_penunjang')->default('tidak ada')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'diagnosa_penunjang_kuantitas')) {
                $table->integer('diagnosa_penunjang_kuantitas')->default(0)->nullable();
            }
            if (!Schema::hasColumn('bookings', 'diagnosa_penunjang_harga')) {
                $table->integer('diagnosa_penunjang_harga')->default(0)->nullable();
            }

            if (!Schema::hasColumn('bookings', 'transportasi')) {
                $table->string('transportasi')->default('tidak ada')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'transportasi_kuantitas')) {
                $table->integer('transportasi_kuantitas')->default(0)->nullable();
            }
            if (!Schema::hasColumn('bookings', 'transport_harga')) {
                $table->integer('transport_harga')->default(0)->nullable();
            }
            if (!Schema::hasColumn('bookings', 'total_trasport')) {
                $table->integer('total_trasport')->default(0)->nullable();
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
                'vaksin', 'vaksin_kuantitas', 'vaksin_harga',
                'grooming', 'grooming_kuantitas', 'grooming_harga',
                'operasi', 'operasi_kuantitas', 'operasi_harga',
                'lainnya', 'lainnya_kuantitas', 'lainnya_harga',
                'jasa_dokter', 'jasa_dokter_kuantitas', 'jasa_dokter_harga',
                'diagnosa_penunjang', 'diagnosa_penunjang_kuantitas', 'diagnosa_penunjang_harga',
                'transportasi', 'transportasi_kuantitas', 'transport_harga', 'total_trasport'
            ]);
        });
    }
}
