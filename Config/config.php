<?php

return [
    'name'        => 'Custom Email',
    'description' => 'Custom Email sending with basic setup',
    'version'     => '1.0.0',
    'author'      => 'Drashti',

    // Register services so Mautic can find your subscriber and form
    'services' => [
        'events' => [
            'customemail.campaign_action.send' => [
                'class'     => \MauticPlugin\CustomEmailBundle\CampaignAction\SendCustomEmailAction::class,
                'arguments' => [
                    'mautic.email.model.email',
                ],
            ],
        ],
        'forms' => [
            'mautic.form.type.customemail' => [
                'class' => \MauticPlugin\CustomEmailBundle\Form\Type\CustomEmailType::class,
                'alias' => 'customemail_action', // optional alias
            ],
        ],
        'models' => [
            'mautic.customemail.model.customemail' => [
                'class' => \MauticPlugin\CustomEmailBundle\Entity\CustomEmail::class,
            ],
            'mautic.customemail.model.queue' => [
                'class' => \MauticPlugin\CustomEmailBundle\Entity\CustomEmailQueue::class,
            ],
        ],
        'commands' => [
            'mautic.customemail.processqueue' => [
                'class' => \MauticPlugin\CustomEmailBundle\Command\ProcessQueueCommand::class,
                'arguments' => ['@doctrine.dbal.default_connection', '@mautic.helper.mailer'], // <-- fix here
                'tags' => ['console.command'],
            ],
        ],


    ],
];
