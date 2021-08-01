<?php

declare(strict_types=1);

namespace Prokl\FrameworkExtensionBundle\Services\Command\Lockable\Traits;

use Symfony\Component\Lock\LockFactory;

/**
 * Trait LockFactoryAwareTrait
 * @package Prokl\FrameworkExtensionBundle\Services\Command\Lockable\Traits
 */
trait LockFactoryAwareTrait
{
    /**
     * @var LockFactory $lockFactory
     */
    protected $lockFactory;

    /**
     * @required
     * @param LockFactory $lockFactory
     */
    public function setLockFactory(LockFactory $lockFactory): void
    {
        $this->lockFactory = $lockFactory;
    }
}
