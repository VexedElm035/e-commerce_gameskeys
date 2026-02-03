<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SupportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userId = auth()->id();
        $userRole = auth()->user()->role;

        if ($userRole === 'admin') {
            $tickets = \App\Models\Support::with(['buyer:id,username,avatar,role,created_at', 'seller:id,username,avatar,role,created_at', 'game'])->latest()->get();
        } else {
            $tickets = \App\Models\Support::with(['buyer:id,username,avatar,role,created_at', 'seller:id,username,avatar,role,created_at', 'game'])
                ->where('user_id_buyer', $userId)
                ->orWhere('user_id_seller', $userId)
                ->latest()
                ->get();
        }

        return response()->json($tickets);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id_seller' => 'required|exists:users,id',
            'issue' => 'required|string|max:255',
            'description' => 'required|string',
            'game_id' => 'nullable|exists:games,id',
        ]);

        $validated['user_id_buyer'] = auth()->id();
        $validated['state'] = 'abierto';

        $ticket = \App\Models\Support::create($validated);

        return response()->json($ticket, 201);
    }

    public function show($id)
    {
        $ticket = \App\Models\Support::with(['buyer:id,username,avatar,role,created_at', 'seller:id,username,avatar,role,created_at', 'game'])->findOrFail($id);
        
        $userId = auth()->id();
        if ($ticket->user_id_buyer !== $userId && $ticket->user_id_seller !== $userId && auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($ticket);
    }

    public function update(Request $request, $id)
    {
        $ticket = \App\Models\Support::findOrFail($id);

        if (auth()->user()->role !== 'admin' && $ticket->user_id_seller !== auth()->id()) {
            // Permitir al comprador cerrar ticket? Tal vez. Por ahora solo admin/vendedor actualiza estado
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'state' => 'required|in:abierto,cerrado,pendiente',
        ]);

        $ticket->update($validated);

        return response()->json($ticket);
    }

    public function destroy($id)
    {
        $ticket = \App\Models\Support::findOrFail($id);

        if (auth()->user()->role !== 'admin') {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        $ticket->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
