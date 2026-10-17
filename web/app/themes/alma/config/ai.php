<?php

use App\Domains\Ai\Abilities\Leads\LeadStats;
use App\Domains\Ai\Abilities\Leads\QueryLeads;
use App\Domains\Ai\Abilities\Leads\UpdateLeadStatus;
use App\Domains\Ai\Abilities\Media\ListMedia;
use App\Domains\Ai\Abilities\Media\UploadMedia;
use App\Domains\Ai\Abilities\Pages\DescribePage;
use App\Domains\Ai\Abilities\Pages\ListBlocks;
use App\Domains\Ai\Abilities\Pages\ListPages;
use App\Domains\Ai\Abilities\Pages\ListPatterns;
use App\Domains\Ai\Abilities\Posts\CreateDraftPost;
use App\Domains\Ai\Abilities\Redirects\CreateRedirect;
use App\Domains\Ai\Abilities\Redirects\DeleteRedirect;
use App\Domains\Ai\Abilities\Redirects\ListRedirects;
use App\Domains\Ai\Abilities\Site\SiteHealth;

return [

    /*
    |--------------------------------------------------------------------------
    | AI access
    |--------------------------------------------------------------------------
    |
    | Exposes the site to Claude (claude.ai, Desktop, Code) as WordPress
    | abilities, served over MCP by the mcp-adapter plugin. The module only
    | loads when the core Abilities API exists (WordPress 6.9+). Set
    | AI_ENABLED=false to switch it off on an environment.
    |
    */

    'enabled' => (bool) env('AI_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Ability categories
    |--------------------------------------------------------------------------
    */

    'categories' => [
        'alma-site' => ['label' => 'Site', 'description' => 'Health and configuration of this site.'],
        'alma-content' => ['label' => 'Content', 'description' => 'Pages, patterns, blocks and posts.'],
        'alma-media' => ['label' => 'Media', 'description' => 'The media library.'],
        'alma-leads' => ['label' => 'Leads', 'description' => 'Enquiries captured by the site\'s forms.'],
        'alma-seo' => ['label' => 'SEO', 'description' => 'Redirects and search settings.'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Abilities
    |--------------------------------------------------------------------------
    |
    | One line per ability; delete a line to stop exposing it. Each ability
    | checks its own capability, so what an agent can do is decided by the
    | WordPress user it authenticates as (see 'agent' below).
    |
    */

    'abilities' => [
        // Site
        SiteHealth::class,

        // Content
        ListPages::class,
        DescribePage::class,
        ListPatterns::class,
        ListBlocks::class,
        CreateDraftPost::class,

        // Media
        ListMedia::class,
        UploadMedia::class,

        // Leads (personal data: needs alma_manage_leads)
        LeadStats::class,
        QueryLeads::class,
        UpdateLeadStatus::class,

        // SEO
        ListRedirects::class,
        CreateRedirect::class,
        DeleteRedirect::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | The agent user
    |--------------------------------------------------------------------------
    |
    | A dedicated WordPress user Claude authenticates as, with Application
    | Passwords issued per person in Settings → AI Access. It is an editor, and
    | each flag below grants (true) or explicitly revokes (false) a group of
    | capabilities on that user. `wp acorn alma:deploy` re-applies them on every
    | release, so removing a flag tightens access on the next deploy.
    |
    | Defaults are conservative: drafts and uploads only. Turn on what the
    | project needs.
    |
    */

    'agent' => [
        'login' => env('AI_AGENT_LOGIN', 'ai-agent'),
        'email' => env('AI_AGENT_EMAIL'),
        'role' => 'editor',

        'grants' => [
            'publish' => [
                'enabled' => (bool) env('AI_AGENT_CAN_PUBLISH', false),
                'capabilities' => ['publish_posts', 'publish_pages'],
            ],
            'edit_published' => [
                'enabled' => (bool) env('AI_AGENT_CAN_EDIT_PUBLISHED', false),
                'capabilities' => ['edit_published_posts', 'edit_published_pages', 'delete_published_posts', 'delete_published_pages'],
            ],
            'read_leads' => [
                'enabled' => (bool) env('AI_AGENT_CAN_READ_LEADS', false),
                'capabilities' => ['alma_manage_leads'],
            ],
            'upload_media' => [
                'enabled' => (bool) env('AI_AGENT_CAN_UPLOAD_MEDIA', true),
                'capabilities' => ['upload_files'],
            ],
            'manage_redirects' => [
                'enabled' => (bool) env('AI_AGENT_CAN_MANAGE_REDIRECTS', false),
                'capabilities' => ['alma_manage_redirects'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Limits
    |--------------------------------------------------------------------------
    */

    'media' => [
        'max_bytes' => (int) env('AI_MEDIA_MAX_MB', 10) * 1024 * 1024,

        // No SVG: it can carry script, and the library serves it from the site's own origin.
        'mime_types' => [
            'jpg|jpeg|jpe' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
        ],
    ],

    // How long a destructive ability's confirmation token stays valid, in seconds.
    'confirmation_ttl' => 600,

    'audit' => [
        'retention_days' => 90,

        // One write in this many also prunes old rows, so no cron job is needed.
        'prune_odds' => 50,

        // The adapter's own meta-tools: execute-ability is logged as the ability it runs.
        'ignore' => [
            'mcp-adapter/discover-abilities',
            'mcp-adapter/get-ability-info',
            'mcp-adapter/execute-ability',
        ],
    ],

];
