<?php

namespace Prokl\FrameworkExtensionBundle\Tests\Cases\Notify;

use Mockery;
use Prokl\FrameworkExtensionBundle\Services\Wordpress\Notifier\Notificator;
use Prokl\TestingTools\Base\BaseTestCase;
use Prokl\CustomFrameworkExtensionsBundle\Services\Notifier\Notification\TelegramConvertorNotification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Exception\TransportException;
use Symfony\Component\Notifier\Recipient\Recipient;

/**
 * Class NotificatorTest
 * @package Prokl\FrameworkExtensionBundle\Tests\Cases\Notify
 *
 * @since 06.08.2021
 */
class NotificatorTest extends BaseTestCase
{
    /**
     * @var Notificator $obTestObject
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
     * Send. Без ошибок.
     *
     * @return void
     */
    public function testSendNoError() : void
    {
        $this->obTestObject = new Notificator($this->getMockNotifierInterface(), 'foo@foo.ru');
        $this->obTestObject->send('Test', 'Test', 'medium');

        // Проверка на то, что send NotifierInterface вызывается один раз.
        $this->assertTrue(true);
    }

    /**
     * Send. Ошибка транспорта.
     *
     * @return void
     */
    public function testSendTransportError() : void
    {
        $this->obTestObject = new Notificator($this->getMockNotifierInterfaceTransportError(), 'foo@foo.ru');

        $this->obTestObject->send('Test', 'Test', 'medium');

        // Проверка на то, что send NotifierInterface вызывается один раз.
        $this->assertTrue(true);
    }

    /**
     * Мок NotifierInterface, без ошибок.
     *
     * @return mixed
     */
    private function getMockNotifierInterface()
    {
        $mock = Mockery::mock(NotifierInterface::class)->shouldReceive('send')->once();

        return $mock->getMock();
    }

    /**
     * Мок NotifierInterface. Ошибка транспорта.
     *
     * @return mixed
     */
    private function getMockNotifierInterfaceTransportError()
    {
        $mock = Mockery::mock(NotifierInterface::class)->shouldReceive('send')
                                                        ->andThrowExceptions([Mockery::mock(TransportException::class)]);

        return $mock->getMock();
    }

    /**
     * Мок TelegramConvertorNotification.
     *
     * @return mixed
     */
    private function getMockTelegramConvertorNotification()
    {
        $mock = Mockery::mock(
            'overload:Prokl\CustomFrameworkExtensionsBundle\Services\Notifier\Notification\TelegramConvertorNotification'
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
}
