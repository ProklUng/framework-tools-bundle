<?php

namespace Prokl\FrameworkExtensionBundle\Services\Bitrix;

use Bitrix\Main\Application;
use Bitrix\Main\DB\ConnectionException;
use Exception;

/**
 * Class LoaderBitrix
 * @package Prokl\FrameworkExtensionBundle\Services\Bitrix
 *
 * @since 11.12.2020
 * @since 24.05.2021 Поиск DOCUMENT_ROOT, если его не задали снаружи.
 * @since 03.08.2021 Подтягивание переменных окружения в $_SERVER. Реинициализация битриксового
 * контекста.
 */
class LoaderBitrix
{
    /**
     * Bitrix is unavailable.
     */
    public const BITRIX_STATUS_UNAVAILABLE = 500;

    /**
     * Bitrix is available, but not have connection to DB.
     */
    public const BITRIX_STATUS_NO_DB_CONNECTION = 100;

    /**
     * Bitrix is available.
     */
    public const BITRIX_STATUS_COMPLETE = 0;

    /**
     * @var integer Status of Bitrix kernel. Value of constant `Application::BITRIX_STATUS_*`.
     */
    private $bitrixStatus = self::BITRIX_STATUS_UNAVAILABLE;

    /**
     * @var null|string
     */
    private $documentRoot = null;

    /**
     * @var string $prologPath Путь к прологу Битрикса.
     */
    private $prologPath = '/bitrix/modules/main/include/prolog_before.php';

    /**
     * @var array $defaultPaths Пути, по которым искать Битрикс.
     */
    private $defaultPaths = [
        '.',
        '../..',
        '../../..',
        '../../../..',
        '../../../../..',
        '../../../../../..',
        '../../../../../../..',
        'web',
        'common',
    ];

    /**
     * Initialize kernel of Bitrix.
     *
     * @return integer The status of readiness kernel.
     * @throws Exception Когда нашли Битрикс, но с ним что-то не так.
     */
    public function initializeBitrix() : int
    {
        // Если не задали DOCUMENT_ROOT, то попробую найти его сам.
        // Рискованный момент - поисковик считает рутом папку, в которой лежит Битрикс,
        // а это не всегда так.
        if ($this->documentRoot === null) {
            $this->documentRoot = $this->findBitrixCorePath();
        }

        if ($this->bitrixStatus === static::BITRIX_STATUS_COMPLETE) {
            return static::BITRIX_STATUS_COMPLETE;
        } elseif (!$this->checkBitrix()) {
            return static::BITRIX_STATUS_UNAVAILABLE;
        }

        define('BITRIX_CLI', true);
        define('NO_KEEP_STATISTIC', true);
        define('NOT_CHECK_PERMISSIONS', true);
        define('LANGUAGE_ID', 'pa');
        define('LOG_FILENAME', 'php://stderr');
        define('BX_NO_ACCELERATOR_RESET', true);
        define('STOP_STATISTICS', true);
        define('NO_AGENT_STATISTIC', 'Y');
        define('NO_AGENT_CHECK', true);
        defined('PUBLIC_AJAX_MODE') || define('PUBLIC_AJAX_MODE', true);

        try {
            /**
             * Declare global legacy variables
             *
             * Including kernel here makes them local by default but some modules depend on them in installation class
             */

            global
            /** @noinspection PhpUnusedLocalVariableInspection */
            $DB, $DBType, $DBHost, $DBLogin, $DBPassword,
            $DBName, $DBDebug, $DBDebugToFile, $APPLICATION, $USER, $DBSQLServerType;

            require_once $this->documentRoot . '/bitrix/modules/main/include/prolog_before.php';

            if (defined('B_PROLOG_INCLUDED') && B_PROLOG_INCLUDED === true) {
                $this->bitrixStatus = static::BITRIX_STATUS_COMPLETE;
            }

            if (in_array(\PHP_SAPI, ['cli', 'phpdbg', 'embed'], true) === false) {
                echo 'Warning: The console should be invoked via the CLI version of PHP, not the '
                    . \PHP_SAPI . ' SAPI' . \PHP_EOL;
            }

            $_SERVER = array_merge($_SERVER, $_ENV);

            // Реинициализация битриксового контекста с учетом подтянутого $_SERVER
            $httpApp = \Bitrix\Main\Application::getInstance();
            $context = $httpApp->getContext();

            $server = new \Bitrix\Main\Server($_SERVER);
            $request = new \Bitrix\Main\HttpRequest($server, $_GET ?? [], $_POST ?? [], $_FILES ?? [], $_COOKIE ?? []);

            $context->initialize($request, $context->getResponse(), $server);
            $httpApp->setContext($context);

            // Альтернативный способ вывода ошибок типа "DB query error.":
            $GLOBALS['DB']->debug = true;

            $app = Application::getInstance();
            $con = $app->getConnection();
            $DB->db_Conn = $con->getResource();
        } catch (ConnectionException $e) {
            $this->bitrixStatus = static::BITRIX_STATUS_NO_DB_CONNECTION;
        }

        return $this->bitrixStatus;
    }

    /**
     * Попытка найти путь к ядру Битрикса.
     *
     * @return string
     *
     * @throws Exception Когда директория с Битриксом существует, но в ней нет .settings.php.
     *
     * @since 24.05.2021
     */
    public function findBitrixCorePath(): string
    {
        foreach ($this->defaultPaths as $path) {
            $normalizedPath = $this->normalizePath($path);

            if (file_exists($normalizedPath)) {
                $pathBitrix = getcwd() . DIRECTORY_SEPARATOR . $path;
                if (!is_file($pathBitrix . '/bitrix/.settings.php')) {
                    throw new Exception(
                        'Path bitrix exist, but file bitrix/.settings.php not exist. Wrong!'
                    );
                }

                return $pathBitrix;
            }
        }

        throw new Exception('Wrong document root or bitrix is not found.');
    }

    /**
     * Checks readiness of Bitrix for kernel initialize.
     *
     * @return boolean
     */
    public function checkBitrix() : bool
    {
        if (!is_file($this->documentRoot . '/bitrix/.settings.php')) {
            return false;
        }

        return true;
    }

    /**
     * Gets Bitrix status.
     *
     * @return integer Value of constant `Application::BITRIX_STATUS_*`.
     */
    public function getBitrixStatus() : int
    {
        return $this->bitrixStatus;
    }

    /**
     * Checks that the Bitrix kernel is loaded.
     *
     * @return boolean
     */
    public function isBitrixLoaded() : bool
    {
        return $this->bitrixStatus === static::BITRIX_STATUS_COMPLETE;
    }

    /**
     * Sets path to the document root of site.
     *
     * @param string $dir Path to document root.
     *
     * @return void
     */
    public function setDocumentRoot(string $dir) : void
    {
        $_SERVER['DOCUMENT_ROOT'] = $this->documentRoot = $dir;
    }

    /**
     * Gets document root of site.
     *
     * @return null|string
     */
    public function getDocumentRoot() : ?string
    {
        return $this->documentRoot;
    }

    /**
     * Нормализовать путь.
     *
     * @param string $path Путь.
     *
     * @return string
     */
    private function normalizePath(string $path): string
    {
        return \realpath(
            \implode(
                \DIRECTORY_SEPARATOR,
                [
                    \getcwd(),
                    $path,
                    $this->prologPath
                ]
            )
        ) ?: '';
    }
}
