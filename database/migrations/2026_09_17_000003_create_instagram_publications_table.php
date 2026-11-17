<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInstagramPublicationsTable extends Migration
{
    public function up()
    {
        Schema::create('instagram_publications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instagram_content_id');
            $table->string('platform', 20)->default('instagram');
            $table->string('external_post_id')->nullable(); // from Meta Graph API
            $table->string('status', 30)->default('pending'); // pending, publishing, published, failed
            $table->timestamp('published_at')->nullable();
            $table->text('error_message')->nullable();
            $table->json('response_metadata')->nullable();
            $table->timestamps();

            $table->foreign('instagram_content_id')
                  ->references('id')->on('instagram_contents')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('instagram_publications');
    }
}
