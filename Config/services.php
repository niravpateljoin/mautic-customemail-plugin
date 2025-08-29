<?php

declare(strict_types=1);

use Mautic\CoreBundle\DependencyInjection\MauticCoreExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    $excludes = [
        'node_modules',
    ];

    // Load all services except excluded dirs
    $services->load('MauticPlugin\\CustomEmailBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MauticCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');

    // Load repositories separately (Doctrine style)
    $services->load('MauticPlugin\\CustomEmailBundle\\Entity\\', '../Entity/*Repository.php');

    // 👇 Add alias for your queue model (if you want to inject it easily)
    $services->alias('customemail.queue', MauticPlugin\CustomEmailBundle\Entity\CustomEmailQueue::class);
};

