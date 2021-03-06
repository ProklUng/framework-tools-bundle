<?php

declare(strict_types=1);

namespace Prokl\FrameworkExtensionBundle\Services\Command\Lockable;

use Prokl\FrameworkExtensionBundle\Services\Command\Lockable\Traits\EventDispatcherAwareTrait;
use Prokl\FrameworkExtensionBundle\Services\Command\Lockable\Traits\LockFactoryAwareTrait;
use Prokl\FrameworkExtensionBundle\Services\Command\Lockable\Traits\LoggerAwareTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\Exception\LockAcquiringException;
use Symfony\Component\Lock\Exception\LockConflictedException;
use Symfony\Component\Lock\Exception\LockExpiredException;
use Symfony\Component\Lock\LockInterface;

/**
 * Class AbstractLockableCommand
 * @package Local\Services\Command
 *
 */
abstract class AbstractLockableCommand extends Command implements LockableCommandInterface
{
    use EventDispatcherAwareTrait;
    use LockFactoryAwareTrait;
    use LoggerAwareTrait;

    /**
     * @var LockInterface $lock
     */
    protected $lock;

    /**
     * {@inheritdoc}
     */
    public function getDisableLockCommandOptionName(): string
    {
        return LockableCommandInterface::DISABLE_LOCK_COMMAND_OPTION_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommandIsLockedErrorCode(): int
    {
        return LockableCommandInterface::COMMAND_IS_LOCKED_ERROR_CODE;
    }

    /**
     * {@inheritdoc}
     */
    public function getLockTtl(): int
    {
        return LockableCommandInterface::DEFAULT_TTL;
    }

    /**
     * {@inheritdoc}
     */
    public function run(InputInterface $input, OutputInterface $output): int
    {
        if ($input->hasOption($this->getDisableLockCommandOptionName())) {
            return parent::run($input, $output);
        }

        $name = $this->getName();

        $this->lock = $this->lockFactory->createLock(sprintf('command_lock|%s', $name), $this->getLockTtl());

        try {
            if (!$this->lock->acquire()) {
                $this->warning(sprintf('The command "%s" is already running', $name));

                return $this->getCommandIsLockedErrorCode();
            }
        } catch (LockExpiredException | LockConflictedException | LockAcquiringException $e) {
            $this->error($e, get_defined_vars());

            return $this->getCommandIsLockedErrorCode();
        }

        try {
            return parent::run($input, $output);
        } finally {
            $this->lock->release();
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    protected function configure()
    {
        parent::configure();

        $this->addOption(
            $this->getDisableLockCommandOptionName(),
            '',
            InputOption::VALUE_NONE,
            'Disable command locking'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        $this->dispatcher->addListener(
            LockableCommandInterface::LOCKABLE_COMMAND_REFRESH_EVENT_NAME,
            function (): void {
                $this->debug(sprintf('Command "%s" received lock refresh event', $this->getName()));

                try {
                    $this->lock->refresh();
                } catch (LockConflictedException | LockAcquiringException $e) {
                    $this->error($e);
                }
            }
        );
    }
}
