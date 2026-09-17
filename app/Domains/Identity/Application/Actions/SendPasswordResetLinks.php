<?php

namespace App\Domains\Identity\Application\Actions;

use App\Domains\Identity\Application\Data\ForgotPasswordData;
use App\Domains\Identity\Application\Mail\PasswordResetLinksMail;
use App\Domains\Identity\Domain\Enums\Profil;
use App\Domains\Identity\Domain\PasswordReset\UserKeyedTokenRepository;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

final class SendPasswordResetLinks
{
    public function __construct(
        private readonly UserKeyedTokenRepository $tokens,
    ) {}

    public function __invoke(ForgotPasswordData $data): void
    {
        $accounts = User::query()
            ->where('email', $data->email)
            ->where('is_active', true)
            ->orderBy('profil')
            ->get();

        // Aucun compte : on ne fait rien, et l'appelant renvoie la même réponse que
        // dans le cas nominal. Sans cela, l'endpoint énumère les comptes.
        if ($accounts->isEmpty()) {
            return;
        }

        $links = $accounts->map(fn (User $user) => [
            'label' => Profil::from($user->profil)->label(),
            'url' => $this->linkFor($user),
        ])->all();

        // Mise en file plutôt qu'envoi synchrone : la poignée de main SMTP est lente
        // sur un endpoint public, et le projet fait déjà tourner deux `queue:work`,
        // qui réessaieront en cas de panne passagère.
        //
        // Une panne SMTP est attrapée par le filet de __invoke, comme tout le reste :
        // la réponse reste identique et l'incident part au journal.
        Mail::to($data->email)->queue(new PasswordResetLinksMail($links));
    }

    private function linkFor(User $user): string
    {
        $token = $this->tokens->createFor($user);

        return rtrim((string) config('app.front_app_url'), '/')
            .'/reset-password?token='.urlencode($token);
    }
}
