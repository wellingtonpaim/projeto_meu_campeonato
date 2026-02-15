<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Services\SimulationService;
use ReflectionMethod;

class SimulationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_determine_winner_uses_last_resort_tiebreaker()
    {
        $teamA = (object)['id' => 1];
        $teamB = (object)['id' => 2];

        $statsA = ['goals' => 2, 'yellow_cards' => 1, 'penalties' => 3];
        $statsB = ['goals' => 2, 'yellow_cards' => 1, 'penalties' => 3];
        $scoreA = 10;
        $scoreB = 10;

        $service = new SimulationService();

        $method = new ReflectionMethod(SimulationService::class, 'determineWinner');

        $winner = $method->invoke($service, $teamA, $teamB, $statsA, $statsB, $scoreA, $scoreB, 1);

        $this->assertNotNull($winner);
    }
}
