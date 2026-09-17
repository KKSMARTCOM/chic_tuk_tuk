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
 * découpe sur le premier point est donc sûre.
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
