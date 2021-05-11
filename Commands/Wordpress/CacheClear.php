<?php

namespace Prokl\FrameworkExtensionBundle\Commands\Wordpress;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class CacheClear
 * Очистка кэша.
 * @package Prokl\FrameworkExtensionBundle\Commands\Wordpress
 *
 * @since 13.12.2020
 */
class CacheClear extends Command
{
    /**
     * @const string ARG_CACHE_TYPE Команда.
     */
    public const ARG_CACHE_TYPE = 'cache-type';

    /**
     * @var Filesystem $filesystem Файловая система.
     */
    private $filesystem;

    /**
     * @var string $baseCacheDir Базовая директория кэша.
     */
    private $baseCacheDir;

    /**
     * CacheClear constructor.
     *
     * @param Filesystem $filesystem   Файловая система.
     * @param string     $baseCacheDir Базовая директория кэша.
     * @param string     $name         Тип кэша к очистке.
     */
    public function __construct(
        Filesystem $filesystem,
        string $baseCacheDir,
        string $name = self::ARG_CACHE_TYPE
    ) {
        parent::__construct($name);

        $this->filesystem = $filesystem;

        $this->baseCacheDir = $baseCacheDir;
    }

    /**
     * Конфигурация.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('cache:clear')
            ->setDescription('Clear cache')
            ->addArgument(
                self::ARG_CACHE_TYPE,
                InputArgument::OPTIONAL,
                'Cache type [all, symfony, comet, library]',
                'all'
            );
    }

    /**
     * Исполнение команды.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return integer
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $cacheType = $input->getArgument(self::ARG_CACHE_TYPE);

        // Весь кэш.
        if ($cacheType === 'all') {
            if (!$this->rmDir($this->baseCacheDir, $output)) {
                return 1;
            }

            $this->filesystem->mkdir(
                $this->baseCacheDir
            );

            return 0;
        }

        // Comet cache.
        if ($cacheType === 'comet') {
            if (!$this->rmDir($this->baseCacheDir . 'comet-cache', $output)) {
                return 1;
            }

            return 0;
        }

        // Symfony cache.
        if ($cacheType === 'symfony') {
            if (!$this->rmDir($this->baseCacheDir . 'symfony', $output)) {
                return 1;
            }

            return 0;
        }

        // Symfony cache.
        if ($cacheType === 'library') {
            if (!$this->rmDir($this->baseCacheDir . 'symfony/html-library', $output)) {
                return 1;
            }

            return 0;
        }

        $output->writeln(
            sprintf(
                'Invalid option %s. Available: all, symfony, comet, library',
                $cacheType
            ),
        );

        return 0;
    }

    /**
     * Рекурсивно удалить папки и файлы в них.
     *
     * @param string          $path   Путь.
     * @param OutputInterface $output Вывод в консоль.
     *
     * @return boolean
     */
    private function rmDir (
        string $path,
        OutputInterface $output
    ) : bool {
        if (!$this->filesystem->exists($path)) {
            $output->writeln(
                sprintf(
                    'Directory %s not exist.',
                    $this->baseCacheDir
                ),
            );

            return false;
        }

        try {
            $this->filesystem->remove($path);
        } catch (IOException $e) {
            $output->writeln(
                sprintf(
                    'Clearing directory %s failed.',
                    $path
                ),
            );

            $output->writeln($e->getMessage());

            return false;
        }

          $output->writeln(
            sprintf(
                'Clearing directory %s done.',
                $path
            ),
        );

        return true;
    }
}
