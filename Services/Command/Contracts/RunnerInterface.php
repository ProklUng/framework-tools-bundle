<?php

namespace Prokl\FrameworkExtensionBundle\Services\Command\Contracts;

use Exception;

/**
 * Interface RunnerInterface
 * @package Prokl\FrameworkExtensionBundle\Services\Command\Contracts
 *
 * @since 12.07.2021
 */
interface RunnerInterface
{
    /**
     * Запустить команду.
     *
     * @param string $command Команда.
     * @param array  $params  Параметры.
     *
     * @return string
     * @throws Exception
     */
    public function run(string $command, array $params = []) : string;
}