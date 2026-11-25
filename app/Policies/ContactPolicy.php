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
    public function ver(User $usuario, Contact $contacto): Response
    {
        return $this->esDueno($usuario, $contacto);
    }

    /**
     * Actualizar un contacto: solo su dueño.
     */
    public function actualizar(User $usuario, Contact $contacto): Response
    {
        return $this->esDueno($usuario, $contacto);
    }

    /**
     * Eliminar un contacto: solo su dueño.
     */
    public function eliminar(User $usuario, Contact $contacto): Response
    {
        return $this->esDueno($usuario, $contacto);
    }

    private function esDueno(User $usuario, Contact $contacto): Response
    {
        return $usuario->id === $contacto->user_id
            ? Response::allow()
            : Response::deny('No tienes permiso para acceder a este contacto.');
    }
}
