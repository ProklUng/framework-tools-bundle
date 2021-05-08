<?php

namespace Prokl\FrameworkExtensionBundle\Event;

use Prokl\FrameworkExtensionBundle\Services\DelayedEvents\DelayedEventDispatcher;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

/**
 * Class FlushDelayedEventsListener
 * @package Prokl\FrameworkExtensionBundle\Event
 *
 * @since 13.04.2021
 */
class FlushDelayedEventsListener
{
    /**
     * @var DelayedEventDispatcher $delayedEventDispatcher Диспетчер отложенных событий.
     */
    private $delayedEventDispatcher;

    /**
     * OnKernelTerminateRequestListener constructor.
     *
     * @param DelayedEventDispatcher $delayedEventDispatcher Диспетчер отложенных событий.
     */
    public function __construct(DelayedEventDispatcher $delayedEventDispatcher)
    {
        $this->delayedEventDispatcher = $delayedEventDispatcher;
    }

    /**
     * @param TerminateEvent $event Событие.
     *
     * @return void
     */
    public function handle(TerminateEvent $event) : void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $this->delayedEventDispatcher->flush();
    }
}
