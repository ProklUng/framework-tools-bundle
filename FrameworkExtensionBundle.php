<?php

namespace Prokl\FrameworkExtensionBundle;

use Prokl\FrameworkExtensionBundle\DependencyInjection\FrameworkExtensionExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class FrameworkExtensionBundle
 * @package Prokl\FrameworkExtensionBundle
 *
 * @since 13.04.2021
 */
final class FrameworkExtensionBundle extends Bundle
{
   /**
   * @inheritDoc
   */
    public function getContainerExtension()
    {
        if ($this->extension === null) {
            $this->extension = new FrameworkExtensionExtension();
        }

        return $this->extension;
    }
}
