<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInstagramContentsTable extends Migration
{
    public function up()
    {
        Schema::create('instagram_contents', function (Blueprint $table) {
            $table->id();
            $table->string('kode_konten', 20)->unique();
            $table->string('source_type', 50); // 'kasus_klinik','edukasi','layanan','promo','custom'
            $table->unsignedBigInteger('source_id')->nullable(); // booking_id or layanan_id, etc.
            $table->string('topic');
            $table->string('content_type', 30); // 'educational','case_story','tips','service','promotional','awareness','faq'
            $table->string('tone', 30)->default('professional'); // professional, friendly, informative
            $table->string('template_style', 30)->default('auto'); // hero, text_focus, split, case_study, educational
            $table->tinyInteger('slide_count')->default(3);
            $table->string('status', 30)->default('draft'); // draft, generated, review, approved, published, failed
            $table->text('caption')->nullable();
            $table->json('hashtags')->nullable();
            $table->json('ai_raw_response')->nullable(); // store full AI output for debugging
            $table->json('brand_snapshot')->nullable(); // logo/info snapshot at generation time
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('publish_error')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('instagram_contents');
    }
}
