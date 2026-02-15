<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Championship;
use App\Models\Team;

class ChampionshipEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_blocks_enrollment_when_championship_has_eight_teams()
    {
        $championship = Championship::create([
            'name' => 'Copa de Testes',
            'status' => 'pending'
        ]);

        for ($i = 1; $i <= 8; $i++) {
            $team = Team::create(['name' => "Time $i"]);
            $championship->teams()->attach($team->id);
        }

        $response = $this->postJson("/api/championships/{$championship->id}/enroll", [
            'name' => 'Time 9'
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error', 'O campeonato já atingiu o limite de 8 times inscritos.');
    }

    public function test_it_allows_enrollment_if_championship_is_not_full()
    {
        $championship = Championship::create([
            'name' => 'Copa Vazia',
            'status' => 'pending'
        ]);

        $response = $this->postJson("/api/championships/{$championship->id}/enroll", [
            'name' => 'Primeiro Time'
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseCount('championship_team', 1);
    }
}
