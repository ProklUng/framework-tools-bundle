<?php

namespace Prokl\FrameworkExtensionBundle\Services\Wordpress;

use JsonSerializable;

/**
 * Class Context
 * @package Prokl\FrameworkExtensionBundle\Services\Wordpress
 *
 * @since 10.06.2021
 * @see https://github.com/inpsyde/wp-context
 */
class Context implements JsonSerializable
{
    public const AJAX = 'ajax';
    public const BACKOFFICE = 'backoffice';
    public const CLI = 'wpcli';
    public const CORE = 'core';
    public const CRON = 'cron';
    public const FRONTOFFICE = 'frontoffice';
    public const INSTALLING = 'installing';
    public const LOGIN = 'login';
    public const REST = 'rest';
    public const XML_RPC = 'xml-rpc';

    private const ALL = [
        self::AJAX,
        self::BACKOFFICE,
        self::CLI,
        self::CORE,
        self::CRON,
        self::FRONTOFFICE,
        self::INSTALLING,
        self::LOGIN,
        self::REST,
        self::XML_RPC,
    ];

    /**
     * @var array $data
     */
    private $data;

    /**
     * @var array<string, callable>
     */
    private $actionCallbacks = [];

    /**
     * @return self
     */
    final public static function new(): self
    {
        return new self(array_fill_keys(self::ALL, false));
    }

    /**
     * @return self
     */
    final public static function determine(): self
    {
        $installing = defined('WP_INSTALLING') && WP_INSTALLING;
        $xmlRpc = defined('XMLRPC_REQUEST') && XMLRPC_REQUEST;
        $isCore = defined('ABSPATH');
        $isCli = defined('WP_CLI');
        $notInstalling = $isCore && !$installing;
        $isAjax = $notInstalling ? wp_doing_ajax() : false;
        $isAdmin = $notInstalling ? (is_admin() && !$isAjax) : false;
        $isCron = $notInstalling ? wp_doing_cron() : false;

        $undetermined = $notInstalling && !$isAdmin && !$isCron && !$isCli && !$xmlRpc && !$isAjax;

        $isRest = $undetermined ? static::isRestRequest() : false;
        $isLogin = ($undetermined && !$isRest) ? static::isLoginRequest() : false;

        // When nothing else matches, we assume it is a front-office request.
        $isFront = $undetermined && !$isRest && !$isLogin;

        /*
         * Note that when core is installing **only** `INSTALLING` will be true, not even `CORE`.
         * This is done to do as less as possible during installation, when most of WP does not act
         * as expected.
         */

        $instance = new self(
            [
                self::CORE => ($isCore || $xmlRpc) && !$installing,
                self::FRONTOFFICE => $isFront,
                self::BACKOFFICE => $isAdmin,
                self::LOGIN => $isLogin,
                self::AJAX => $isAjax,
                self::REST => $isRest,
                self::CRON => $isCron,
                self::CLI => $isCli,
                self::XML_RPC => $xmlRpc && !$installing,
                self::INSTALLING => $installing,
            ]
        );

        $instance->addActionHooks();

        return $instance;
    }

    /**
     * @param array $data ????????????.
     */
    private function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @param string $context ????????????????.
     *
     * @return self
     */
    final public function force(string $context): self
    {
        if (!in_array($context, self::ALL, true)) {
            throw new \LogicException("'{$context}' is not a valid context.");
        }

        $this->removeActionHooks();

        $data = array_fill_keys(self::ALL, false);
        $data[$context] = true;
        if ($context !== self::INSTALLING && $context !== self::CORE && $context !== self::CLI) {
            $data[self::CORE] = true;
        }

        $this->data = $data;

        return $this;
    }

    /**
     * @return self
     */
    final public function withCli(): self
    {
        $this->data[self::CLI] = true;

        return $this;
    }

