<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DevAuthController extends Controller
{
    public function login(Request $request)
    {
        if (!app()->environment('local')) {
            abort(404);
        }

        $datos = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $datos['email'])->first();

        if (!$user || !Hash::check($datos['password'], $user->password)) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Credenciales incorrectas.',
            ], 401);
        }

        $token = $user->createToken(
            'dev-token'
        )->plainTextToken;

        return response()->json([
            'ok' => true,
            'mensaje' => 'Login de desarrollo correcto.',
            'token' => $token,
            'user' => $user->load('roles'),
        ]);
    }
}