<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Post Types
    |--------------------------------------------------------------------------
    |
    | Post types to be registered with Extended CPTs
    | <https://github.com/johnbillion/extended-cpts>
    |
    */
    'post_types' => [
        'ai_conversation' => [
            'menu_icon' => 'dashicons-format-chat',
            'supports' => ['title', 'custom-fields'],
            'public' => true,
            'show_ui' => true,
            'show_in_rest' => true,
            'names' => [
                'singular' => 'AI Conversation',
                'plural' => 'AI Conversations',
                'slug' => 'ai_conversation',
            ],
            'meta_fields' => [
                'thread_id' => [
                    'type' => 'string',
                    'required' => true,
                ],
                'agent_id' => [
                    'type' => 'string',
                    'required' => true,
                ],
                'messages' => [
                    'type' => 'text',
                    'required' => true,
                ],
                'user_id' => [
                    'type' => 'integer',
                    'required' => false,
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Taxonomies
    |--------------------------------------------------------------------------
    |
    | Taxonomies to be registered with Extended CPTs library
    | <https://github.com/johnbillion/extended-cpts>
    |
    */
];