<?php

namespace App\Services;

use App\Models\Championship;
use App\Models\Team;
use Exception;

class ChampionshipService
{
    public function createChampionship(array $data)
    {
        return Championship::create([
            'name' => $data['name'] ?? 'Meu Campeonato ' . date('Y'),
            'status' => 'pending'
        ]);
    }

    public function enrollTeam(int $championshipId, string $teamName)
    {
        $championship = Championship::findOrFail($championshipId);

        if ($championship->status !== 'pending') {
            throw new Exception("Este campeonato já foi iniciado ou finalizado.");
        }

        if ($championship->teams()->count() >= 8) {
            throw new Exception("O campeonato já atingiu o limite de 8 times inscritos.");
        }

        $team = Team::firstOrCreate(['name' => $teamName]);

        if ($championship->teams()->where('team_id', $team->id)->exists()) {
            throw new Exception("Este time já está inscrito neste campeonato.");
        }

        $championship->teams()->attach($team->id);

        return $team;
    }
}
