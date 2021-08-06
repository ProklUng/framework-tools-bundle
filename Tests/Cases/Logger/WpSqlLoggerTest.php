<?php

namespace Prokl\FrameworkExtensionBundle\Tests\Cases\Logger;

use Prokl\FrameworkExtensionBundle\Services\Wordpress\Logger\WpSqlLogger;
use Prokl\TestingTools\Base\BaseTestCase;
use Psr\Log\LoggerInterface;
use wpdb;

/**
 * Class WpSqlLoggerTest
 * @package Prokl\FrameworkExtensionBundle\Tests\Cases\Logger
 *
 * @since 06.08.2021
 */
class WpSqlLoggerTest extends BaseTestCase
{
    /**
     * @var WpSqlLogger $obTestObject
     */
    protected $obTestObject;

    /**
     * @return void
     */
    public function testNoAdministrator() : void
    {
        $wpdb = $this->getMockWpDb();
        $wpdb->queries = [];

        $this->obTestObject = new WpSqlLogger(
            $wpdb,
            $this->getMockLogger(false)
        );

        $this->obTestObject->log();

        // Проверка на то, что info логгера не вызывается ни разу.
        $this->assertTrue(true);
    }

    /**
     * Логирование.
     *
     * @return void
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLogAdministrator() : void
    {
        $this->mockerFunctions->setNamespace('Prokl\FrameworkExtensionBundle\Services\Wordpress\Logger')
            ->full('current_user_can', true)
            ->mock();

        $wpdb = $this->getMockWpDb();
        $wpdb->queries = [['Test query', '111']];

        $this->obTestObject = new WpSqlLogger(
            $wpdb,
            $this->getMockLogger(true, ['Test query - (111 s)' . "\n\n"])
        );

        $this->obTestObject->log();

        // Проверка на то, что info логгера вызывается один раз с правильными аргументами.
        $this->assertTrue(true);
    }

    /**
     * Мок LoggerInterface.
     *
     * @param boolean $once Запускается один раз или нет.
     * @param array   $args Аргументы.
     *
     * @return mixed
     */
    private function getMockLogger(bool $once = true, array $args = [])
    {
        $mock = \Mockery::mock(LoggerInterface::class);
        if ($once) {
            $mock = $mock->shouldReceive('info')->once()->withArgs($args);
        } else {
            $mock = $mock->shouldReceive('info')->never();
        }

        return $mock->getMock();
    }

    /**
     * Мок wpdb.
     *
     * @return mixed
     */
    private function getMockWpDb()
    {
        return \Mockery::mock(wpdb::class);
    }
}
