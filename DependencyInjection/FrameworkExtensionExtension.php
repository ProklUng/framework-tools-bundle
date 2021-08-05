<?php

namespace Prokl\FrameworkExtensionBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Notifier\NotifierInterface;
use Prokl\CustomFrameworkExtensionsBundle\Services\Notifier\Notification\TelegramConvertorNotification;

/**
 * Class FrameworkExtensionExtension
 * @package Prokl\FrameworkExtensionBundle\FrameworkExtension\DependencyInjection
 *
 * @since 13.04.2021
 */
final class FrameworkExtensionExtension extends Extension
{
    private const DIR_CONFIG = '/../Resources/config';

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container) : void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . self::DIR_CONFIG)
        );

        $loader->load('services.yaml');
        $loader->load('listeners.yaml');
        $loader->load('commands.yaml');
        $loader->load('utils.yaml');
        $loader->load('validators.yaml');

        if (defined('B_PROLOG_INCLUDED') && B_PROLOG_INCLUDED === true) {
            $loader->load('bitrix.yaml');
        }

        if (defined('ABSPATH')) {
            $loader->load('wordpress.yaml');
            $loader->load('context.yaml');

            // Нотифайер ошибок.
            if (interface_exists(NotifierInterface::class)
                &&
                class_exists(TelegramConvertorNotification::class)
            ) {
                $loader->load('wordpress_error_handler.yaml');

                if (!array_key_exists('ADMIN_EMAIL', $_ENV)) {
                    $defLogger = $container->findDefinition('logger_notify_decorated');
                    $defLogger->replaceArgument(1, '');
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getAlias() : string
    {
        return 'framework_extension';
    }
}
