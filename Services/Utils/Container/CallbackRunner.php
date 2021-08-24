<?php

namespace Prokl\FrameworkExtensionBundle\Services\Utils\Container;

use InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class CallbackRunner
 * @since 24.08.2021
 *
 * @internal Fork from https://github.com/DarvinStudio/darvin-utils/blob/master/Callback/CallbackRunner.php
 */
class CallbackRunner implements CallbackRunnerInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function runCallback(string $id, ?string $method = null, ...$args)
    {
        if (!$this->container) {
            throw new InvalidArgumentException('Container not set.');
        }

        if ($this->container->has($id)) {
            $service = $this->container->get($id);

            if (null === $method) {
                if (!is_callable($service)) {
                    throw new InvalidArgumentException(
                        sprintf('Class "%s" is not callable. Make sure it has "__invoke()" method.', get_class($service))
                    );
                }

                return $service(...$args);
            }
            if (!method_exists($service, $method)) {
                throw new InvalidArgumentException(sprintf('Method "%s::%s()" does not exist.', get_class($service), $method));
            }

            return $service->$method(...$args);
        }

        if (!class_exists($id)) {
            throw new InvalidArgumentException(
                sprintf('Service or class "%s" does not exist. If it is a service, make sure it is public.', $id)
            );
        }

        if (null === $method) {
            throw new InvalidArgumentException('Method not specified.');
        }
        if (!method_exists($id, $method)) {
            throw new InvalidArgumentException(sprintf('Method "%s::%s()" does not exist.', $id, $method));
        }

        $callable = [$id, $method];

        if (!is_callable($callable)) {
            throw new InvalidArgumentException(sprintf('Method "%s::%s()" is not statically callable.', $id, $method));
        }

        return $callable(...$args);
    }
}