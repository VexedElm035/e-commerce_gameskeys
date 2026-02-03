<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\Request;
use App\Http\Requests\GameRequest;

class GameController extends Controller
{
    public function index()
    {
        return response()->json(Game::all(), 200);
    }

    public function store(GameRequest $request)
    {
        $game = Game::create($request->validated());
        return response()->json(['message' => 'Juego creado exitosamente', 'data' => $game], 200);
    }

    public function show($id)
    {
        $game = Game::find($id);
        if (!$game) {
            return response()->json(['message' => 'Game not found'], 404);
        }
        return response()->json($game, 200);
    }

    public function update(GameRequest $request, Game $game)
    {
        if (auth()->user()->role !== 'admin') {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        $game->update($request->validated());
        return response()->json(['message' => 'Juego actualizado correctamente', 'data' => $game], 200);
    }

    public function destroy(Game $game)
    {
        if (auth()->user()->role !== 'admin') {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        $game->delete();
        return response()->json(['message' => 'Juego eliminado correctamente'], 200);
    }
}
