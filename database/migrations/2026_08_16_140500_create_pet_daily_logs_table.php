<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePetDailyLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('pet_daily_logs')) {
            Schema::create('pet_daily_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('booking_id')->nullable()->constrained('bookings')->onDelete('cascade');
                $table->foreignId('owner_pet_id')->nullable()->constrained('owner_pets')->onDelete('cascade');
                $table->dateTime('tanggal_log');
                $table->string('suhu_tubuh')->nullable();
                $table->string('denyut_jantung')->nullable();
                $table->decimal('berat_badan', 5, 2)->nullable();
                $table->string('crt')->nullable();
                $table->string('nafsu_makan')->nullable();
                $table->text('catatan_observasi')->nullable();
                $table->string('petugas')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pet_daily_logs');
    }
}
