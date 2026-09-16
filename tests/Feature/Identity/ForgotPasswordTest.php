<?php

namespace Tests\Feature\Identity;

use App\Domains\Identity\Application\Mail\PasswordResetLinksMail;
use App\Domains\Identity\Domain\Enums\Profil;
use App\Domains\Identity\Domain\PasswordReset\UserKeyedTokenRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    private const EMAIL = 'double@chictuktuk.com';

    public function test_la_table_est_indexee_par_utilisateur(): void
    {
        $colonnes = Schema::getColumnListing('password_reset_tokens');

        $this->assertContains('user_id', $colonnes);
        $this->assertNotContains('email', $colonnes);
    }

    public function test_le_depot_cree_un_jeton_prefixe_par_l_identifiant_du_compte(): void
    {
        $user = User::factory()->profil(Profil::Owner)->create();
        $depot = app(UserKeyedTokenRepository::class);

        $jeton = $depot->createFor($user);

        $this->assertStringStartsWith($user->id.'.', $jeton);
        // Le jeton est haché en base : la valeur en clair ne doit pas y figurer.
        $ligne = DB::table('password_reset_tokens')->where('user_id', $user->id)->sole();
        $this->assertNotSame($jeton, $ligne->token);

        $this->assertTrue($depot->resolve($jeton)?->is($user));
    }

    public function test_le_depot_refuse_un_jeton_inconnu_mal_forme_ou_expire(): void
    {
        $user = User::factory()->profil(Profil::Owner)->create();
        $depot = app(UserKeyedTokenRepository::class);

        $this->assertNull($depot->resolve('pas-de-point'));
        $this->assertNull($depot->resolve($user->id.'.mauvais-alea'));

        $jeton = $depot->createFor($user);
        DB::table('password_reset_tokens')
            ->where('user_id', $user->id)
            ->update(['created_at' => now()->subMinutes(61)]);

        $this->assertNull($depot->resolve($jeton));
    }

    public function test_un_email_a_deux_comptes_recoit_un_seul_message_a_deux_liens(): void
    {
        Mail::fake();
        config(['app.front_app_url' => 'https://app-staging.chictuktuk.com']);

        $admin = User::factory()->profil(Profil::Admin)->create(['email' => self::EMAIL]);
        $owner = User::factory()->profil(Profil::Owner)->create(['email' => self::EMAIL]);

        $this->postJson('/api/v1/auth/password/forgot', ['email' => self::EMAIL])
            ->assertOk()
            ->assertJsonStructure(['message']);

        Mail::assertQueuedCount(1);
        Mail::assertQueued(PasswordResetLinksMail::class, function (PasswordResetLinksMail $mail) {
            return count($mail->links) === 2
                && $mail->links[0]['label'] === 'Administrateur'
                && $mail->links[1]['label'] === 'Propriétaire'
                && str_starts_with($mail->links[0]['url'], 'https://app-staging.chictuktuk.com/reset-password?token=');
        });

        // Un jeton par compte, indépendants.
        $this->assertSame(1, DB::table('password_reset_tokens')->where('user_id', $admin->id)->count());
        $this->assertSame(1, DB::table('password_reset_tokens')->where('user_id', $owner->id)->count());
    }

    public function test_un_email_inconnu_donne_la_meme_reponse_sans_envoyer_de_message(): void
    {
        Mail::fake();

        User::factory()->profil(Profil::Driver)->create(['email' => 'connu@chictuktuk.com']);

        $reponseConnue = $this->postJson('/api/v1/auth/password/forgot', ['email' => 'connu@chictuktuk.com']);
        $reponseInconnue = $this->postJson('/api/v1/auth/password/forgot', ['email' => 'personne@chictuktuk.com']);

        // Strictement identiques : sinon l'endpoint énumère les comptes.
        $this->assertSame($reponseConnue->status(), $reponseInconnue->status());
        $this->assertSame($reponseConnue->json(), $reponseInconnue->json());

        Mail::assertQueuedCount(1);
    }

    public function test_un_compte_desactive_ne_recoit_pas_de_lien(): void
    {
        Mail::fake();

        User::factory()->profil(Profil::Driver)->inactive()->create(['email' => 'desactive@chictuktuk.com']);

        $this->postJson('/api/v1/auth/password/forgot', ['email' => 'desactive@chictuktuk.com'])
            ->assertOk();

        Mail::assertNothingQueued();
    }

    public function test_une_nouvelle_demande_remplace_le_jeton_precedent(): void
    {
        Mail::fake();

        $user = User::factory()->profil(Profil::Driver)->create(['email' => 'connu@chictuktuk.com']);

        $this->postJson('/api/v1/auth/password/forgot', ['email' => 'connu@chictuktuk.com'])->assertOk();
        $premier = DB::table('password_reset_tokens')->where('user_id', $user->id)->sole()->token;

        $this->postJson('/api/v1/auth/password/forgot', ['email' => 'connu@chictuktuk.com'])->assertOk();
        $lignes = DB::table('password_reset_tokens')->where('user_id', $user->id)->get();

        $this->assertCount(1, $lignes);
        $this->assertNotSame($premier, $lignes->first()->token);
    }
}
