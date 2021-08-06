<?php

namespace Prokl\FrameworkExtensionBundle\Tests\Cases\CompilerPass;

use Prokl\FrameworkExtensionBundle\DependencyInjection\CompilerPass\NotifierErrorCompilerPass;
use Prokl\FrameworkExtensionBundle\Services\Wordpress\ErrorHandler\Contract\ErrorDbOperatorInterface;
use Prokl\TestingTools\Base\BaseTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class NotifierErrorCompilerPassTest
 * @package Prokl\FrameworkExtensionBundle\Tests\Cases\CompilerPass
 *
 * @since 06.08.2021
 */
class NotifierErrorCompilerPassTest extends BaseTestCase
{
    /**
     * @var NotifierErrorCompilerPass $obTestObject
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

        $this->fakeService = new class {
            public function handle()
            {
            }
        };

        $this->container = new ContainerBuilder();

        $this->obTestObject = new NotifierErrorCompilerPass();
    }

    /**
     * Не в Wordpress.
     *
     * @return void
     */
    public function testNoWp() : void
    {
        $this->container->setDefinition(
            'wp_error_notifier',
            new Definition(get_class($this->fakeService))
        );

        $this->obTestObject->process($this->container);

        $this->assertTrue($this->container->hasDefinition('wp_error_notifier'));
    }

    /**
     * Нет ErrorDbOperatorInterface.
     *
     * @return void
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testNoDbOperator() : void
    {
        define('ABSPATH', true);

        $this->container->setDefinition(
            'wp_error_notifier',
            new Definition(get_class($this->fakeService))
        );

        $this->obTestObject->process($this->container);

        $this->assertFalse($this->container->hasDefinition('wp_error_notifier'));
    }

    /**
     * ErrorDbOperatorInterface и алиас.
     *
     * @return void
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testHasDbOperator() : void
    {
        define('ABSPATH', true);

        $this->container->setDefinition(
            ErrorDbOperatorInterface::class,
            new Definition(get_class($this->fakeService))
        );

        $this->container->setDefinition(
            'wp_error_notifier',
            new Definition(get_class($this->fakeService))
        );

        $this->obTestObject->process($this->container);

        $this->assertTrue($this->container->hasDefinition('wp_error_notifier'));
    }

    /**
     * Только alias.
     *
     * @return void
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testOnlyAliasDbOperator() : void
    {
        define('ABSPATH', true);

        $this->container->setDefinition(
            'fake_operator',
            new Definition(get_class($this->fakeService))
        );

        $this->container->setAlias(
            ErrorDbOperatorInterface::class,
            'fake_operator'
        );

        $this->container->setDefinition(
            'wp_error_notifier',
            new Definition(get_class($this->fakeService))
        );

        $this->obTestObject->process($this->container);

        $this->assertTrue($this->container->hasDefinition('wp_error_notifier'));
    }

    /**
     * Только alias.
     *
     * @return void
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testOnlyServiceDbOperator() : void
    {
        define('ABSPATH', true);

        $this->container->setDefinition(
            ErrorDbOperatorInterface::class,
            new Definition(get_class($this->fakeService))
        );

        $this->container->setDefinition(
            'wp_error_notifier',
            new Definition(get_class($this->fakeService))
        );

        $this->obTestObject->process($this->container);

        $this->assertTrue($this->container->hasDefinition('wp_error_notifier'));
    }
}
