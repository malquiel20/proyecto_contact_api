<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Con PUT se envían todos los campos; con PATCH solo los que cambian.
     * El nombre y la dirección se pueden repetir; el correo y el teléfono no.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $user = $this->user();

        return [
            'url_avatar' => ['sometimes', 'nullable', 'url', 'max:255'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'phone_number' => [
                'sometimes', 'nullable', 'string', 'regex:/^\+?[0-9]{7,15}$/',
                Rule::unique('users', 'phone_number')->ignore($user),
            ],
            'email' => [
                'sometimes', 'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user),
            ],
            'residence' => ['sometimes', 'nullable', 'string', 'max:255'],
            'password' => ['sometimes', 'required', 'confirmed', Password::min(8)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'url_avatar.url' => 'El avatar debe ser una URL válida.',
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre admite máximo 255 caracteres.',
            'phone_number.regex' => 'El teléfono debe tener entre 7 y 15 dígitos.',
            'phone_number.unique' => 'El teléfono ya está en uso por otro usuario.',
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'El correo no tiene un formato válido.',
            'email.unique' => 'El correo ya está en uso por otro usuario.',
            'residence.max' => 'La dirección admite máximo 255 caracteres.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ];
    }
}
