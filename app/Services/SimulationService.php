<?php

namespace App\Services;

use App\Models\Championship;
use App\Models\Game;
use App\Models\Team;
use Exception;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class SimulationService
{
    public function simulate(int $championshipId)
    {
        $championship = Championship::with('teams')->findOrFail($championshipId);

        if ($championship->status !== 'pending') {
            throw new Exception("Este campeonato já foi simulado.");
        }

        if ($championship->teams->count() !== 8) {
            throw new Exception("O campeonato precisa ter exatamente 8 times inscritos para ser simulado.");
        }

        $championship->update(['status' => 'in_progress']);

        $teams = $championship->teams()->orderBy('championship_team.created_at')->get();

        $quarterWinners = [];
        for ($i = 0; $i < 8; $i += 2) {
            $quarterWinners[] = $this->playMatch($championship, 'quarter_final', $teams[$i], $teams[$i+1]);
        }

        $semiWinners = [];
        $semiLosers = [];
        for ($i = 0; $i < 4; $i += 2) {
            $winner = $this->playMatch($championship, 'semi_final', $quarterWinners[$i], $quarterWinners[$i+1]);
            $semiWinners[] = $winner;

            $semiLosers[] = ($winner->id === $quarterWinners[$i]->id) ? $quarterWinners[$i+1] : $quarterWinners[$i];
        }

        $this->playMatch($championship, 'third_place', $semiLosers[0], $semiLosers[1]);

        $champion = $this->playMatch($championship, 'final', $semiWinners[0], $semiWinners[1]);

        $championship->update([
            'status' => 'finished',
            'winner_id' => $champion->id
        ]);

        return $championship->load(['games.teamA', 'games.teamB', 'winner']);
    }

    private function playMatch(Championship $championship, string $phase, Team $teamA, Team $teamB): Team
    {
        $process = new Process(['python3', base_path('teste.py')]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new Exception("Erro no script de simulação: " . $process->getErrorOutput());
        }

        $result = json_decode($process->getOutput(), true);
        $statsA = $result['team_a'];
        $statsB = $result['team_b'];

        $scoreA = $this->getAccumulatedScore($championship->id, $teamA->id);
        $scoreB = $this->getAccumulatedScore($championship->id, $teamB->id);

        $winner = $this->determineWinner($teamA, $teamB, $statsA, $statsB, $scoreA, $scoreB, $championship->id);

        Game::create([
            'championship_id' => $championship->id,
            'phase' => $phase,
            'team_a_id' => $teamA->id,
            'team_b_id' => $teamB->id,
            'team_a_goals' => $statsA['goals'],
            'team_b_goals' => $statsB['goals'],
            'team_a_yellow_cards' => $statsA['yellow_cards'],
            'team_b_yellow_cards' => $statsB['yellow_cards'],
            'team_a_penalties' => $statsA['penalties'],
            'team_b_penalties' => $statsB['penalties'],
            'winner_id' => $winner->id,
        ]);

        return $winner;
    }

    private function getAccumulatedScore($championshipId, $teamId)
    {
        $gamesAsA = Game::where('championship_id', $championshipId)->where('team_a_id', $teamId)->get();
        $gamesAsB = Game::where('championship_id', $championshipId)->where('team_b_id', $teamId)->get();

        $goalsScored = $gamesAsA->sum('team_a_goals') + $gamesAsB->sum('team_b_goals');
        $goalsConceded = $gamesAsA->sum('team_b_goals') + $gamesAsB->sum('team_a_goals');

        return $goalsScored - $goalsConceded;
    }

    private function determineWinner($teamA, $teamB, $statsA, $statsB, $scoreA, $scoreB, $championshipId)
    {
        $tieBreakers = [
            $statsA['goals'] <=> $statsB['goals'],
            $scoreA <=> $scoreB,
            $statsB['yellow_cards'] <=> $statsA['yellow_cards'],
            $statsA['penalties'] <=> $statsB['penalties']
        ];

        foreach ($tieBreakers as $result) {
            if ($result === 1) return $teamA;
            if ($result === -1) return $teamB;
        }

        $enrollmentA = DB::table('championship_team')
            ->where('championship_id', $championshipId)->where('team_id', $teamA->id)->value('created_at');

        $enrollmentB = DB::table('championship_team')
            ->where('championship_id', $championshipId)->where('team_id', $teamB->id)->value('created_at');

        return $enrollmentA < $enrollmentB ? $teamA : $teamB;
    }
}
