<?php

namespace Prokl\FrameworkExtensionBundle\Tests\Cases\CompilerPass;

use Prokl\FrameworkExtensionBundle\DependencyInjection\CompilerPass\SqlLoggerCompilerPass;
use Prokl\TestingTools\Base\BaseTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class SqlLoggerCompilerPassTest
 * @package Prokl\FrameworkExtensionBundle\Tests\Cases\CompilerPass
 *
 * @since 06.08.2021
 */
class SqlLoggerCompilerPassTest extends BaseTestCase
{
    /**
     * @var SqlLoggerCompilerPass $obTestObject
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

        $this->obTestObject = new SqlLoggerCompilerPass();
    }

    /**
     * Не в Wordpress.
     *
     * @return void
     */
    public function testNoWp() : void
    {
        $this->container->setDefinition(
            'sql.logger.monolog',
            new Definition(get_class($this->fakeService))
        );

        $this->obTestObject->process($this->container);

        $this->assertTrue($this->container->hasDefinition('sql.logger.monolog'));
    }

    /**
     * Нет wpdb & logger.
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
            'sql.logger.monolog',
            new Definition(get_class($this->fakeService))
        );

        $this->obTestObject->process($this->container);

        $this->assertFalse($this->container->hasDefinition('sql.logger.monolog'));
    }

    /**
     * wpdb & logger.
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
            'sql.logger.monolog',
            new Definition(get_class($this->fakeService))
        );

        $this->container->setDefinition(
            'wpdb',
            new Definition(get_class($this->fakeService))
        );

        $this->container->setDefinition(
            'logger',
            new Definition(get_class($this->fakeService))
        );

        $this->obTestObject->process($this->container);

        $this->assertTrue($this->container->hasDefinition('sql.logger.monolog'));
    }

    /**
     * Только wpdb.
     *
     * @return void
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testOnlyWpDb() : void
    {
        define('ABSPATH', true);

        $this->container->setDefinition(
            'wpdb',
            new Definition(get_class($this->fakeService))
        );

        $this->container->setDefinition(
            'sql.logger.monolog',
            new Definition(get_class($this->fakeService))
        );

        $this->obTestObject->process($this->container);

        $this->assertFalse($this->container->hasDefinition('sql.logger.monolog'));
    }

    /**
     * Только logger.
     *
     * @return void
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testOnlyLogger() : void
    {
        define('ABSPATH', true);

        $this->container->setDefinition(
            'logger',
            new Definition(get_class($this->fakeService))
        );

        $this->container->setDefinition(
            'sql.logger.monolog',
            new Definition(get_class($this->fakeService))
        );

        $this->obTestObject->process($this->container);

        $this->assertFalse($this->container->hasDefinition('sql.logger.monolog'));
    }
}
