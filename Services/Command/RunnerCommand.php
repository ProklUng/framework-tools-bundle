<?php

namespace Prokl\FrameworkExtensionBundle\Services\Command;

use Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Class RunnerCommand
 * @package Prokl\FrameworkExtensionBundle\Services\Command
 *
 * @since 11.07.2021
 */
class RunnerCommand
{
    /**
     * @var Application
     */
    private $application;

    /**
     * RunnerCommand constructor.
     *
     * @param Application $application
     */
    public function __construct(
        Application $application
    ) {
        $this->application = $application;
    }

    /**
     * @param string $commandName
     * @param array $params
     *
     * @return string
     * @throws Exception
     */
    public function run(string $commandName, array $params = [])
    {
        $this->application->setAutoExit(false);
        $this->application->setCatchExceptions(false);

        $params = array_merge(
            [
            'command' => $commandName
            ],
            $params
        );

        $output = new BufferedOutput();
        $this->application->run(new ArrayInput($params), $output);

        return $output->fetch();
    }
}
