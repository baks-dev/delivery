# BaksDev Delivery

[![Version](https://img.shields.io/badge/version-7.2.14-blue)](https://github.com/baks-dev/delivery/releases)
![php 8.4+](https://img.shields.io/badge/php-min%208.4-red.svg)
[![packagist](https://img.shields.io/badge/packagist-green)](https://packagist.org/packages/baks-dev/delivery)

Модуль доставки заказов

## Установка

``` bash
composer require \
baks-dev/delivery \
baks-dev/delivery-transport
```

`

## Дополнительно`

Установка конфигурации и файловых ресурсов:

``` bash
php bin/console baks:assets:install
```

Изменения в схеме базы данных с помощью миграции

``` bash
php bin/console doctrine:migrations:diff

php bin/console doctrine:migrations:migrate
```

## Тестирование

``` bash
php bin/phpunit --group=delivery
```

## Журнал изменений ![Changelog](https://img.shields.io/badge/changelog-yellow)

О том, что изменилось за последнее время, обратитесь к [CHANGELOG](CHANGELOG.md) за дополнительной информацией.
