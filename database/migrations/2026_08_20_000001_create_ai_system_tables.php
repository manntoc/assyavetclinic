<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAiSystemTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('title')->default('Percakapan Baru');
            $table->string('status')->default('active'); // active, closed, archived
            $table->string('state')->default('IDLE'); // IDLE, UNDERSTANDING, COLLECTING_DATA, VALIDATING, WAITING_CONFIRMATION, EXECUTING, COMPLETED, FAILED
            $table->json('context_data')->nullable(); // Multi-turn memory entities
            $table->timestamps();
        });

        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id')->index();
            $table->string('role'); // user, assistant, system, tool
            $table->text('content')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('conversation_id')->references('id')->on('ai_conversations')->onDelete('cascade');
        });

        Schema::create('ai_tool_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('tool_name');
            $table->json('parameters')->nullable();
            $table->json('result')->nullable();
            $table->string('status'); // success, failed, denied, pending_confirmation
            $table->float('execution_time')->default(0); // in milliseconds
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
        Schema::dropIfExists('ai_tool_logs');
        Schema::dropIfExists('ai_messages');
        Schema::dropIfExists('ai_conversations');
    }
}
