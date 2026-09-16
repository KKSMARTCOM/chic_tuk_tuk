<?php

namespace App\Domains\Identity\Application\Data;

use App\Shared\Data\BaseData;

final class ForgotPasswordData extends BaseData
{
    public function __construct(
        public string $email,
    ) {}

    /** @return array<string, mixed> */
    public static function rules(): array
    {
        // Volontairement pas de règle `exists` : elle transformerait la validation
        // en test d'existence de compte, exactement ce que la réponse indifférenciée
        // cherche à éviter.
        return [
            'email' => ['required', 'string', 'email'],
        ];
    }

    /** @return array<string, string> */
    public static function messages(): array
    {
        return [
            'email.required' => "L'adresse email est obligatoire.",
            'email.email' => "L'adresse email est invalide.",
        ];
    }
}
