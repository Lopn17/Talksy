<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memory_games', function (Blueprint $table) {
            $table->id();
            $table->string('difficulty')->default('easy');
            $table->json('cards');           // [{id, emoji, flipped, matched}]
            $table->json('flipped_ids')->nullable(); // currently flipped card ids (max 2)
            $table->unsignedBigInteger('current_player_id')->nullable(); // whose turn
            $table->string('status')->default('waiting'); // waiting | playing | finished
            $table->timestamps();
        });

        Schema::create('memory_players', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('game_id');
            $table->string('name');
            $table->integer('score')->default(0);
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->foreign('game_id')->references('id')->on('memory_games')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memory_players');
        Schema::dropIfExists('memory_games');
    }
};