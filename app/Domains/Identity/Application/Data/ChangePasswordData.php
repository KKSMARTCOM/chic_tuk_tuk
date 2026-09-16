<?php

namespace App\Domains\Identity\Application\Data;

use App\Shared\Data\BaseData;

final class ChangePasswordData extends BaseData
{
    public function __construct(
        public string $currentPassword,
        public string $password,
    ) {}

    /** @return array<string, mixed> */
    public static function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            // `confirmed` exige un champ password_confirmation identique.
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /** @return array<string, string> */
    public static function messages(): array
    {
        return [
            'current_password.required' => 'Le mot de passe actuel est obligatoire.',
            'password.required' => 'Le nouveau mot de passe est obligatoire.',
            'password.min' => 'Le nouveau mot de passe doit comporter au moins 8 caractères.',
            'password.confirmed' => 'La confirmation ne correspond pas au nouveau mot de passe.',
        ];
    }
}
