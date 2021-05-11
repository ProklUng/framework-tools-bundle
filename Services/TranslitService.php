<?php

namespace Prokl\FrameworkExtensionBundle\Services;

use Denismitr\Translit\Translit;
use Prokl\FrameworkExtensionBundle\Services\Contracts\TranslitInterface;

/**
 * Class TranslitService
 * @package Prokl\FrameworkExtensionBundle\Services
 *
 * @since 08.09.2020
 */
class TranslitService implements TranslitInterface
{
    /**
     * @var Translit $handler Транслитер.
     */
    private $handler;

    /**
     * TranslitService constructor.
     *
     * @param Translit $handler Транслитер.
     */
    public function __construct(
        Translit $handler
    ) {

        $this->handler = $handler;
    }

    /**
     * Транслитировать строку.
     *
     * @param string $value Значение.
     *
     * @return string
     */
    public function transform(string $value) : string
    {
        return $this->handler->forString($value)
                             ->getSlug();
    }
}
