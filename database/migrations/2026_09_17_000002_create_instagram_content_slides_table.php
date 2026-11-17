<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInstagramContentSlidesTable extends Migration
{
    public function up()
    {
        Schema::create('instagram_content_slides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instagram_content_id');
            $table->tinyInteger('slide_number');
            $table->string('purpose', 30); // hook, problem, solution, tips, cta
            $table->string('layout', 30)->default('hero'); // hero, text_focus, split, case_study, educational
            $table->string('headline')->nullable();
            $table->text('body')->nullable();
            $table->string('image_path')->nullable();    // uploaded or selected image
            $table->string('rendered_path')->nullable(); // server-rendered PNG path
            $table->json('design_data')->nullable();     // layout overrides, colors, etc.
            $table->timestamps();

            $table->foreign('instagram_content_id')
                  ->references('id')->on('instagram_contents')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('instagram_content_slides');
    }
}
