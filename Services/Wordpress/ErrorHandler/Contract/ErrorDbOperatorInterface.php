<?php

namespace Prokl\FrameworkExtensionBundle\Services\Wordpress\ErrorHandler\Contract;

/**
 * Interface ErrorDbOperatorInterface
 * @package Prokl\FrameworkExtensionBundle\Services\Wordpress\ErrorHandler\Contract
 *
 * @since 05.08.2021
 */
interface ErrorDbOperatorInterface
{
    /**
     * Сохранить.
     *
     * @param string $error Ошибка.
     *
     * @return void
     */
    public function save(string $error) : void;

    /**
     * Есть такая запись уже или нет.
     *
     * @param string $error Ошибка.
     *
     * @return boolean
     */
    public function has(string $error) : bool;

    /**
     * Удалить все записи в таблице.
     *
     * @return void
     */
    public function clearTable() : void;
}