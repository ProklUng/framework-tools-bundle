<?php

namespace Prokl\FrameworkExtensionBundle\Services\Wordpress\Notifier;

use Prokl\CustomFrameworkExtensionsBundle\Services\Notifier\Notification\TelegramConvertorNotification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;

/**
 * Class Notificator
 * @package Prokl\FrameworkExtensionBundle\Services\Wordpress\Notifier
 *
 * @since 31.07.2021
 */
class Notificator
{
    /**
     * @var NotifierInterface $notifier Нотификатор.
     */
    private $notifier;

    /**
     * @var string $email Email.
     */
    private $email;

    /**
     * Notificator constructor.
     *
     * @param NotifierInterface $notifier Нотификатор.
     * @param string            $email    Email.
     */
    public function __construct(NotifierInterface $notifier, string $email)
    {
        $this->notifier = $notifier;
        $this->email = $email;
    }

    /**
     * @param string $title
     * @param string $body
     * @param string $importancy
     *
     * @return void
     */
    public function send(
        string $title,
        string $body,
        string $importancy = TelegramConvertorNotification::IMPORTANCE_MEDIUM
    ) : void {
        $notification = (new TelegramConvertorNotification($title))
            ->content($body)
            ->importance($importancy);

        $this->notifier->send($notification, new Recipient($this->email));
    }
}