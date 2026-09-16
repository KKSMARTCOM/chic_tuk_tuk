<?php

namespace App\Domains\Identity\Application\Data;

use App\Shared\Data\BaseData;

final class ResetPasswordData extends BaseData
{
    public function __construct(
        public string $token,
        public string $password,
    ) {}

    /** @return array<string, mixed> */
    public static function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /** @return array<string, string> */
    public static function messages(): array
    {
        return [
            'token.required' => 'Le jeton de réinitialisation est obligatoire.',
            'password.required' => 'Le nouveau mot de passe est obligatoire.',
            'password.min' => 'Le nouveau mot de passe doit comporter au moins 8 caractères.',
            'password.confirmed' => 'La confirmation ne correspond pas au nouveau mot de passe.',
        ];
    }
}
