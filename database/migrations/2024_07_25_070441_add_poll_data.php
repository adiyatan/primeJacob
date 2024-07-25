<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Create poll_data table
        Schema::create('poll_data', function (Blueprint $table) {
            $table->id();
            $table->string('poll_id');
            $table->string('chat_id')->nullable();
            $table->json('options');
            $table->integer('total_voter_count');
            $table->timestamp('date')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('poll_data');
    }
};
