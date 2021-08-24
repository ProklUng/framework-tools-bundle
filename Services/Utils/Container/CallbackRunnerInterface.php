<?php declare(strict_types=1);

namespace Prokl\FrameworkExtensionBundle\Services\Utils\Container;

/**
 * Interface CallbackRunnerInterface
 *
 * @since 24.08.2021
 * @internal Fork from https://github.com/DarvinStudio/darvin-utils/blob/master/Callback/CallbackRunner.php
 */
interface CallbackRunnerInterface
{
    /**
     * @param string      $id      Class or service ID
     * @param string|null $method  Method to call
     * @param mixed       ...$args Arguments to pass to callable method
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function runCallback(string $id, ?string $method = null, ...$args);
}