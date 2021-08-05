<?php

namespace Prokl\FrameworkExtensionBundle\Services\Wordpress\ErrorHandler;

use Prokl\FrameworkExtensionBundle\Services\Wordpress\ErrorHandler\Contract\ErrorDbOperatorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ClearErrorCommand
 * @package Prokl\FrameworkExtensionBundle\Services\Wordpress\ErrorHandler
 *
 * @since 01.08.2021
 */
class ClearErrorCommand extends Command
{
    /**
     * @var ErrorDbOperatorInterface $repository Repository.
     */
    private $repository;

    /**
     * ClearErrorCommand constructor.
     *
     * @param ErrorDbOperatorInterface $repository Repository.
     */
    public function __construct(ErrorDbOperatorInterface $repository)
    {
        $this->repository = $repository;
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure() : void
    {
        $this->setName('fatal-error:clear')
            ->setDescription('Clear fatal error table.')
            ->setHelp('Clear fatal error table.');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $output->writeln('Start clearing...');

        $this->repository->clearTable();

        $output->writeln('Clearing complete...');

        return 0;
    }
}