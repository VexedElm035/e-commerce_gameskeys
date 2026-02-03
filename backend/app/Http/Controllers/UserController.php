<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\GameKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        // Only admin should see all users, or public profile data only
        if (Auth::user()->role !== 'admin') {
             return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json(User::all(), 200);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response([
                'message' => 'Credenciales incorrectas',
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response([
            'message' => 'Login exitoso',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'avatar' => 'nullable|string',
            'role' => 'required|string|in:admin,user,seller', // Consider restricting 'admin' creation
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);
        return response()->json($user, 201);
    }

    public function show(string $id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        $authUser = Auth::user();

        if ($authUser->id != $id && $authUser->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'username' => ['sometimes', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'sometimes|string|min:8',
            'avatar' => 'nullable|string',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);
        return response()->json($user);
    }
    
    public function sellerStats(Request $request, $sellerId)
    {
         $seller = User::findOrFail($sellerId);
         
         // Optional: Check if the requester is the seller themselves or admin
         if (Auth::id() != $seller->id && Auth::user()->role !== 'admin') {
             return response()->json(['message' => 'Unauthorized'], 403);
         }

        $totalEarnings = GameKey::where('seller_id', $seller->id)
            ->where('state', 'vendida')
            ->sum('price');

        //8% de comisión
        $sellerEarnings = $totalEarnings * 0.8;

        return response()->json([
            'total_earnings' => (float) $sellerEarnings,
            'total_sales' => (int) GameKey::where('seller_id', $seller->id)
                ->where('state', 'vendida')
                ->count(),
            'available_keys' => (int) GameKey::where('seller_id', $seller->id)
                ->where('state', 'disponible')
                ->count(),
            'sold_keys' => (int) GameKey::where('seller_id', $seller->id)
                ->where('state', 'vendida')
                ->count()
        ]);
    }

    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        if (Auth::user()->role !== 'admin') {
             return response()->json(['message' => 'Unauthorized'], 403);
        }
        $user->delete();
        return response()->json(['message' => 'User deleted']);
    }
}
