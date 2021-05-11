<?php

namespace Prokl\FrameworkExtensionBundle\Services\Contracts;

/**
 * Interface TranslitInterface
 * @package Prokl\FrameworkExtensionBundle\Services\Contracts
 *
 * @since 08.09.2020
 */
interface TranslitInterface
{
    /**
     * Транслитировать строку.
     *
     * @param string $value Значение.
     *
     * @return string
     */
    public function transform(string $value) : string;
}
