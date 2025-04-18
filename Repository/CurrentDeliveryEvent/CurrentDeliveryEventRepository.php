<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Delivery\Repository\CurrentDeliveryEvent;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Delivery\Entity\Delivery;
use BaksDev\Delivery\Entity\Event\DeliveryEvent;
use BaksDev\Delivery\Type\Event\DeliveryEventUid;
use BaksDev\Delivery\Type\Id\DeliveryUid;
use InvalidArgumentException;

final class CurrentDeliveryEventRepository implements CurrentDeliveryEventInterface
{
    private DeliveryUid|false $delivery = false;

    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
        private readonly ORMQueryBuilder $ORMQueryBuilder
    ) {}

    public function forDelivery(Delivery|DeliveryUid|string $delivery): self
    {
        if(empty($delivery))
        {
            $this->delivery = false;
            return $this;
        }

        if(is_string($delivery))
        {
            $delivery = new DeliveryUid($delivery);
        }

        if($delivery instanceof Delivery)
        {
            $delivery = $delivery->getId();
        }

        $this->delivery = $delivery;

        return $this;
    }

    /**
     * Метод возвращает активное событие доставки
     */
    public function get(): ?DeliveryEvent
    {
        if(false === ($this->delivery instanceof DeliveryUid))
        {
            throw new InvalidArgumentException('Invalid Argument Delivery');
        }

        $qb = $this->ORMQueryBuilder->createQueryBuilder(self::class);

        $qb
            ->from(Delivery::class, 'delivery')
            ->where('delivery.id = :delivery')
            ->setParameter(
                key: 'delivery',
                value: $this->delivery,
                type: DeliveryUid::TYPE
            );

        $qb
            ->select('event')
            ->join(
                DeliveryEvent::class,
                'event',
                'WITH',
                'event.id = delivery.event'
            );

        return $qb->getOneOrNullResult();
    }

    public function getId(): DeliveryEventUid|false
    {
        if(false === ($this->delivery instanceof DeliveryUid))
        {
            throw new InvalidArgumentException('Invalid Argument Delivery');
        }

        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->select('delivery.event AS value')
            ->from(Delivery::class, 'delivery')
            ->where('delivery.id = :delivery')
            ->setParameter(
                key: 'delivery',
                value: $this->delivery,
                type: DeliveryUid::TYPE
            );

        return $dbal->fetchHydrate(DeliveryEventUid::class);
    }
}
