<?php

namespace Prokl\FrameworkExtensionBundle\Samples;

use Prokl\FrameworkExtensionBundle\Services\DelayedEvents\Delayable;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class SendEmailEvent
 * @package Prokl\FrameworkExtensionBundle\Samples
 *
 * @since 13.04.2021
 */
class SendEmailEvent extends Event implements Delayable
{
    /**
     * @var integer $value
     */
    public $value = 3;
}
