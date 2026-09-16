<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS)
    |--------------------------------------------------------------------------
    |
    | Les fronts Nuxt sont sur des origines distinctes de l'API :
    |   landing : https://chictuktuk.com
    |   app     : https://app.chictuktuk.com
    |   api     : https://api.chictuktuk.com
    |
    | Ces trois origines partagent le domaine enregistrable chictuktuk.com : elles
    | sont donc "same-site", ce qui permet au cookie de refresh (SameSite=Lax,
    | HttpOnly) d'être transmis depuis le front vers l'API.
    |
    | `supports_credentials` interdit le joker '*' : les origines doivent être
    | énumérées explicitement, et diffèrent entre staging et production — d'où le
    | passage par des variables d'environnement.
    |
    | Tant que FRONT_LANDING_URL et FRONT_APP_URL ne sont pas définies, la liste est
    | vide et aucune origine tierce n'est autorisée : déployer ce fichier ne change
    | donc rien au comportement actuel.
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_values(array_filter([
        env('FRONT_LANDING_URL'),
        env('FRONT_APP_URL'),
    ])),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    // Le front lit Retry-After pour temporiser après une 429.
    'exposed_headers' => ['Retry-After'],

    // Met le preflight OPTIONS en cache (Chrome plafonne à 2 h). Sans cela, chaque
    // requête JSON authentifiée paie un aller-retour supplémentaire.
    'max_age' => 7200,

    'supports_credentials' => true,

];
