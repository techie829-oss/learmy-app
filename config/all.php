<?php

return [
    'meta' => [
        'api_version' => env('META_GRAPH_API_VERSION', 'v22.0'),
        'graph_url'   => env('META_GRAPH_API_URL', 'https://graph.facebook.com/v22.0'),
    ],

    'features' => [
        'whatsapp'        => true,   // Messaging, Inbox, Channel Setup, Contacts
        'facebook'        => false,  // Messenger inbox filter + channel setup
        'instagram'       => false,  // Instagram inbox filter + channel setup
        'broadcasts'      => false,  // Campaigns, SMS Gateways, Email Server
        'social_media'    => false,  // Social Media posts/calendar/accounts
        'ai'              => false,  // AI Chatbots, Knowledge Bases, Providers
        'automations'     => false,  // Automation workflows
        'leads'           => false,  // Lead Scraper
        'developer_tools' => false,  // API Tokens, Webhooks, API Docs, Media Library
        'reports'         => false,  // Analytics & Reports
    ],
];
