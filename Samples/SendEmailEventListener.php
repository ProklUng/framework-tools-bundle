<?php

namespace Prokl\FrameworkExtensionBundle\Samples;

use Prokl\FrameworkExtensionBundle\Services\DelayedEvents\Delayable;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class SendEmailEvent
 * Пример оформления подписчика. Название события = классу события.
 * @package Prokl\FrameworkExtensionBundle\Samples
 *
 * @since 13.04.2021
 */
class SendEmailEventListener extends Event implements Delayable
{
    /**
     * @var integer $value
     */
    public $value = 3;

    /**
     * @param SendEmailEvent $event Событие.
     *
     * @return SendEmailEvent
     */
    public function handle(SendEmailEvent $event): SendEmailEvent
    {
        $this->value = $event->value;

        return $event;
    }
}
