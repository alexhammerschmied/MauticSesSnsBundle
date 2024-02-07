<?php

return [
    'name'        => 'Amazon SES SNS Integration',
    'description' => 'Handles Amazon SNS notifications for Amazon SES.',
    'version'     => '1.0',
    'author'      => 'Alex Hammerschmied - Team hartmut.io',
    'services'    => [
        'events' => [
            'mautic.ses_sns_bundle.subscriber' => [
                'class'     => \MauticPlugin\MauticSesSnsBundle\EventSubscriber\CallbackSubscriber::class,
                'arguments' => [
                    'mautic.helper.core_parameters',
                    // Add other services your subscriber needs as arguments here
                ],
            ],
        ],
    ],
    'routes' => [
        'main' => [
            'mautic_ses_sns_callback' => [
                'path'       => '/ses/sns/callback',
                'controller' => 'MauticSesSnsBundle:Webhook:callback',
            ],
        ],
    ],
];
