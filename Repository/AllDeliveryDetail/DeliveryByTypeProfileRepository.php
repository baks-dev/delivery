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

namespace BaksDev\Delivery\Repository\AllDeliveryDetail;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Delivery\Entity\Cover\DeliveryCover;
use BaksDev\Delivery\Entity\Delivery;
use BaksDev\Delivery\Entity\Event\DeliveryEvent;
use BaksDev\Delivery\Entity\Price\DeliveryPrice;
use BaksDev\Delivery\Entity\Trans\DeliveryTrans;
use BaksDev\Reference\Region\Entity\Event\RegionEvent;
use BaksDev\Reference\Region\Entity\Region;
use BaksDev\Reference\Region\Entity\Trans\RegionTrans;
use BaksDev\Reference\Region\Type\Id\RegionUid;
use BaksDev\Users\Profile\TypeProfile\Type\Id\TypeProfileUid;

final class DeliveryByTypeProfileRepository implements DeliveryByTypeProfileInterface
{
    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
    ) {}


    public function fetchAllDeliveryAssociative(TypeProfileUid $profile, ?RegionUid $region): ?array
    {
        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->addSelect('delivery.id AS delivery_id')
            ->addSelect('delivery.event AS delivery_event')
            ->from(Delivery::class, 'delivery');

        $condition = '';

        if($region)
        {
            $condition = 'AND (delivery_event.region = :region OR delivery_event.region IS NULL)';
            $dbal->setParameter(
                'region',
                $region,
                RegionUid::TYPE
            );

        }

        $dbal->join(
            'delivery',
            DeliveryEvent::class,
            'delivery_event',
            '
			delivery_event.id = delivery.event AND
			delivery_event.active = true AND
			(delivery_event.type = :profile OR delivery_event.type IS NULL)'.$condition
        )
            ->setParameter(
                'profile',
                $profile,
                TypeProfileUid::TYPE
            );


        $dbal
            ->addSelect('delivery_trans.name AS delivery_name')
            ->addSelect('delivery_trans.description AS delivery_description')
            ->addSelect('delivery_trans.agreement AS delivery_agreement')
            ->leftJoin(
                'delivery_event',
                DeliveryTrans::class,
                'delivery_trans',
                'delivery_trans.event = delivery_event.id AND delivery_trans.local = :local'
            );

        /** Обложка */
        $dbal
            ->addSelect('delivery_cover.ext AS delivery_cover_ext')
            ->addSelect('delivery_cover.cdn AS delivery_cover_cdn')
            ->addSelect(
                "
			CASE
			 WHEN delivery_cover.name IS NOT NULL THEN
					CONCAT ( '/upload/".$dbal->table(DeliveryCover::class)."' , '/', delivery_cover.name)
			   		ELSE NULL
			END AS delivery_cover_name
		"
            );

        $dbal->leftJoin(
            'delivery_event',
            DeliveryCover::class,
            'delivery_cover',
            'delivery_cover.event = delivery_event.id'
        );

        $dbal
            ->addSelect('delivery_price.price AS delivery_price')
            ->addSelect('delivery_price.excess AS delivery_excess')
            ->addSelect('delivery_price.currency AS delivery_currency')
            ->leftJoin(
                'delivery_event',
                DeliveryPrice::class,
                'delivery_price',
                'delivery_price.event = delivery_event.id'
            );


        /** Регион */
        $dbal
            ->addSelect('region.id AS region_id')
            ->addSelect('region.event AS region_event')
            ->leftJoin(
                'delivery_event',
                Region::class,
                'region',
                'region.id = delivery_event.region'
            );

        $dbal->leftJoin(
            'region',
            RegionEvent::class,
            'region_event',
            'region_event.id = region.event'
        );

        $dbal
            ->addSelect('region_trans.name AS region_name')
            ->addSelect('region_trans.description AS region_description')
            ->leftJoin(
                'region_event',
                RegionTrans::class,
                'region_trans',
                'region_trans.event = region_event.id AND region_trans.local = :local'
            );


        $dbal->addOrderBy('delivery_event.sort');
        $dbal->addOrderBy('region_event.sort');


        return $dbal
            ->enableCache('delivery', 3600)
            ->fetchAllAssociative();

    }
}
