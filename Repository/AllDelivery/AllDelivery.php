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

declare(strict_types=1);

namespace BaksDev\Delivery\Repository\AllDelivery;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Delivery\Entity as DeliveryEntity;
use BaksDev\Reference\Region\Entity as RegionEntity;
use BaksDev\Users\Profile\TypeProfile\Entity as TypeProfileEntity;

final class AllDelivery implements AllDeliveryInterface
{

    private PaginatorInterface $paginator;

    private DBALQueryBuilder $DBALQueryBuilder;


    public function __construct(
        DBALQueryBuilder $DBALQueryBuilder,
        PaginatorInterface $paginator,
    )
    {

        $this->paginator = $paginator;
        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }


    public function fetchAllDeliveryAssociative(SearchDTO $search): PaginatorInterface
    {
        $qb = $this->DBALQueryBuilder->createQueryBuilder(self::class)
        ->bindLocal();


        $qb->select('delivery.id');
        $qb->addSelect('delivery.event');
        $qb->from(DeliveryEntity\Delivery::TABLE, 'delivery');

        $qb->addSelect('event.sort AS delivery_sort');
        $qb->addSelect('event.active AS delivery_active');
        $qb->join('delivery', DeliveryEntity\Event\DeliveryEvent::TABLE, 'event', 'event.id = delivery.event');

        $qb->addSelect('trans.name AS delivery_name');
        $qb->addSelect('trans.description AS delivery_description');

        $qb->leftJoin('event',
            DeliveryEntity\Trans\DeliveryTrans::TABLE,
            'trans',
            'trans.event = event.id AND trans.local = :local'
        );

        /** Стоимость доставки */
        $qb->addSelect('price.price AS delivery_price');
        $qb->addSelect('price.currency AS delivery_currency');
        $qb->addSelect('price.excess AS delivery_excess');

        $qb->leftJoin('event',
            DeliveryEntity\Price\DeliveryPrice::TABLE,
            'price',
            'price.event = event.id'
        );


        /** Обложка */
        $qb->addSelect('cover.ext AS delivery_cover_ext');
        $qb->addSelect('cover.cdn AS delivery_cover_cdn');

        $qb->addSelect("
			CASE
			   WHEN cover.name IS NOT NULL THEN
					CONCAT ( '/upload/".DeliveryEntity\Cover\DeliveryCover::TABLE."' , '/', cover.name)
			   ELSE NULL
			END AS delivery_cover_name
		"
        );

        $qb->leftJoin('event',
            DeliveryEntity\Cover\DeliveryCover::TABLE,
            'cover',
            'cover.event = event.id'
        );

        /** Ограничение профилем */

        $qb->leftJoin('event',
            TypeProfileEntity\TypeProfile::TABLE,
            'type_profile',
            'event.type IS NOT NULL AND type_profile.id = event.type'
        );

        $qb->leftJoin('type_profile',
            TypeProfileEntity\Event\TypeProfileEvent::TABLE,
            'type_profile_event',
            'type_profile_event.id = type_profile.event'
        );

        $qb->addSelect('type_profile_trans.name AS type_profile_name');

        $qb->leftJoin('type_profile_event',
            TypeProfileEntity\Trans\TypeProfileTrans::TABLE,
            'type_profile_trans',
            'type_profile_trans.event = type_profile_event.id AND type_profile_trans.local = :local'
        );

        /** Ограничение регионом */
        $qb->leftJoin('event',
            RegionEntity\Region::TABLE,
            'region',
            'event.region IS NOT NULL AND region.id = event.region'
        );

        $qb->leftJoin('region',
            RegionEntity\Event\RegionEvent::TABLE,
            'region_event',
            'region_event.id = region.event'
        );

        $qb->addSelect('region_trans.name AS region_name');

        $qb->leftJoin('region_event',
            RegionEntity\Trans\RegionTrans::TABLE,
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