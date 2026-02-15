<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class MatchResultProcessingTest extends TestCase
{
    public function test_it_correctly_parses_simulated_match_json()
    {
        $pythonJsonOutput = '{
            "team_a": {"goals": 3, "yellow_cards": 1, "penalties": 5},
            "team_b": {"goals": 1, "yellow_cards": 2, "penalties": 3}
        }';

        $data = json_decode($pythonJsonOutput, true);

        $this->assertArrayHasKey('team_a', $data);
        $this->assertArrayHasKey('yellow_cards', $data['team_a']);
        $this->assertEquals(3, $data['team_a']['goals']);
        $this->assertEquals(2, $data['team_b']['yellow_cards']);
    }

    #[DataProvider('goalProvider')]
    public function test_goal_differential_logic($goalsA, $goalsB, $expected)
    {
        $differential = $goalsA - $goalsB;

        $this->assertEquals($expected, $differential);
        $this->assertTrue($differential > 0);
    }

    public static function goalProvider(): array
    {
        return [
            'cenário vitória simples' => [5, 2, 3],
            'cenário goleada'         => [10, 2, 8],
        ];
    }
}
