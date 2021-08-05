<?php

namespace Prokl\FrameworkExtensionBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Prokl\FrameworkExtensionBundle\Services\Wordpress\ErrorHandler\Contract\ErrorDbOperatorInterface;

/**
 * Class NotifierErrorCompilerPass
 * @package Prokl\FrameworkExtensionBundle\DependencyInjection\CompilerPass
 *
 * @since 05.08.2021
 *
 * @internal В корневом проекте должен быть определен сервис ErrorDbOperatorInterface (работа с БД)
 * Если нет, то соответствующие сервисы будут удалены из контейнера.
 */
final class NotifierErrorCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!defined('ABSPATH')) {
            return;
        }

        if (!$container->hasDefinition(ErrorDbOperatorInterface::class)
            &&
            !$container->hasAlias(ErrorDbOperatorInterface::class))
        {
            $container->removeDefinition('console_command.clear_error_command');
            $container->removeDefinition('wp_error_notifier');
        }
    }
}