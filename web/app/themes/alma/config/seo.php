<?php

/*
|--------------------------------------------------------------------------
| SEO
|--------------------------------------------------------------------------
|
| SEO is handled by The SEO Framework (TSF). Its settings live in the
| database, which drifts between environments and gets changed by accident;
| the keys listed under "settings" are re-applied on every deploy (and with
| `wp acorn alma:seo:apply`), so the repository is the source of truth for
| them. Keys not listed are left alone and stay editable in wp-admin.
|
| Unknown keys are refused, never written: a typo here cannot add junk to
| TSF's option. Key names come from TSF 5.2 (Data\Plugin\Setup).
|
*/

return [

    'tsf' => [

        // Set SEO_APPLY_ON_DEPLOY=false to stop the deploy from touching TSF's settings.
        'apply_on_deploy' => (bool) env('SEO_APPLY_ON_DEPLOY', true),

        'settings' => [
            // Titles: "Page title | Site name".
            'title_separator' => 'pipe',
            'title_location' => 'right',
            'title_rem_prefixes' => 1,

            // A marketing site has no use for author or date archives in search results.
            'author_noindex' => 1,
            'date_noindex' => 1,
            'search_noindex' => 1,
            'noindex_post_types' => ['attachment' => 1],
            'noindex_taxonomies' => ['post_format' => 1],

            // Social cards and structured data.
            'og_tags' => 1,
            'facebook_tags' => 1,
            'twitter_tags' => 1,
            'twitter_card' => 'summary_large_image',
            'ld_json_enabled' => 1,
            'ld_json_breadcrumbs' => 1,
            'knowledge_output' => 1,
            'knowledge_type' => 'organization',
            'oembed_remove_author' => 1,

            // TSF's optimized sitemap, listed in robots.txt.
            'sitemaps_output' => 1,
            'sitemaps_robots' => 1,
            'sitemaps_modified' => 1,

            'shortlink_tag' => 0,

            // Opt-in: ask AI training crawlers (GPTBot, CCBot, ...) to stay away via robots.txt.
            'robotstxt_block_ai' => (int) (bool) env('SEO_BLOCK_AI_CRAWLERS', false),
        ],

    ],

    'robots' => [

        // Appended to robots.txt for every host. Machine endpoints only: a page blocked here can
        // never be seen as noindex, so it can stay in results as a bare URL. Use the
        // <!-- alma:noindex --> marker for pages instead.
        'disallow' => [
            '/api/',
            '/livewire/',
        ],

    ],

];
