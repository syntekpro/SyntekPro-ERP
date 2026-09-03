<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Product Identity
    |--------------------------------------------------------------------------
    |
    | These values identify the SyntekPro ERP product. They are used in the
    | user interface, generated documents, and external metadata such as
    | Docker image labels.
    |
    */

    'name' => env('SYNTEK_PRODUCT_NAME', 'SyntekPro ERP'),

    'short_name' => env('SYNTEK_PRODUCT_SHORT_NAME', 'SyntekPro'),

    'company' => env('SYNTEK_COMPANY_NAME', 'SyntekPro'),

    'website' => env('SYNTEK_WEBSITE', 'https://syntekpro.com'),

    'support_email' => env('SYNTEK_SUPPORT_EMAIL', 'support@syntekpro.com'),

    /*
    |--------------------------------------------------------------------------
    | Product Version
    |--------------------------------------------------------------------------
    |
    | This is the authoritative ERP application version. It is displayed in
    | the administration UI and reported by the API. Update this value when
    | a new ERP release is published.
    |
    */

    'version' => env('SYNTEK_VERSION', '1.0.0'),

    /*
    |--------------------------------------------------------------------------
    | Release / Update Channel
    |--------------------------------------------------------------------------
    |
    | Reserved for the future update system. The channel determines which
    | GitHub releases are offered to this installation (e.g. stable or beta).
    |
    */

    'release_channel' => env('SYNTEK_RELEASE_CHANNEL', 'stable'),

    'github' => [
        'owner' => env('SYNTEK_GITHUB_OWNER', 'syntekpro'),
        'repo' => env('SYNTEK_GITHUB_REPO', 'SyntekPro-ERP'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Update Agent
    |--------------------------------------------------------------------------
    |
    | The ERP communicates with a dedicated, internal update-agent container
    | through these settings. The agent is the only component with access to
    | the Docker socket and is responsible for applying ERP updates safely.
    |
    */

    'updater' => [
        'url' => env('UPDATER_URL', 'http://updater:8088'),
        'token' => env('UPDATER_API_TOKEN'),
    ],

];