    /**
     * @param string $context     ????????????????.
     * @param string ...$contexts ?????? ??????????????????.
     *
     * @return boolean
     */
    final public function is(string $context, string ...$contexts): bool
    {
        array_unshift($contexts, $context);

        foreach ($contexts as $context) {
            if ($this->data[$context] ?? null) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return boolean
     */
    public function isCore(): bool
    {
        return $this->is(self::CORE);
    }

    /**
     * @return boolean
     */
    public function isFrontoffice(): bool
    {
        return $this->is(self::FRONTOFFICE);
    }

    /**
     * @return boolean
     */
    public function isBackoffice(): bool
    {
        return $this->is(self::BACKOFFICE);
    }

    /**
     * @return boolean
     */
    public function isAjax(): bool
    {
        return $this->is(self::AJAX);
    }

    /**
     * @return boolean
     */
    public function isLogin(): bool
    {
        return $this->is(self::LOGIN);
    }

    /**
     * @return boolean
     */
    public function isRest(): bool
    {
        return $this->is(self::REST);
    }

    /**
     * @return boolean
     */
    public function isCron(): bool
    {
        return $this->is(self::CRON);
    }

    /**
     * @return boolean
     */
    public function isWpCli(): bool
    {
        return $this->is(self::CLI);
    }

    /**
     * @return boolean
     */
    public function isXmlRpc(): bool
    {
        return $this->is(self::XML_RPC);
    }

    /**
     * @return boolean
     */
    public function isInstalling(): bool
    {
        return $this->is(self::INSTALLING);
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->data;
    }

    /**
     * When context is determined very early we do our best to understand some context like
     * login, rest and front-office even if WordPress normally would require a later hook.
     * When that later hook happen, we change what we have determined, leveraging the more
     * "core-compliant" approach.
     *
     * @return void
     */
    private function addActionHooks(): void
    {
        $this->actionCallbacks = [
            'login_init' => function (): void {
                $this->resetAndForce(self::LOGIN);
            },
            'rest_api_init' => function (): void {
                $this->resetAndForce(self::REST);
            },
            'template_redirect' => function (): void {
                $this->resetAndForce(self::FRONTOFFICE);
            },
            'current_screen' => function (\WP_Screen $screen): void {
                $screen->in_admin() && $this->resetAndForce(self::BACKOFFICE);
            },
        ];

        foreach ($this->actionCallbacks as $action => $callback) {
            add_action($action, $callback, PHP_INT_MIN);
        }
    }

    /**
     * When "force" is called on an instance created via `determine()` we need to remove added hooks
     * or what we are forcing might be overridden.
     *
     * @return void
     */
    private function removeActionHooks(): void
    {
        foreach ($this->actionCallbacks as $action => $callback) {
            remove_action($action, $callback, PHP_INT_MIN);
        }
        $this->actionCallbacks = [];
    }

    /**
     * @param string $context
     *
     * @return void
     */
    private function resetAndForce(string $context): void
    {
        $cli = $this->data[self::CLI];
        $this->data = array_fill_keys(self::ALL, false);
        $this->data[self::CORE] = true;
        $this->data[self::CLI] = $cli;
        $this->data[$context] = true;

        $this->removeActionHooks();
    }

    /**
     * @return boolean
     */
    private static function isRestRequest(): bool
    {
        if (defined('REST_REQUEST') && REST_REQUEST) {
            return true;
        }

        if (!get_option('permalink_structure')) {
            return !empty($_GET['rest_route']); // phpcs:ignore
        }

        /*
         * This is needed because, if called early, global $wp_rewrite is not defined but required
         * by get_rest_url(). WP will reuse what we set here, or in worst case will replace, but no
         * consequences for us in any case.
         */
        if (empty($GLOBALS['wp_rewrite'])) {
            $GLOBALS['wp_rewrite'] = new \WP_Rewrite();
        }

        $currentPath = trim((string)parse_url((string)add_query_arg([]), PHP_URL_PATH), '/') . '/';
        $restPath = trim((string)parse_url((string)get_rest_url(), PHP_URL_PATH), '/') . '/';

        return strpos($currentPath, $restPath) === 0;
    }

    /**
     * @return boolean
     */
    private static function isLoginRequest(): bool
    {
        if (!empty($_REQUEST['interim-login'])) { // phpcs:ignore
            return true;
        }

        $pageNow = (string)($GLOBALS['pagenow'] ?? '');
        if ($pageNow && (basename($pageNow) === 'wp-login.php')) {
            return true;
        }

        $currentPath = (string)parse_url(add_query_arg([]), PHP_URL_PATH);
        $loginPath = (string)parse_url(wp_login_url(), PHP_URL_PATH);

        return rtrim($currentPath, '/') === rtrim($loginPath, '/');
    }
}