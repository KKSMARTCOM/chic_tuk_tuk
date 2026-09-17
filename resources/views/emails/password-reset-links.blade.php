<p>Bonjour,</p>

@if (count($links) > 1)
    <p>
        Plusieurs comptes ChicTukTuk utilisent cette adresse. Choisissez celui dont vous
        souhaitez réinitialiser le mot de passe.
    </p>
@else
    <p>Vous avez demandé la réinitialisation de votre mot de passe.</p>
@endif

<ul>
    @foreach ($links as $link)
        <li>
            <a href="{{ $link['url'] }}">Réinitialiser mon accès {{ $link['label'] }}</a>
        </li>
    @endforeach
</ul>

<p>
    Ce lien est valable {{ config('identity.password_reset.expire_minutes') }} minutes.
    Si vous n'êtes pas à l'origine de cette demande, ignorez ce message : votre mot de
    passe reste inchangé.
</p>
