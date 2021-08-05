<?php

namespace Prokl\FrameworkExtensionBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Prokl\FrameworkExtensionBundle\Services\Wordpress\ErrorHandler\Contract\ErrorDbOperatorInterface;

/**
 * Class SqlLoggerCompilerPass
 * @package Prokl\FrameworkExtensionBundle\DependencyInjection\CompilerPass
 *
 * @since 05.08.2021
 */
final class SqlLoggerCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!defined('ABSPATH')) {
            return;
        }

        if (!$container->hasDefinition('wpdb')
            ||
            !$container->hasDefinition('logger')) {
            $container->removeDefinition('sql.logger.monolog');
        }
    }
}
