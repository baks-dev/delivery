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

namespace BaksDev\Delivery\Repository\AllDelivery;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Delivery\Entity\Cover\DeliveryCover;
use BaksDev\Delivery\Entity\Delivery;
use BaksDev\Delivery\Entity\Event\DeliveryEvent;
use BaksDev\Delivery\Entity\Price\DeliveryPrice;
use BaksDev\Delivery\Entity\Trans\DeliveryTrans;
use BaksDev\Reference\Region\Entity\Event\RegionEvent;
use BaksDev\Reference\Region\Entity\Region;
use BaksDev\Reference\Region\Entity\Trans\RegionTrans;
use BaksDev\Users\Profile\TypeProfile\Entity\Event\TypeProfileEvent;
use BaksDev\Users\Profile\TypeProfile\Entity\Trans\TypeProfileTrans;
use BaksDev\Users\Profile\TypeProfile\Entity\TypeProfile;

final class AllDeliveryRepository implements AllDeliveryInterface
{
    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
        private readonly PaginatorInterface $paginator,
    ) {}


    public function fetchAllDeliveryAssociative(SearchDTO $search): PaginatorInterface
    {
        $qb = $this
            ->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $qb
            ->select('delivery.id')
            ->addSelect('delivery.event')
            ->from(Delivery::class, 'delivery');

        $qb
            ->addSelect('event.sort AS delivery_sort')
            ->addSelect('event.active AS delivery_active')
            ->join(
                'delivery',
                DeliveryEvent::class,
                'event',
                'event.id = delivery.event'
            );

        $qb
            ->addSelect('trans.name AS delivery_name')
            ->addSelect('trans.description AS delivery_description')
            ->leftJoin(
                'event',
                DeliveryTrans::class,
                'trans',
                'trans.event = event.id AND trans.local = :local'
            );

        /** Стоимость доставки */
        $qb
            ->addSelect('price.price AS delivery_price')
            ->addSelect('price.currency AS delivery_currency')
            ->addSelect('price.excess AS delivery_excess')
            ->leftJoin(
                'event',
                DeliveryPrice::class,
                'price',
                'price.event = event.id'
            );


        /** Обложка */
        $qb
            ->addSelect('cover.ext AS delivery_cover_ext')
            ->addSelect('cover.cdn AS delivery_cover_cdn')
            ->addSelect(
                "
			CASE
			   WHEN cover.name IS NOT NULL THEN
					CONCAT ( '/upload/".$qb->table(DeliveryCover::class)."' , '/', cover.name)
			   ELSE NULL
			END AS delivery_cover_name
		"
            );

        $qb->leftJoin(
            'event',
            DeliveryCover::class,
            'cover',
            'cover.event = event.id'
        );

        /** Ограничение профилем */

        $qb->leftJoin(
            'event',
            TypeProfile::class,
            'type_profile',
            'event.type IS NOT NULL AND type_profile.id = event.type'
        );

        $qb->leftJoin(
            'type_profile',
            TypeProfileEvent::class,
            'type_profile_event',
            'type_profile_event.id = type_profile.event'
        );

        $qb->addSelect('type_profile_trans.name AS type_profile_name');

        $qb->leftJoin(
            'type_profile_event',
            TypeProfileTrans::class,
            'type_profile_trans',
            'type_profile_trans.event = type_profile_event.id AND type_profile_trans.local = :local'
        );

        /** Ограничение регионом */
        $qb->leftJoin(
            'event',
            Region::class,
            'region',
            'event.region IS NOT NULL AND region.id = event.region'
        );

        $qb->leftJoin(
            'region',
            RegionEvent::class,
            'region_event',
            'region_event.id = region.event'
        );

        $qb->addSelect('region_trans.name AS region_name');

        $qb->leftJoin(
            'region_event',
            RegionTrans::class,
            'region_trans',
            'region_trans.event = region_event.id AND region_trans.local = :local'
        );

        /* Поиск */
        if($search->getQuery())
        {
            $qb
                ->createSearchQueryBuilder($search)
                ->addSearchLike('trans.name')
                ->addSearchLike('trans.description')
                ->addSearchLike('type_profile_trans.name')
                ->addSearchLike('region_trans.name');
        }


        $qb->addOrderBy('event.sort');

        return $this->paginator->fetchAllAssociative($qb);

    }

}
