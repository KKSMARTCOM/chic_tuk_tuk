<?php

namespace App\Shared\Data;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

/**
 * Classe de base de tous les DTO de l'API.
 *
 * Une classe Data remplit deux rôles à la fois : elle porte les règles de validation
 * en entrée (à la place d'un FormRequest) et la sérialisation en sortie (à la place
 * d'une JsonResource). C'est la convention retenue pour ce projet — ne pas réintroduire
 * de FormRequest ni de JsonResource dans le nouveau code.
 *
 * Le mapping snake_case est appliqué dans les deux sens : les propriétés PHP peuvent
 * être nommées en camelCase, le JSON échangé avec les fronts Nuxt reste en snake_case,
 * cohérent avec les colonnes de la base et avec les payloads Blade actuels.
 */
#[MapInputName(SnakeCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
abstract class BaseData extends Data
{
}
