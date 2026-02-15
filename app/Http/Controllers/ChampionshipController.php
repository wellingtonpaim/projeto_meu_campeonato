<?php

namespace App\Http\Controllers;

use App\Services\ChampionshipService;
use App\Services\SimulationService;
use Illuminate\Http\Request;

class ChampionshipController
{
    protected $championshipService;

    public function __construct(ChampionshipService $championshipService)
    {
        $this->championshipService = $championshipService;
    }

    public function store(Request $request)
    {
        $championship = $this->championshipService->createChampionship($request->all());
        return response()->json($championship, 201);
    }

    public function enroll(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255']);

        try {
            $team = $this->championshipService->enrollTeam($id, $request->name);
            return response()->json([
                'message' => 'Time inscrito com sucesso',
                'team' => $team
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function simulate($id, SimulationService $simulationService)
    {
        try {
            $championship = $simulationService->simulate($id);

            return response()->json([
                'message' => 'Campeonato simulado com sucesso!',
                'championship' => $championship
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function index()
    {
        $championships = \App\Models\Championship::with('winner')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($championships, 200);
    }
}
