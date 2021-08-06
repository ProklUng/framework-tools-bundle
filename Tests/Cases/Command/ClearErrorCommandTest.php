<?php

namespace Prokl\FrameworkExtensionBundle\Tests\Cases\Command;

use Prokl\FrameworkExtensionBundle\Services\Wordpress\ErrorHandler\ClearErrorCommand;
use Prokl\FrameworkExtensionBundle\Services\Wordpress\ErrorHandler\Contract\ErrorDbOperatorInterface;
use Prokl\TestingTools\Base\CommandTestCase;

/**
 * Class ClearErrorCommandTest
 * @package Prokl\FrameworkExtensionBundle\Tests\Cases\Command
 *
 * @since 06.08.2021
 */
class ClearErrorCommandTest extends CommandTestCase
{
    /**
     * @var ClearErrorCommand $obTestObject
     */
    protected $obTestObject;

    /**
     * @var object $fakeService
     */
    private $fakeService;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->obTestObject = new ClearErrorCommand($this->getMockErrorDbOperatorInterface());
    }

    /**
     * Run.
     *
     * @return void
     */
    public function testRun() : void
    {
        $result = $this->executeCommand($this->obTestObject, 'fatal-error:clear');

        $this->assertStringContainsString('Start clearing...', $result);
        $this->assertStringContainsString('Clearing complete...', $result);
    }

    /**
     * Мок ErrorDbOperatorInterface.
     *
     * @return mixed
     */
    private function getMockErrorDbOperatorInterface()
    {
        $mock = \Mockery::mock(ErrorDbOperatorInterface::class)->shouldReceive('clearTable')->once();

        return $mock->getMock();
    }
}
