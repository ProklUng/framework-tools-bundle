<?php

namespace Cases;

use Brain\Monkey\Expectation\Exception\ExpectationArgsRequired;
use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Prokl\FrameworkExtensionBundle\Services\Wordpress\Context;

/**
 * Class ContextTest
 * @package Cases
 */
class ContextTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var string $currentPath
     */
    private $currentPath = '/';

    /**
     * @inheritDoc
     * @throws ExpectationArgsRequired
     */
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        Monkey\Functions\expect('add_query_arg')
            ->with([])
            ->andReturnUsing(function (): string {
                return $this->currentPath;
            });
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $this->currentPath = '/';
        unset($GLOBALS['pagenow']);
        Monkey\tearDown();
        parent::tearDown();
    }

    /**
     * @return void
     */
    public function testNotCore(): void
    {
        $context = Context::determine();

        static::assertFalse($context->isCore());
        static::assertFalse($context->isLogin());
        static::assertFalse($context->isRest());
        static::assertFalse($context->isCron());
        static::assertFalse($context->isFrontoffice());
        static::assertFalse($context->isBackoffice());
        static::assertFalse($context->isAjax());
        static::assertFalse($context->isWpCli());
        static::assertFalse($context->is(Context::CORE));
    }

    /**
     * @return void
     * @throws ExpectationArgsRequired
     */
    public function testIsLogin(): void
    {
        define('ABSPATH', __DIR__);
        Monkey\Functions\when('wp_doing_ajax')->justReturn(false);
        Monkey\Functions\when('is_admin')->justReturn(false);
        Monkey\Functions\when('wp_doing_cron')->justReturn(false);
        $this->mockIsRestRequest(false);
        $this->mockIsLoginRequest(true);

        $context = Context::determine();

        static::assertTrue($context->isCore());
        static::assertTrue($context->isLogin());
        static::assertFalse($context->isRest());
        static::assertFalse($context->isCron());
        static::assertFalse($context->isFrontoffice());
        static::assertFalse($context->isBackoffice());
        static::assertFalse($context->isAjax());
        static::assertFalse($context->isWpCli());

        static::assertTrue($context->is(Context::LOGIN));
        static::assertTrue($context->is(Context::LOGIN, Context::REST));
        static::assertFalse($context->is(Context::FRONTOFFICE, Context::REST));
        static::assertTrue($context->is(Context::FRONTOFFICE, Context::REST, Context::CORE));
    }

    /**
     * @return void
     * @throws ExpectationArgsRequired
     * @throws Monkey\Expectation\Exception\NotAllowedMethod
     */
    public function testIsLoginLate(): void
    {
        define('ABSPATH', __DIR__);
        Monkey\Functions\when('wp_doing_ajax')->justReturn(false);
        Monkey\Functions\when('is_admin')->justReturn(false);
        Monkey\Functions\when('wp_doing_cron')->justReturn(false);
        $this->mockIsRestRequest(false);
        $this->mockIsLoginRequest(false);

        $onLoginInit = null;
        Monkey\Actions\expectAdded('login_init')
            ->whenHappen(static function (callable $callback) use (&$onLoginInit) {
                $onLoginInit = $callback;
            });

        $context = Context::determine();

        static::assertTrue($context->isCore());
        static::assertFalse($context->isLogin());
        /** @var callable $onLoginInit */
        $onLoginInit();
        static::assertTrue($context->isLogin());
    }

    /**
     * @return void
     * @throws ExpectationArgsRequired
     */
    public function testIsRest(): void
    {
        define('ABSPATH', __DIR__);
        Monkey\Functions\when('wp_doing_ajax')->justReturn(false);
        Monkey\Functions\when('is_admin')->justReturn(false);
        Monkey\Functions\when('wp_doing_cron')->justReturn(false);
        $this->mockIsRestRequest(true);
        $this->mockIsLoginRequest(false);

        $context = Context::determine();

        static::assertTrue($context->isCore());
        static::assertFalse($context->isLogin());
        static::assertTrue($context->isRest());
        static::assertFalse($context->isCron());
        static::assertFalse($context->isFrontoffice());
        static::assertFalse($context->isBackoffice());
        static::assertFalse($context->isAjax());
        static::assertFalse($context->isWpCli());

        static::assertTrue($context->is(Context::REST));
        static::assertTrue($context->is(Context::REST, Context::LOGIN));
        static::assertFalse($context->is(Context::FRONTOFFICE, Context::LOGIN));
        static::assertTrue($context->is(Context::FRONTOFFICE, Context::LOGIN, Context::CORE));
    }

    /**
     * @return void
     * @throws ExpectationArgsRequired
     * @throws Monkey\Expectation\Exception\NotAllowedMethod
     */
    public function testIsRestLate(): void
    {
        define('ABSPATH', __DIR__);
        Monkey\Functions\when('wp_doing_ajax')->justReturn(false);
        Monkey\Functions\when('is_admin')->justReturn(false);
        Monkey\Functions\when('wp_doing_cron')->justReturn(false);
        $this->mockIsRestRequest(false);
        $this->mockIsLoginRequest(false);

        $onRestInit = null;
        Monkey\Actions\expectAdded('rest_api_init')
            ->whenHappen(static function (callable $callback) use (&$onRestInit) {
                $onRestInit = $callback;
            });

        $context = Context::determine();

        static::assertTrue($context->isCore());
        static::assertFalse($context->isRest());
        /** @var callable $onRestInit */
        $onRestInit();
        static::assertTrue($context->isRest());
    }

    /**
     * @return void
     * @throws ExpectationArgsRequired
     */
    public function testIsCron(): void
    {
        define('ABSPATH', __DIR__);
        Monkey\Functions\when('wp_doing_ajax')->justReturn(false);
        Monkey\Functions\when('is_admin')->justReturn(false);
        Monkey\Functions\when('wp_doing_cron')->justReturn(true);
        $this->mockIsRestRequest(false);
        $this->mockIsLoginRequest(false);

        $context = Context::determine();

        static::assertTrue($context->isCore());
        static::assertFalse($context->isLogin());
        static::assertFalse($context->isRest());
        static::assertTrue($context->isCron());
        static::assertFalse($context->isFrontoffice());
        static::assertFalse($context->isBackoffice());
        static::assertFalse($context->isAjax());
        static::assertFalse($context->isWpCli());

        static::assertTrue($context->is(Context::CRON));
        static::assertTrue($context->is(Context::LOGIN, Context::CRON));
        static::assertFalse($context->is(Context::FRONTOFFICE, Context::LOGIN));
        static::assertTrue($context->is(Context::FRONTOFFICE, Context::LOGIN, Context::CORE));
    }

    /**
     * @return void
     * @throws ExpectationArgsRequired
     */
    public function testIsFrontoffice(): void
    {
        define('ABSPATH', __DIR__);
        Monkey\Functions\when('wp_doing_ajax')->justReturn(false);
        Monkey\Functions\when('is_admin')->justReturn(false);
        Monkey\Functions\when('wp_doing_cron')->justReturn(false);
        $this->mockIsRestRequest(false);
        $this->mockIsLoginRequest(false);

        $context = Context::determine();

        static::assertTrue($context->isCore());
        static::assertFalse($context->isLogin());
        static::assertFalse($context->isRest());
        static::assertFalse($context->isCron());
        static::assertTrue($context->isFrontoffice());
        static::assertFalse($context->isBackoffice());
        static::assertFalse($context->isAjax());
        static::assertFalse($context->isWpCli());

        static::assertTrue($context->is(Context::FRONTOFFICE));
        static::assertTrue($context->is(Context::LOGIN, Context::FRONTOFFICE));
        static::assertFalse($context->is(Context::CRON, Context::LOGIN));
        static::assertTrue($context->is(Context::CRON, Context::LOGIN, Context::CORE));
    }

    /**
     * @return void
     * @throws ExpectationArgsRequired
     */
    public function testIsBackoffice(): void
    {
        define('ABSPATH', __DIR__);
        Monkey\Functions\when('wp_doing_ajax')->justReturn(false);
        Monkey\Functions\when('is_admin')->justReturn(true);
        Monkey\Functions\when('wp_doing_cron')->justReturn(false);
        $this->mockIsRestRequest(false);
        $this->mockIsLoginRequest(false);

        $context = Context::determine();

        static::assertTrue($context->isCore());
        static::assertFalse($context->isLogin());
        static::assertFalse($context->isRest());
        static::assertFalse($context->isCron());
        static::assertFalse($context->isFrontoffice());
        static::assertTrue($context->isBackoffice());
        static::assertFalse($context->isAjax());
        static::assertFalse($context->isWpCli());

        static::assertTrue($context->is(Context::BACKOFFICE));
        static::assertTrue($context->is(Context::LOGIN, Context::BACKOFFICE));
        static::assertFalse($context->is(Context::CRON, Context::LOGIN));
        static::assertTrue($context->is(Context::CRON, Context::LOGIN, Context::CORE));
    }

    /**
     * @return void
     * @throws ExpectationArgsRequired
     */
    public function testIsAjax(): void
    {
        define('ABSPATH', __DIR__);
        Monkey\Functions\when('wp_doing_ajax')->justReturn(true);
        Monkey\Functions\when('is_admin')->justReturn(true);
        Monkey\Functions\when('wp_doing_cron')->justReturn(false);
        $this->mockIsRestRequest(false);
        $this->mockIsLoginRequest(false);

        $context = Context::determine();

        static::assertTrue($context->isCore());
        static::assertFalse($context->isLogin());
        static::assertFalse($context->isRest());
        static::assertFalse($context->isCron());
        static::assertFalse($context->isFrontoffice());
        static::assertFalse($context->isBackoffice());
        static::assertTrue($context->isAjax());
        static::assertFalse($context->isWpCli());

        static::assertTrue($context->is(Context::AJAX));
        static::assertTrue($context->is(Context::AJAX, Context::BACKOFFICE));
        static::assertFalse($context->is(Context::CRON, Context::BACKOFFICE));
        static::assertTrue($context->is(Context::CRON, Context::BACKOFFICE, Context::CORE));
    }

    /**
     * @return void
     * @throws ExpectationArgsRequired
     */
    public function testIsCli(): void
    {
        define('ABSPATH', __DIR__);
        define('WP_CLI', 2);

        Monkey\Functions\when('wp_doing_ajax')->justReturn(false);
        Monkey\Functions\when('is_admin')->justReturn(false);
        Monkey\Functions\when('wp_doing_cron')->justReturn(false);
        $this->mockIsRestRequest(false);
        $this->mockIsLoginRequest(false);

        $context = Context::determine();

        static::assertTrue($context->isCore());
        static::assertFalse($context->isLogin());
        static::assertFalse($context->isRest());
        static::assertFalse($context->isCron());
        static::assertFalse($context->isFrontoffice());
        static::assertFalse($context->isBackoffice());
        static::assertFalse($context->isAjax());
        static::assertTrue($context->isWpCli());

        static::assertTrue($context->is(Context::CLI));
        static::assertTrue($context->is(Context::FRONTOFFICE, Context::CLI));
        static::assertFalse($context->is(Context::FRONTOFFICE, Context::CRON));
        static::assertTrue($context->is(Context::CRON, Context::BACKOFFICE, Context::CORE));
    }

    /**
     * @return void
     * @throws ExpectationArgsRequired
     */
    public function testJsonSerialize(): void
    {
        define('ABSPATH', __DIR__);
        Monkey\Functions\when('wp_doing_ajax')->justReturn(false);
        Monkey\Functions\when('is_admin')->justReturn(false);
        Monkey\Functions\when('wp_doing_cron')->justReturn(false);
        $this->mockIsRestRequest(false);
        $this->mockIsLoginRequest(true);

        $context = Context::determine();
        $decoded = (array)json_decode((string)json_encode($context), true);

        static::assertTrue($decoded[Context::CORE]);
        static::assertTrue($decoded[Context::LOGIN]);
        static::assertFalse($decoded[Context::REST]);
        static::assertFalse($decoded[Context::CRON]);
        static::assertFalse($decoded[Context::FRONTOFFICE]);
        static::assertFalse($decoded[Context::BACKOFFICE]);
        static::assertFalse($decoded[Context::AJAX]);
        static::assertFalse($decoded[Context::CLI]);
    }

    /**
     * @param boolean $is
     *
     * @return void
     * @throws ExpectationArgsRequired
     */
    private function mockIsRestRequest(bool $is): void
    {
        Monkey\Functions\expect('get_option')->with('permalink_structure')->andReturn(true);
        $GLOBALS['wp_rewrite'] = \Mockery::mock('WP_Rewrite');
        Monkey\Functions\when('get_rest_url')->justReturn('https://example.com/wp-json');
        $is && $this->currentPath = '/wp-json/foo';
    }

    /**
     * @param boolean $is
     *
     * @return void
     * @throws ExpectationArgsRequired
     */
    private function mockIsLoginRequest(bool $is): void
    {
        $is && $this->currentPath = '/wp-login.php';
        Monkey\Functions\when('wp_login_url')->justReturn('https://example.com/wp-login.php');
        Monkey\Functions\when('home_url')
            ->alias(static function (string $path = ''): string {
                return 'https://example.com/'.ltrim($path, '/');
            });
    }
}
