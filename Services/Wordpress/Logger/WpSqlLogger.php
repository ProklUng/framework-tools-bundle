<?php

namespace Prokl\FrameworkExtensionBundle\Services\Wordpress\Logger;

use Psr\Log\LoggerInterface;
use wpdb;

/**
 * Class WpSqlLogger
 * @package Prokl\FrameworkExtensionBundle\Services\Wordpress\Logger
 */
class WpSqlLogger
{
    /**
     * @var wpdb $wpdb Инстанс wpdb.
     */
    private $wpdb;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * WpSqlLogger constructor.
     *
     * @param wpdb            $wpdb   Инстанс wpdb.
     * @param LoggerInterface $logger Логгер.
     */
    public function __construct(
        wpdb $wpdb,
        LoggerInterface $logger
    ) {
        $this->wpdb = $wpdb;
        $this->logger = $logger;
    }

    /**
     * Инициализация события.
     *
     * @return void
     */
    public function init() : void
    {
        add_filter('shutdown', [$this, 'log']);
    }

    /**
     * Движуха.
     *
     * @return void
     */
    public function log() : void
    {
        if (!$this->wpdb->queries) {
            return;
        }

        if (current_user_can('administrator')) {
            $result = [];
            foreach ($this->wpdb->queries as $q) {
                $result[] = $q[0] . " - ($q[1] s)" . "\n\n";
            }

            $this->logger->info(implode(' ', $result));
        }
    }
}
