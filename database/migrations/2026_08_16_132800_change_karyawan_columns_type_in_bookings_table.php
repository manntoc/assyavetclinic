<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeKaryawanColumnsTypeInBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Drop foreign keys on karyawan columns if exist
        $foreignKeys = ['bookings_karyawan1_foreign', 'bookings_karyawan2_foreign', 'bookings_karyawan3_foreign'];
        foreach ($foreignKeys as $fk) {
            try {
                DB::statement("ALTER TABLE `bookings` DROP FOREIGN KEY `{$fk}`");
            } catch (\Exception $e) {
                // Ignore if foreign key does not exist
            }
        }

        // Alter columns to VARCHAR(191)
        DB::statement("ALTER TABLE `bookings` MODIFY `karyawan1` VARCHAR(191) NULL DEFAULT 'Belum Ditentukan'");
        DB::statement("ALTER TABLE `bookings` MODIFY `karyawan2` VARCHAR(191) NULL DEFAULT 'Belum Ditentukan'");
        DB::statement("ALTER TABLE `bookings` MODIFY `karyawan3` VARCHAR(191) NULL DEFAULT 'Belum Ditentukan'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `bookings` MODIFY `karyawan1` BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE `bookings` MODIFY `karyawan2` BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE `bookings` MODIFY `karyawan3` BIGINT UNSIGNED NULL");
    }
}
