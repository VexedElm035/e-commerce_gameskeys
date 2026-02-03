<?php

namespace App\Http\Controllers;

use App\Models\GameKey;
use Illuminate\Http\Request;
use App\Http\Requests\GameKeyRequest;
use Illuminate\Support\Facades\Auth;

class GameKeyController extends Controller
{
    public function index(Request $request)
    {
        $query = GameKey::with(['seller:id,username,avatar,role,created_at', 'game']);

        if ($request->has('seller_id')) {
            $query->where('seller_id', $request->seller_id);
        }

        if ($request->has('state')) {
            $query->where('state', $request->state);
        }
        
        // Default to latest
        $query->latest();

        return response()->json($query->get());
    }

    public function store(GameKeyRequest $request)
    {
        $validated = $request->validated();
        
        // Force seller_id to be the authenticated user unless admin (optional, for now enforce auth user)
        $validated['seller_id'] = Auth::id();

        $gameKey = GameKey::create($validated);
        return response()->json($gameKey, 201);
    }

    public function show($id)
    {
        return GameKey::with(['seller:id,username,avatar,role,created_at', 'game'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $gameKey = GameKey::findOrFail($id);

        if (Auth::id() !== $gameKey->seller_id && Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Use standard validation for updates, allowing partial updates
        $validated = $request->validate([
            'game_id' => 'sometimes|exists:games,id',
            'state' => 'sometimes|string|in:disponible,vendida,reservada,inactiva',
            'region' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'tax' => 'sometimes|numeric|min:0',
            'delivery_time' => 'sometimes|string',
            'platform' => 'sometimes|string',
            'sale_id' => 'nullable|exists:sales,id',
            'key' => 'sometimes|string'
        ]);

        $gameKey->update($validated);
        return response()->json($gameKey);
    }

    public function destroy($id)
    {
        $gameKey = GameKey::findOrFail($id);

        if (Auth::id() !== $gameKey->seller_id && Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $gameKey->delete();
        return response()->json(['message' => 'GameKey deleted successfully']);
    }
}
