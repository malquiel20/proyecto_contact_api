<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Inicia sesión y entrega un token para la app.
     *
     * @unauthenticated
     */
    public function iniciarSesion(LoginRequest $solicitud): JsonResponse
    {
        $usuario = User::where('email', $solicitud->validated('email'))->first();

        if (! $usuario || ! Hash::check($solicitud->validated('password'), $usuario->password)) {
            throw ValidationException::withMessages([
                'email' => 'Las credenciales no son correctas.',
            ]);
        }

        $token = $usuario->createToken($solicitud->validated('device_name'))->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($usuario),
        ]);
    }

    /**
     * Cierra la sesión y revoca el token actual.
     */
    public function cerrarSesion(Request $solicitud): Response
    {
        $solicitud->user()->currentAccessToken()->delete();

        return response()->noContent();
    }
}
