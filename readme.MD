# Различные дополнительные инструменты для кастомных вариантов Symfony

## Установка

1) composer.json:

```json
  "repositories": [
    {
      "type": "git",
      "url": "https://github.com/proklung/framework-tools-bundle"
    }
  ]
```

2) `composer require proklung/framework-tools-bundle`

## Детали

### Delayed event dispatcher

[Основа](https://github.com/olvlvl/delayed-event-dispatcher/blob/master/README.md)

Из особо интересного: "Flushing delayed events with a custom flusher".

Если запускается из под Битрикс, то подвязывается слушатель на событие `OnEpilog`.

Если запускается из под Wordpress, то подвязывается слушатель на хук `shutdown`.

### Command runner

Форк [пакета](https://github.com/Fichtme/symfony-command-runner). Запуск команд пакетом в разных процессах.

#### Пример использования

```php
(new CommandRunner([
            new Process("my:command -q"),
            new Process("my:command2 -q"),
            new Process("my:command3 -q").
            new Process("my:command4 -q"),
            new Process("my:command5 -q"),
            new Process("my:command6 -q --env=$env"),
        ]))
            ->continueOnError(true)
            ->setIO($this->io)
            ->setLimit(3)
            ->run();
            
```

Как-то так:

```php
class ExampleRunner extends Command
{
    /** @var SymfonyStyle */
    protected $io;

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('runner:example')
             ->setDescription('runner example');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->io->writeln('Running runner example');

        sleep(5); # Sleep so user can abort update

        (new CommandRunner([
            new Process(['cache:clear', 'cache:clear --cache-type menu']),
        ]))
            ->continueOnError(true)
            ->setIO($this->io)
            ->setLimit(3)
            ->run();

        return 0;
    }
}
```

### Lockable консольные команды

Команды, запускающиеся одновременно только в одном экземпляре.

```php
use Prokl\FrameworkExtensionBundle\Services\Command\Lockable\AbstractLockableCommand;

class SomeCommand extends AbstractLockableCommand
{
   protected function configure()
   {
       $this->setName('lock:command')
            ->setDescription('Lock command')
    
       ;
    
       parent::configure();
   }

   protected function execute(InputInterface $input, OutputInterface $output) : int
   {
        $output->writeln('Start');
        sleep(100);
        $output->writeln('End');

        return 0;
   }
}
```

Можно отнаследовать метод `getLockTtl()`, чтобы переопределить время блокировки (по умолчанию - 60 секунд).

Зависимости такого сорта команд подцепляются через сеттеры посредством механизма autowiring.

### Консольные команды

#### Очистка кэша (Битрикс и Wordpress)

```bash
php bin/console cache:clear 
```

### Простой битриксовый PSR-16 кэш

```yaml
  bitrix.simple.cacher.configured:
    class: Prokl\FrameworkExtensionBundle\Services\Bitrix\Psr16Cache\BitrixCacher
    arguments: ['@Bitrix\Main\Data\Cache']
    calls:
      - setBaseDir: ['/guzzle_request']
      - setTtl: [3600]
```

Методы:

- **get**
- **getMultiple**
- **has**
- **delete**
- **deleteMultiple**
- **clear**
- **setMultiple**
- **set** - `set($key, $value, $ttl = null)`
- **getOrSet** - `getOrSet(string $key, callable $callable, $ttl = null)`

### Кастомные валидаторы для Symfony Validator

- **Email** - при помощи `Egulias\EmailValidator`
- **Phone** - при помощи `giggsey/libphonenumber-for-php`

### Вспомогательное для контроллеров

- `BinaryFileResponseTrait` - метод `returnFile(string $file)` отдаст в браузер BinaryFileResponse файл $file,
автоматом определив contentType.


### Отправка информации о фатальных ошибках через Symfony Notifier для Wordpress

- Должна быть определена переменная среды `ADMIN_EMAIL`
- Должен быть установлен пакет `symfony/notifier` и [бандл](https://github.com/ProklUng/core.framework.extension.bundle).
Если нет, то соответствующие сервисы удаляются из контейнера при компиляции.
- В корневом проекте должен быть класс-сервис, реализующий `Prokl\FrameworkExtensionBundle\Services\Wordpress\ErrorHandler\Contract\ErrorDbOperatorInterface`
 для работы с записями в БД (в моем случае сохраняется md5 от сериализованного исключения).
 
 - `save` - сохранить сведения об ошибке в базу (или куда там еще).
 - `has` -  есть ли запись об этой ошибке в базе или нет.
 - `clearTable` - очистить таблицу с данными об ошибках.
 
- По умолчанию сообщения засылаются в каналы, отмеченные как `urgent`.
