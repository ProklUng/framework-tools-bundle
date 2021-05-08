<?php

namespace Prokl\FrameworkExtensionBundle\Services\DelayedEvents;

/**
 * Class DelayableEventsControllerTrait
 * Трэйт для контроллеров с инжекцией DelayedEventDispatcher через сеттер.
 * @package Prokl\FrameworkExtensionBundle\Services\DelayedEvents
 *
 * @since 13.04.2021
 */
class DelayableEventsControllerTrait
{
    /**
     * @var DelayedEventDispatcher $delayedEventDispatcher Диспетчер отложенных событий.
     */
    private $delayedEventDispatcher;

    /**
     * @param DelayedEventDispatcher $delayedEventDispatcher Диспетчер отложенных событий.
     *
     * @return void
     */
    public function setDelayedEventDispatcher(
        DelayedEventDispatcher $delayedEventDispatcher
    ): void {
        $this->delayedEventDispatcher = $delayedEventDispatcher;
    }
}
