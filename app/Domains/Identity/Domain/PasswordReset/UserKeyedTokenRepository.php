<?php

namespace App\Domains\Identity\Domain\PasswordReset;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Jetons de réinitialisation indexés par compte.
 *
 * Le dépôt de Laravel est indexé par email et ne peut donc pas distinguer deux
 * comptes partageant une adresse, ce que l'unicité (email, profil) autorise.
 *
 * Forme du jeton remis à l'utilisateur : `{user_id}.{aléa}`. Les jetons sont hachés
 * en base ; sans ce préfixe, retrouver le compte à partir du seul jeton imposerait de
 * parcourir toutes les lignes en comparant les hachages. Le front n'a rien à en
 * savoir : il transmet une chaîne opaque. Un uuid ne contient pas de point, la
 * découpe sur le premier point est donc sûre pour un jeton que NOUS avons émis.
 *
 * ⚠️ Un jeton REÇU, lui, peut être n'importe quoi — tronqué par un client de
 * messagerie, recopié à la main, fabriqué. `resolve()` valide donc la forme du
 * préfixe avant toute requête : `user_id` est une colonne uuid de PostgreSQL, et lui
 * comparer une valeur qui n'est pas un uuid fait échouer la conversion côté base.
 */
final class UserKeyedTokenRepository
{
    private const TABLE = 'password_reset_tokens';

    /** Crée un jeton pour ce compte, en remplaçant toute demande en cours. */
    public function createFor(User $user): string
    {
        $this->consume($user);

        $plain = Str::random(48);

        DB::table(self::TABLE)->insert([
            'user_id' => $user->id,
            'token' => Hash::make($plain),
            'created_at' => now(),
        ]);

        return $user->id.'.'.$plain;
    }

    /** Le compte visé, ou null si le jeton est mal formé, inconnu ou expiré. */
    public function resolve(string $composite): ?User
    {
        $separator = strpos($composite, '.');

        if ($separator === false) {
            return null;
        }

        $userId = substr($composite, 0, $separator);
        $plain = substr($composite, $separator + 1);

        // Avant toute requête : `user_id` est une colonne uuid, et lui comparer
        // « abc » fait lever PostgreSQL (SQLSTATE 22P02), ce qui ressortait en 500 là
        // où un 422 est attendu. Un lien de réinitialisation tronqué suffisait à le
        // déclencher. Un aléa vide est écarté au même endroit : il ne peut
        // correspondre à aucun jeton émis, Str::random(48) n'étant jamais vide.
        if (! Str::isUuid($userId) || $plain === '') {
            return null;
        }

        $row = DB::table(self::TABLE)->where('user_id', $userId)->first();

        if ($row === null || ! Hash::check($plain, $row->token)) {
            return null;
        }

        $expiresAfter = (int) config('identity.password_reset.expire_minutes');

        // ⚠️ Ne pas écrire `now()->diffInMinutes($row->created_at) > $expiresAfter` :
        // Carbon 3 renvoie des différences SIGNÉES, donc -61 pour une date passée, et
        // la comparaison serait toujours fausse — les jetons n'expireraient jamais.
        // `created_at` sort de DB::table en chaîne, d'où le Carbon::parse.
        if (Carbon::parse($row->created_at)->addMinutes($expiresAfter)->isPast()) {
            return null;
        }

        return User::query()->find($userId);
    }

    public function consume(User $user): void
    {
        DB::table(self::TABLE)->where('user_id', $user->id)->delete();
    }
}
