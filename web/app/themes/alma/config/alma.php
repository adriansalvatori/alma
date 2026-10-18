<?php

use App\Domains\Account\AccountServiceProvider;
use App\Domains\Ai\AiServiceProvider;
use App\Domains\Commerce\CommerceServiceProvider;
use App\Domains\Content\ContentServiceProvider;
use App\Domains\Lead\LeadServiceProvider;
use App\Domains\Payment\PaymentServiceProvider;
use App\Domains\Seo\SeoServiceProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | Modules
    |--------------------------------------------------------------------------
    |
    | Each module is a folder under app/Domains with its own service provider.
    | Remove a line to drop a module from a project entirely, or list its key
    | in ALMA_DISABLED_MODULES to switch it off per environment.
    |
    | Optional modules check their own prerequisites: Commerce only registers
    | when WooCommerce is active, Payment only when PAYMENTS_ENABLED=true.
    |
    */

    'modules' => [
        'content' => ContentServiceProvider::class,
        'seo' => SeoServiceProvider::class,
        'lead' => LeadServiceProvider::class,
        'account' => AccountServiceProvider::class,
        'commerce' => CommerceServiceProvider::class,
        'payment' => PaymentServiceProvider::class,
        'ai' => AiServiceProvider::class,
    ],

    'disabled' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('ALMA_DISABLED_MODULES', '')),
    ))),

    /*
    |--------------------------------------------------------------------------
    | Comments
    |--------------------------------------------------------------------------
    |
    | Off by default: marketing sites don't take comments, and an open form is
    | a spam inlet. Off closes comments and pingbacks everywhere (front end,
    | wp-admin, toolbar, REST, feeds) and hides existing ones without deleting
    | them. Set COMMENTS_ENABLED=true for a site that wants them.
    |
    */

    'comments' => (bool) env('COMMENTS_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Release
    |--------------------------------------------------------------------------
    |
    | The running release, written into .env by the deploy script (the git tag,
    | or the commit SHA for staging). Reported to Sentry and the health endpoint.
    |
    */

    'version' => env('APP_VERSION', 'dev'),

];
