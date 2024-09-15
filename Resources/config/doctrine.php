<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use BaksDev\Delivery\BaksDevDeliveryBundle;
use BaksDev\Delivery\Type\Cover\DeliveryCoverType;
use BaksDev\Delivery\Type\Cover\DeliveryCoverUid;
use BaksDev\Delivery\Type\Event\DeliveryEventType;
use BaksDev\Delivery\Type\Event\DeliveryEventUid;
use BaksDev\Delivery\Type\Field\DeliveryFieldType;
use BaksDev\Delivery\Type\Field\DeliveryFieldUid;
use BaksDev\Delivery\Type\Id\DeliveryType;
use BaksDev\Delivery\Type\Id\DeliveryUid;
use Symfony\Config\DoctrineConfig;

return static function (ContainerConfigurator $container, DoctrineConfig $doctrine) {

    $doctrine->dbal()->type(DeliveryUid::TYPE)->class(DeliveryType::class);
    $doctrine->dbal()->type(DeliveryEventUid::TYPE)->class(DeliveryEventType::class);
    $doctrine->dbal()->type(DeliveryFieldUid::TYPE)->class(DeliveryFieldType::class);
    $doctrine->dbal()->type(DeliveryCoverUid::TYPE)->class(DeliveryCoverType::class);

    $emDefault = $doctrine->orm()->entityManager('default')->autoMapping(true);

    $emDefault->mapping('delivery')
        ->type('attribute')
        ->dir(BaksDevDeliveryBundle::PATH.'Entity')
        ->isBundle(false)
        ->prefix(BaksDevDeliveryBundle::NAMESPACE.'\\Entity')
        ->alias('delivery');
};
