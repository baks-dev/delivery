# BaksDev Delivery

[![Version](https://img.shields.io/badge/version-7.0.28-blue)](https://github.com/baks-dev/delivery/releases)
![php 8.2+](https://img.shields.io/badge/php-min%208.1-red.svg)

Модуль доставки заказов

## Установка

``` bash
$ composer require baks-dev/delivery
```

## Дополнительно

Установка файловых ресурсов в публичную директорию (javascript, css, image ...):

``` bash
$ php bin/console baks:assets:install
```

Изменения в схеме базы данных с помощью миграции

``` bash
$ php bin/console doctrine:migrations:diff

$ php bin/console doctrine:migrations:migrate
```

Тесты

``` bash
$ php bin/phpunit --group=delivery
```

## Журнал изменений ![Changelog](https://img.shields.io/badge/changelog-yellow)

О том, что изменилось за последнее время, обратитесь к [CHANGELOG](CHANGELOG.md) за дополнительной информацией.
