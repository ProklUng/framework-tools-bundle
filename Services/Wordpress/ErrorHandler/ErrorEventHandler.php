<?php

namespace Prokl\FrameworkExtensionBundle\Services\Wordpress\ErrorHandler;

use Prokl\FrameworkExtensionBundle\Services\Wordpress\ErrorHandler\Contract\ErrorDbOperatorInterface;
use Prokl\FrameworkExtensionBundle\Services\Wordpress\Notifier\Notificator;

/**
 * Class ErrorEventHandler
 * @package Prokl\FrameworkExtensionBundle\Services\Wordpress\ErrorHandler
 *
 * @since 31.07.2021
 */
class ErrorEventHandler
{
    /**
     * @var boolean
     */
    private $isPhpUnitRunning;

    /**
     * @var Notificator $errorNotifier
     */
    private $errorNotifier;

    /**
     * @var string $env Окружение.
     */
    private $env;

    /**
     * @var ErrorDbOperatorInterface $repository Repository.
     */
    private $repository;

    /**
     * @var array $loggableEnv Окружения, в которых работает нотификация.
     */
    private $loggableEnv;

    /**
     * @var string Важность (в терминологии importancy-policy).
     */
    private $importancy;

    /**
     * ErrorEventHandler constructor.
     *
     * @param Notificator              $notificator Нотификатор.
     * @param ErrorDbOperatorInterface $repository  Repository.
     * @param string                   $env         Окружение.
     * @param array                    $loggableEnv Окружения, в которых работает нотификация.
     * @param string                   $importancy  Важность (в терминологии importancy-policy).
     */
    public function __construct(
        Notificator $notificator,
        ErrorDbOperatorInterface $repository,
        string $env,
        array $loggableEnv,
        string $importancy = 'urgent'
    ) {
        $this->errorNotifier = $notificator;
        $this->repository = $repository;
        $this->env = $env;
        $this->loggableEnv = $loggableEnv;
        $this->importancy = $importancy;

        $this->isPhpUnitRunning = defined('PHPUNIT_COMPOSER_INSTALL') || defined('__PHPUNIT_PHAR__');
    }

    /**
     * Обработчик события wp_die_handler.
     *
     * @param string $dieHandler Обработчик.
     *
     * @return array|string
     */
    public function handler(string $dieHandler)
    {
        if (is_admin() || $this->isPhpUnitRunning || !in_array($this->env, $this->loggableEnv, true)) {
            return $dieHandler;
        }

        return [$this, 'notify'];
    }

    /**
     * Заслать уведомление.
     *
     * @param mixed $message Сообщение.
     * @param mixed $title   Заголовок.
     * @param mixed $args    Аргументы.
     *
     * @return void
     */
    public function notify($message, $title, $args)
    {
        if (is_wp_error($message)) {
            $data = $message->get_error_data();
            $errorData = (string)$data['error']['message'];

            if (!$this->repository->has($errorData)) {
                $this->errorNotifier->send(
                    'Fatal error: ' . $_SERVER['REQUEST_URI'],
                    $errorData,
                    $this->importancy
                );

                $this->repository->save($errorData);
            }

            _default_wp_die_handler($errorData, $title, $args);
        }

        die();
    }
}
