<?php

return [
    'name'        => 'Custom Email',
    'description' => 'Custom Email sending with basic setup',
    'version'     => '1.0.0',
    'author'      => 'Drashti',

    'services' => [
        'events' => [
            'customemail.campaign_action.send' => [
                'class'     => \MauticPlugin\CustomEmailBundle\CampaignAction\SendCustomEmailAction::class,
                'arguments' => ['mautic.email.model.email'],
            ],
            'mautic.customemail.campaign.subscriber' => [
                'class'     => \MauticPlugin\CustomEmailBundle\EventListener\CampaignSubscriber::class,
                'arguments' => [
                    '@doctrine.orm.entity_manager',
                    '@mautic.helper.mailer',
                    '@mautic.lead.model.lead',
                ],
            ],
        ],
        'forms' => [
            'mautic.form.type.customemail' => [
                'class' => \MauticPlugin\CustomEmailBundle\Form\Type\CustomEmailType::class,
                'alias' => 'customemail_action',
            ],
        ],
        'commands' => [
            'mautic.customemail.processqueue' => [
                'class'     => \MauticPlugin\CustomEmailBundle\Command\ProcessQueueCommand::class,
                'arguments' => [
                    '@doctrine.orm.entity_manager',
                    '@mautic.helper.mailer',
                    '@logger',
                ],
                'tag'       => 'console.command',
            ],
        ],
        'entities' => [
            'customemail.queue' => 'MauticPlugin\CustomEmailBundle\Entity\CustomEmailQueue',
        ],
    ],
];
