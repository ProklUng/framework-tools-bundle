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