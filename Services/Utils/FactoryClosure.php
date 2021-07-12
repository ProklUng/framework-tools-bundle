<?php

namespace Prokl\FrameworkExtensionBundle\Services\Utils;

use Closure;

/**
 * Class FactoryClosure
 * @package Prokl\FrameworkExtensionBundle\Services\Utils
 */
class FactoryClosure
{
    /**
     * @param Closure $closure Closure.
     *
     * @return mixed
     */
    public function from(Closure $closure)
    {
        return $closure();
    }
}