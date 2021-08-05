<?php

namespace Prokl\FrameworkExtensionBundle\Services\Wordpress\Notifier;

use Psr\Log\LoggerInterface;

/**
 * Class LoggerDecorator
 * @package Prokl\FrameworkExtensionBundle\Services\Wordpress\Notifier
 *
 * @since 31.07.2021
 */
class LoggerDecorator implements LoggerInterface
{
    /**
     * @var LoggerInterface $decoratedLogger Логгер.
     */
    private $decoratedLogger;

    /**
     * @var Notificator $notificator Нотификатор.
     */
    private $notificator;

    /**
     * LoggerDecorator constructor.
     *
     * @param LoggerInterface $logger      Логгер.
     * @param Notificator     $notificator Нотификатор.
     */
    public function __construct(
        LoggerInterface $logger,
        Notificator $notificator
    ) {
        $this->decoratedLogger = $logger;
        $this->notificator = $notificator;
    }

    /**
     * @inheritdoc
     */
    public function emergency($message, array $context = [])
    {
        $this->decoratedLogger->emergency($message, $context);
        $this->sendNotify($message, $context);
    }

    /**
     * @inheritdoc
     */
    public function alert($message, array $context = [])
    {
        $this->decoratedLogger->alert($message, $context);
        $this->sendNotify($message, $context);
    }

    /**
     * @inheritdoc
     */
    public function critical($message, array $context = [])
    {
        $this->decoratedLogger->critical($message, $context);
        $this->sendNotify($message, $context);
    }

    /**
     * @inheritdoc
     */
    public function error($message, array $context = [])
    {
        $this->decoratedLogger->error($message, $context);
        $this->sendNotify($message, $context);
    }

    /**
     * @inheritdoc
     */
    public function warning($message, array $context = [])
    {
        $this->decoratedLogger->warning($message, $context);
        $this->sendNotify($message, $context);
    }

    /**
     * @inheritdoc
     */
    public function notice($message, array $context = [])
    {
        $this->decoratedLogger->notice($message, $context);
        $this->sendNotify($message, $context);
    }

    /**
     * @inheritdoc
     */
    public function info($message, array $context = [])
    {
        $this->decoratedLogger->info($message, $context);
        $this->sendNotify($message, $context);
    }

    /**
     * @inheritdoc
     */
    public function debug($message, array $context = [])
    {
        $this->decoratedLogger->debug($message, $context);
        $this->sendNotify($message, $context);
    }

    /**
     * @inheritdoc
     */
    public function log($level, $message, array $context = [])
    {
        $this->decoratedLogger->log($level, $message, $context);
        $this->sendNotify($message, $context);
    }

    /**
     * Заслать нотификацию согласно channel_policy.
     *
     * @param mixed $message Сообщение.
     * @param array $context Контекст.
     *
     * @return void
     */
    private function sendNotify($message, array $context = []) : void
    {
        $preparedContext = $this->prepareContext($context);
        $this->notificator->send(
            (string)$message,
            $preparedContext,
            'urgent'
        );
    }

    /**
     * Контекст в строку.
     *
     * @param array $context Контекст.
     *
     * @return string
     */
    private function prepareContext(array $context) : string
    {
        return json_encode($context);
    }
}
