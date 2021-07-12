<?php

namespace Prokl\FrameworkExtensionBundle\Services\Command;

use Exception;
use Prokl\FrameworkExtensionBundle\Services\Command\Contracts\RunnerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Class Runner
 * @package Prokl\FrameworkExtensionBundle\Services\Command
 *
 * @since 12.07.2021
 */
class Runner implements RunnerInterface
{
    /**
     * @var Application $application Command application.
     */
    private $application;

    /**
     * @var boolean $supressOutput Запрет вывода на экран.
     */
    private $supressOutput = true;

    /**
     * RabbitMqConsuming constructor.
     *
     * @param Application $application Command application.
     */
    public function __construct(
        Application $application
    ) {
        $this->application = clone $application;
    }

    /**
     * {@inheritDoc}
     */
    public function run(string $command, array $params = []) : string
    {
        $this->application->setAutoExit(false);
        $this->application->setCatchExceptions(false);

        $input = new  ArrayInput(
            array_merge(['command' => $command], $params)
        );

        $output = $this->supressOutput ? new NullOutput() : new BufferedOutput();

        $this->application->run($input, $output);

        return $output->fetch();
    }

    /**
     * {@inheritDoc}
     */
    public function setSupressOutput(bool $supressOutput): void
    {
        $this->supressOutput = $supressOutput;
    }
}
