<?php

declare(strict_types=1);

namespace Prokl\FrameworkExtensionBundle\Services\Command\Lockable\Traits;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Trait EventDispatcherAwareTrait
 * @package Prokl\FrameworkExtensionBundle\Services\Command\Lockable\Traits
 */
trait EventDispatcherAwareTrait
{
    /**
     * @var EventDispatcher|EventDispatcherInterface $dispatcher
     */
    protected $dispatcher;

    /**
     * @required
     * @param EventDispatcherInterface $dispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $dispatcher): void
    {
        $this->dispatcher = $dispatcher;
    }
}
