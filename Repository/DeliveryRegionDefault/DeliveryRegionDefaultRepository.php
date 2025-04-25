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

namespace BaksDev\Delivery\Repository\DeliveryRegionDefault;

use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Delivery\Entity\Delivery;
use BaksDev\Delivery\Entity\Event\DeliveryEvent;
use BaksDev\Reference\Region\Type\Id\RegionUid;

final readonly class DeliveryRegionDefaultRepository implements DeliveryRegionDefaultInterface
{
    public function __construct(private ORMQueryBuilder $ORMQueryBuilder) {}

    public function getDefaultDeliveryRegion(): ?RegionUid
    {
        $orm = $this->ORMQueryBuilder->createQueryBuilder(self::class);

        $select = sprintf('new %s(delivery_event.region)', RegionUid::class);
        $orm->select($select);

        $orm->from(Delivery::class, 'delivery');

        $orm->join(
            DeliveryEvent::class,
            'delivery_event',
            'WITH',
            'delivery_event.id = delivery.event AND delivery_event.region IS NOT NULL'
        );

        $orm->orderBy('delivery_event.sort');

        $orm->setMaxResults(1);

        /* Кешируем результат ORM */
        return $orm->enableCache('delivery', '1 day')->getOneOrNullResult();

    }

}
