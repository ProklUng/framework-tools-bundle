<?php

namespace Prokl\FrameworkExtensionBundle\Tests\Cases;

use Mockery;
use Prokl\FrameworkExtensionBundle\Services\Wordpress\ErrorHandler\Contract\ErrorDbOperatorInterface;
use Prokl\FrameworkExtensionBundle\Services\Wordpress\ErrorHandler\ErrorEventHandler;
use Prokl\FrameworkExtensionBundle\Services\Wordpress\Notifier\Notificator;
use Prokl\TestingTools\Base\BaseTestCase;

/**
 * Class WpSqlLoggerTest
 * @package Prokl\FrameworkExtensionBundle\Tests\Cases
 *
 * @since 06.08.2021
 */
class ErrorEventHandlerTest extends BaseTestCase
{
    /**
     * @var ErrorEventHandler $obTestObject
     */
    protected $obTestObject;
    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->getMockTelegramConvertorNotification();
        $this->getMockRecipient();
    }

    /**
     * @return void
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testNotify() : void
    {
        $_SERVER['REQUEST_URI'] = '/';

        $this->mockerFunctions->setNamespace('Prokl\FrameworkExtensionBundle\Services\Wordpress\ErrorHandler')
            ->full('is_wp_error', true)
            ->mock();

        $this->mockerFunctions->setNamespace('Prokl\FrameworkExtensionBundle\Services\Wordpress\ErrorHandler')
            ->full('_default_wp_die_handler', null)
            ->mock();

        $this->obTestObject = new ErrorEventHandler(
            $this->getMockNotificator(true),
            $this->getMockErrorDbOperatorInterface(true, true, false),
            'dev',
            ['dev'],
            'urgent'
        );

        $mockWpError = $this->getMockWpError();

        $this->obTestObject->notify(
            $mockWpError,
            'Test title',
            []
        );

        // Проверка идет на правильное количество вызовов
        $this->assertTrue(true);
    }

    /**
     * @return void
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testNotifyNotWpError() : void
    {
        $this->mockerFunctions->setNamespace('Prokl\FrameworkExtensionBundle\Services\Wordpress\ErrorHandler')
            ->full('is_wp_error', false)
            ->mock();

        $this->obTestObject = new ErrorEventHandler(
            $this->getMockNotificator(false),
            $this->getMockErrorDbOperatorInterface(false, false, false),
            'dev',
            ['dev'],
            'urgent'
        );

        $mockWpError = Mockery::mock('WP_Error')
            ->shouldReceive('get_error_data')->never();

        $this->obTestObject->notify(
            $mockWpError,
            'Test title',
            []
        );

        // Проверка идет на правильное количество вызовов
        $this->assertTrue(true);
    }

    /**
     * Мок Notificator.
     *
     * @param boolean $once Запускается один раз или нет.
     *
     * @return mixed
     */
    private function getMockNotificator(bool $once = false)
    {
        $mock = Mockery::mock(Notificator::class);

        if ($once) {
            $mock = $mock->shouldReceive('send')->once();
        } else {
            $mock = $mock->shouldReceive('send')->never();
        }

        return $mock->getMock();
    }

    /**
     * Мок ErrorDbOperatorInterface.
     *
     * @param boolean $hasOnce     Has вызывается один раз или нет.
     * @param boolean $saveOnce    Save вызывается один раз или нет.
     * @param mixed   $returnValue Возвращаемое значение.
     * @return mixed
     */
    private function getMockErrorDbOperatorInterface(bool $hasOnce = false, bool $saveOnce = false, $returnValue = true)
    {
        $mock = \Mockery::mock(ErrorDbOperatorInterface::class);
        if ($hasOnce) {
            $mock = $mock->shouldReceive('has')->times(1)->andReturn($returnValue);
        } else {
            $mock = $mock->shouldReceive('has')->never();
        }

        if ($saveOnce) {
            $mock = $mock->shouldReceive('save')->once();
        } else {
            $mock = $mock->shouldReceive('save')->never();
        }

        return $mock->getMock();
    }


    /**
     * Мок TelegramConvertorNotification.
     *
     * @return mixed
     */
    private function getMockTelegramConvertorNotification()
    {
        $mock = Mockery::namedMock(
            'Prokl\CustomFrameworkExtensionsBundle\Services\Notifier\Notification\TelegramConvertorNotification',
            'Prokl\FrameworkExtensionBundle\Tests\Stubs\TelegramConvertorNotificationStub'
        )->shouldReceive('content')->andReturn(Mockery::self());

        $mock->shouldReceive('importance')->andReturn(Mockery::self());

        return $mock->getMock();
    }

    /**
     * Мок Recipient.
     *
     * @return mixed
     */
    private function getMockRecipient()
    {
        return Mockery::mock(
            'overload:Symfony\Component\Notifier\Recipient\Recipient'
        );
    }

    /**
     * Мок WP_Error.
     *
     * @return mixed
     */
    private function getMockWpError()
    {
        $mockWpError = Mockery::mock('WP_Error')
            ->shouldReceive('get_error_data')
            ->once()
            ->andReturn([
                'error' => [
                    'message' => 'test message'
                ]
            ]);

        $mockWpError = $mockWpError->getMock();

        return $mockWpError;
    }
}
