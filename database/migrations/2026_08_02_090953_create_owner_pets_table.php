<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOwnerPetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (!Schema::hasTable('owner_pets')) {
            Schema::create('owner_pets', function (Blueprint $table) {

                $table->id();

                $table->foreignId('owner_id')
                    ->constrained('owners')
                    ->cascadeOnDelete();

                $table->string('nama');
                $table->string('jenis_hewan');
                $table->decimal('berat_hewan', 5, 2)->nullable();
                $table->string('ras_hewan')->nullable();

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
        Schema::dropIfExists('owner_pets');
    }
}
