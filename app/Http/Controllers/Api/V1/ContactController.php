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
    public function index(Request $request): AnonymousResourceCollection
    {
        $contacts = $request->user()
            ->contacts()
            ->orderBy('name')
            ->paginate(15);

        return ContactResource::collection($contacts);
    }

    /**
     * Agrega un contacto a la agenda del usuario autenticado.
     */
    public function store(ContactRequest $request): JsonResponse
    {
        $contact = $request->user()->contacts()->create($request->validated());

        return (new ContactResource($contact))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Perfil del contacto.
     */
    public function show(Contact $contact): ContactResource
    {
        Gate::authorize('view', $contact);

        return new ContactResource($contact);
    }

    /**
     * Actualiza un contacto.
     */
    public function update(ContactRequest $request, Contact $contact): ContactResource
    {
        $contact->update($request->validated());

        return new ContactResource($contact);
    }

    /**
     * Elimina un contacto (borrado lógico).
     */
    public function destroy(Contact $contact): JsonResponse
    {
        Gate::authorize('delete', $contact);

        $contact->delete();

        return response()->json([
            'message' => 'Contacto eliminado correctamente.',
        ]);
    }
}
