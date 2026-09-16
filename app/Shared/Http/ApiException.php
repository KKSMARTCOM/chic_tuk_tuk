<?php

namespace App\Shared\Http;

use RuntimeException;

/**
 * Erreur métier de l'API, porteuse de son propre code et de champs supplémentaires.
 *
 * ApiExceptionRenderer sait déjà traduire les exceptions du framework, mais son
 * enveloppe se limite à {message, code, errors}. Certaines réponses ont besoin d'un
 * champ en plus (retry_after sur un verrou, profils sur une ambiguïté) et d'un code
 * stable que le statut HTTP seul ne détermine pas : 403 peut signifier FORBIDDEN ou
 * ACCOUNT_DISABLED, et le front doit pouvoir les distinguer.
 */
final class ApiException extends RuntimeException
{
    /**
     * @param  array<string, mixed>  $extra  champs ajoutés au corps de la réponse
     * @param  array<string, string>  $headers  en-têtes HTTP à poser
     */
    public function __construct(
        public readonly int $status,
        public readonly string $errorCode,
        string $message,
        public readonly array $extra = [],
        public readonly array $headers = [],
    ) {
        parent::__construct($message);
    }
}
