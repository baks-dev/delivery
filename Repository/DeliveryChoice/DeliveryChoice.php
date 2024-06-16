<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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

declare(strict_types=1);

namespace BaksDev\Delivery\Repository\DeliveryChoice;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Delivery\Entity\Delivery;
use BaksDev\Delivery\Entity\Event\DeliveryEvent;
use BaksDev\Delivery\Entity\Price\DeliveryPrice;
use BaksDev\Delivery\Entity\Trans\DeliveryTrans;
use BaksDev\Delivery\Type\Id\DeliveryUid;
use BaksDev\Users\Profile\TypeProfile\Type\Id\TypeProfileUid;
use Generator;

final class DeliveryChoice implements DeliveryChoiceInterface
{
    private bool $active = false;

    private ?TypeProfileUid $type = null;

    private DBALQueryBuilder $DBALQueryBuilder;

    public function __construct(DBALQueryBuilder $DBALQueryBuilder)
    {
        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }

    /** Фильтр "Только активные" */
    public function onlyActive(): self
    {
        $this->active = true;
        return $this;
    }

    /** Фильтр "Только доступные всем либо указанному типу профиля" */
    public function onlyProfileType(TypeProfileUid|string $type): self
    {
        if(is_string($type))
        {
            $type = new TypeProfileUid($type);
        }

        $this->type = $type;
        return $this;
    }


    /**
     * Метод возвращает идентификаторы способов доставки
     */
    public function findAll(): Generator
    {
        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal->from(Delivery::class, 'delivery');

        $dbal->leftJoin(
            'delivery',
            DeliveryTrans::class,
            'delivery_trans',
            'delivery_trans.event = delivery.event AND delivery_trans.local = :local'
        );

        $dbal->leftJoin(
            'delivery',
            DeliveryPrice::class,
            'price',
            'price.event = delivery.event'
        );


        $condition = 'event.id = delivery.event';

        if($this->active)
        {
            $condition .= ' AND event.active = true';
        }

        if($this->type)
        {
            $condition .= ' AND (event.type IS NULL OR event.type = :type)';
        }

        if($this->active || $this->type)
        {
            $dbal->join(
                'delivery',
                DeliveryEvent::class,
                'event',
                $condition
            );
        }

        /** Свойства конструктора объекта гидрации */

        $dbal->select('delivery.id AS value');
        $dbal->addSelect('delivery.event AS event');
        $dbal->addSelect('delivery_trans.name AS attr');
        $dbal->addSelect('delivery_trans.description AS option');
        $dbal->addSelect('price.price AS price');
        $dbal->addSelect('price.excess AS excess');
        $dbal->addSelect('price.currency AS currency');

        return $dbal
            ->enableCache('delivery', 86400)
            ->fetchAllHydrate(DeliveryUid::class);
    }
}
