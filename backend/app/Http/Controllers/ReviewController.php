<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\Review::with(['user:id,username,avatar,role,created_at', 'game']);

        if ($request->has('game_id')) {
            $query->where('game_id', $request->game_id);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        return response()->json($query->latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'game_id' => 'required|exists:games,id',
            'seller_id' => 'required|exists:users,id',
            'rate' => 'required|integer|min:1|max:5',
            'rate_ux' => 'required|integer|min:1|max:5',
            'rate_time' => 'required|integer|min:1|max:5',
            'commentary' => 'nullable|string|max:1000',
        ]);

        $validated['user_id'] = auth()->id();

        $review = \App\Models\Review::create($validated);

        return response()->json($review, 201);
    }

    public function show($id)
    {
        return \App\Models\Review::with(['user:id,username,avatar,role,created_at', 'game'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $review = \App\Models\Review::findOrFail($id);
        
        if ($review->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'rate' => 'sometimes|integer|min:1|max:5',
            'rate_ux' => 'sometimes|integer|min:1|max:5',
            'rate_time' => 'sometimes|integer|min:1|max:5',
            'commentary' => 'nullable|string|max:1000',
        ]);

        $review->update($validated);

        return response()->json($review);
    }

    public function destroy($id)
    {
        $review = \App\Models\Review::findOrFail($id);

        if ($review->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $review->delete();

        return response()->json(['message' => 'Review deleted']);
    }
}
