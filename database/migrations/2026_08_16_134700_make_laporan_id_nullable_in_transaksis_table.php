<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class MakeLaporanIdNullableInTransaksisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('transaksis') && Schema::hasColumn('transaksis', 'laporan_id')) {
            DB::statement("ALTER TABLE `transaksis` MODIFY `laporan_id` BIGINT UNSIGNED NULL DEFAULT NULL;");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('transaksis') && Schema::hasColumn('transaksis', 'laporan_id')) {
            DB::statement("ALTER TABLE `transaksis` MODIFY `laporan_id` BIGINT UNSIGNED NOT NULL;");
        }
    }
}
