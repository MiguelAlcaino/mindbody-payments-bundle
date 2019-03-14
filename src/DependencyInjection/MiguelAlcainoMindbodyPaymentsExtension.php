<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\DependencyInjection;

use MiguelAlcaino\PaymentGateway\Interfaces\RefundHandlerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class MiguelAlcainoMindbodyPaymentsExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);

        $container->setAlias(RefundHandlerInterface::class, $config['handler']['refund_handler']);
        $container->setAlias('payment_gateway_refund_handler', $config['handler']['refund_handler']);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('services.yaml');
        $loader->load('controller_config.yaml');
    }

}