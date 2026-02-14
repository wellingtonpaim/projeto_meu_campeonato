<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('championship_id')->constrained()->cascadeOnDelete();
            $table->string('phase');

            $table->foreignId('team_a_id')->constrained('teams');
            $table->foreignId('team_b_id')->constrained('teams');

            // Placar principal
            $table->integer('team_a_goals')->nullable();
            $table->integer('team_b_goals')->nullable();

            // Critérios de desempate (Fair Play e Pênaltis)
            $table->integer('team_a_yellow_cards')->nullable();
            $table->integer('team_b_yellow_cards')->nullable();
            $table->integer('team_a_penalties')->nullable();
            $table->integer('team_b_penalties')->nullable();

            $table->foreignId('winner_id')->nullable()->constrained('teams');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
