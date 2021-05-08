# Различные дополнительные инструменты для кастомных вариантов Symfony

Отличается от обычного (для этой конструкции) тем, что не надо вызывать boot вне контейнера и внутри
не используется "магический" доступ к контейнеру.

## Установка

1) `composer require proklung/framework-tools-bundle`

## Детали

### Delayed event dispatcher

[Основа](https://github.com/olvlvl/delayed-event-dispatcher/blob/master/README.md)

Из особо интересного: "Flushing delayed events with a custom flusher".

Если запускается из под Битрикс, то подвязывается слушатель на событие `OnEpilog`.