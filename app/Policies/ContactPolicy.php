<?php

namespace App\Policies;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ContactPolicy
{
    /**
     * Ver el perfil de un contacto: solo su dueño.
     */
    public function view(User $user, Contact $contact): Response
    {
        return $this->owns($user, $contact);
    }

    /**
     * Actualizar un contacto: solo su dueño.
     */
    public function update(User $user, Contact $contact): Response
    {
        return $this->owns($user, $contact);
    }

    /**
     * Eliminar un contacto: solo su dueño.
     */
    public function delete(User $user, Contact $contact): Response
    {
        return $this->owns($user, $contact);
    }

    private function owns(User $user, Contact $contact): Response
    {
        return $user->id === $contact->user_id
            ? Response::allow()
            : Response::deny('No tienes permiso para acceder a este contacto.');
    }
}
