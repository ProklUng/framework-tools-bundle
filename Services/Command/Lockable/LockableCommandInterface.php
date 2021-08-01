<?php

declare(strict_types=1);

namespace Prokl\FrameworkExtensionBundle\Services\Command\Lockable;

/**
 * Interface LockableCommandInterface
 * @package Prokl\FrameworkExtensionBundle\Services\Command\Lockable
 */
interface LockableCommandInterface
{
    public const LOCKABLE_COMMAND_REFRESH_EVENT_NAME = 'lockable_command_refresh_event';
    public const DISABLE_LOCK_COMMAND_OPTION_NAME    = 'disable-lock';

    public const COMMAND_IS_LOCKED_ERROR_CODE = 100;
    public const DEFAULT_TTL                  = 60;

    /**
     * @return string
     */
    public function getDisableLockCommandOptionName(): string;

    /**
     * @return integer
     */
    public function getCommandIsLockedErrorCode(): int;

    /**
     * @return integer
     */
    public function getLockTtl(): int;
}
