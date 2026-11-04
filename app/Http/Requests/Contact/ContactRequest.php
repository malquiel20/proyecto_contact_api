<?php

namespace App\Http\Requests\Contact;

use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class ContactRequest extends FormRequest
{
    /**
     * Al editar, solo el dueño del contacto pasa (antes de validar los datos).
     */
    public function authorize(): bool|Response
    {
        $contact = $this->route('contact');

        return $contact ? Gate::inspect('update', $contact) : true;
    }

    /**
     * Al crear (POST) el nombre es obligatorio y se pide un teléfono o un correo.
     * Al actualizar (PUT/PATCH) solo se validan los campos enviados.
     * El nombre y la dirección se pueden repetir; el correo y el teléfono no,
     * dentro de los contactos del mismo usuario.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $creating = $this->isMethod('post');
        $presence = $creating ? [] : ['sometimes'];

        return [
            'name' => [...$presence, 'required', 'string', 'max:255'],
            'number_phone' => [
                ...$presence,
                ...($creating ? ['required_without:email'] : []),
                'nullable', 'string', 'regex:/^\+?[0-9]{7,15}$/',
                $this->uniqueForUser('number_phone'),
            ],
            'email' => [
                ...$presence, 'nullable', 'email', 'max:255',
                $this->uniqueForUser('email'),
            ],
            'residence' => [...$presence, 'nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre admite máximo 255 caracteres.',
            'number_phone.required_without' => 'Debes indicar un teléfono o un correo.',
            'number_phone.regex' => 'El teléfono debe tener entre 7 y 15 dígitos.',
            'number_phone.unique' => 'Ya tienes un contacto con este teléfono.',
            'email.email' => 'El correo no tiene un formato válido.',
            'email.unique' => 'Ya tienes un contacto con este correo.',
            'residence.max' => 'La dirección admite máximo 255 caracteres.',
        ];
    }

    /**
     * Único entre los contactos activos del usuario, ignorando el que se edita.
     */
    private function uniqueForUser(string $column): Unique
    {
        return Rule::unique('contacts', $column)
            ->where('user_id', $this->user()->id)
            ->withoutTrashed()
            ->ignore($this->route('contact'));
    }
}
