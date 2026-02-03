<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\Message;

class PurchaseController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
             $purchases = Purchase::with(['user', 'gameKey.game'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $purchases = Purchase::with(['user', 'gameKey.game'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return response()->json($purchases);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key_id' => 'required|exists:game_keys,id',
            'pay_method' => 'required|string',
            'total' => 'required|numeric',
            'tax' => 'required|numeric',
            'state' => 'required|string',
        ]);

        $validated['user_id'] = auth()->id();

        $purchase = Purchase::create($validated);

        // Mensajes de compra/venta
        Message::create([
            'sender_id' => 1,
            'receiver_id' => $purchase->user_id,
            'purchase_id' => $purchase->id,
            'subject' => 'Compra exitosa',
            'content' => 'Has comprado la clave para ' . $purchase->gameKey->game->name,
            'type' => 'purchase'
        ]);

        Message::create([
            'sender_id' => 1,
            'receiver_id' => $purchase->gameKey->seller_id,
            'purchase_id' => $purchase->id,
            'subject' => 'Venta realizada',
            'content' => $purchase->user->username . ' ha comprado tu clave de ' . $purchase->gameKey->game->name,
            'type' => 'sale'
        ]);

        return response()->json($purchase, 201);
    }

    public function show($id)
    {
        $purchase = Purchase::with(['gameKey.game', 'user'])->findOrFail($id);
        $user = auth()->user();

        if ((int)$user->id !== (int)$purchase->user_id && $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($purchase);
    }

    // ... (métodos edit/update/destroy sin cambios)
}