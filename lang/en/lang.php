<?php

return [
    'plugin' => [
        'name' => 'Campaign',
        'description' => 'Campaign management',
    ],
    'permissions' => [
        'manage' => 'Manage campaigns',
    ],
    'components' => [
        'subscribe' => [
            'heading' => 'Subscribe to newsletter',
            'description' => 'Get my latest news!',      
            'form' => [
                'name' => 'Name',
                'email' => 'Email',
                'button' => 'Subscribe',
            ],
            'privacy' => 'I respect your <a href=":privacyLink">privacy</a>. Unsubscribe at any time.',
            'feedback' => 'Check your email for a confirmation link!',
        ],
        'confirm' => [
            'success' => [
                'heading' => 'Subscription confirmed!',
                'body' => 'Thank you, :subscriber. Your subscription has been confirmed.',
            ],
            'error' => [
                'heading' => 'Subscription failed!',
                'body' => 'This confirmation link is invalid or has already been used.',
            ],
            'mail' => [
                'subject' => 'Confirm your subscription',
                'body' => 'Please confirm your subscription by clicking the button below:',
                'button' => 'Confirm subscription',
                'ignore' => 'If you did not request this, you can safely ignore this email.',
            ],
        ],
    ],
];
