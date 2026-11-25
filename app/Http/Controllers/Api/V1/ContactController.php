<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\ContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class ContactController extends Controller
{
    /**
     * Lista los contactos del usuario autenticado.
     */
    public function index(Request $solicitud): AnonymousResourceCollection
    {
        $contactos = $solicitud->user()
            ->contactos()
            ->orderBy('name')
            ->paginate(15);

        return ContactResource::collection($contactos);
    }

    /**
     * Agrega un contacto a la agenda del usuario autenticado.
     */
    public function store(ContactRequest $solicitud): JsonResponse
    {
        $contacto = $solicitud->user()->contactos()->create($solicitud->validated());

        return (new ContactResource($contacto))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Perfil del contacto.
     */
    public function show(Contact $contacto): ContactResource
    {
        Gate::authorize('ver', $contacto);

        return new ContactResource($contacto);
    }

    /**
     * Actualiza un contacto.
     */
    public function update(ContactRequest $solicitud, Contact $contacto): ContactResource
    {
        $contacto->update($solicitud->validated());

        return new ContactResource($contacto);
    }

    /**
     * Elimina un contacto (borrado lógico).
     */
    public function destroy(Contact $contacto): JsonResponse
    {
        Gate::authorize('eliminar', $contacto);

        $contacto->delete();

        return response()->json([
            'message' => 'Contacto eliminado correctamente.',
        ]);
    }
}
