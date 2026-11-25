<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Perfil del usuario autenticado.
     */
    public function mostrar(Request $solicitud): UserResource
    {
        return new UserResource($solicitud->user());
    }

    /**
     * Actualiza el perfil del usuario autenticado.
     */
    public function actualizar(UpdateProfileRequest $solicitud): UserResource
    {
        $usuario = $solicitud->user();

        $usuario->update($solicitud->validated());

        return new UserResource($usuario);
    }
}
