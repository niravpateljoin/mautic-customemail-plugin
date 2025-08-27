<?php
namespace MauticPlugin\CustomEmailBundle;

use Mautic\PluginBundle\Bundle\PluginBundleBase;
use MauticPlugin\CustomEmailBundle\Command\ProcessQueueCommand;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class Plugin extends PluginBundleBase
{
    // Register the Command in Symfony's Dependency Injection Container
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        // Register the command manually
        $container->register('mautic.customemail.command.processqueue', ProcessQueueCommand::class)
            ->addArgument($container->get('doctrine.dbal.default_connection'))
            ->addArgument($container->get('mautic.email.helper.mailer'))
            ->addTag('console.command');  // This tag tells Symfony it's a console command
    }
}
