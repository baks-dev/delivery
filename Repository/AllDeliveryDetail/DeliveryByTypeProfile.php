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
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Delivery\Entity as DeliveryEntity;
use BaksDev\Reference\Region\Entity as RegionEntity;
use BaksDev\Reference\Region\Type\Id\RegionUid;
use BaksDev\Users\Profile\TypeProfile\Type\Id\TypeProfileUid;
use Symfony\Contracts\Translation\TranslatorInterface;

final class DeliveryByTypeProfile implements DeliveryByTypeProfileInterface
{

    private TranslatorInterface $translator;
    private DBALQueryBuilder $DBALQueryBuilder;


    public function __construct(
        DBALQueryBuilder $DBALQueryBuilder,
        TranslatorInterface $translator,
    )
    {

        $this->translator = $translator;
        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }


    public function fetchAllDeliveryAssociative(TypeProfileUid $profile, ?RegionUid $region): ?array
    {
        $qb = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        //$qb->select('id');
        $qb->addSelect('delivery.id AS delivery_id');
        $qb->addSelect('delivery.event AS delivery_event');

        $qb->from(DeliveryEntity\Delivery::TABLE, 'delivery');

        $condition = '';

        if($region)
        {
            $condition = 'AND (delivery_event.region = :region OR delivery_event.region IS NULL)';
            $qb->setParameter('region', $region, RegionUid::TYPE);

        }

        $qb->join('delivery',
            DeliveryEntity\Event\DeliveryEvent::TABLE,
            'delivery_event',
            '
			delivery_event.id = delivery.event AND
			delivery_event.active = true AND
			(delivery_event.type = :profile OR delivery_event.type IS NULL)
	'.$condition
        );

        $qb->addSelect('delivery_trans.name AS delivery_name');
        $qb->addSelect('delivery_trans.description AS delivery_description');
        $qb->addSelect('delivery_trans.agreement AS delivery_agreement');
        $qb->leftJoin('delivery_event',
            DeliveryEntity\Trans\DeliveryTrans::TABLE,
            'delivery_trans',
            'delivery_trans.event = delivery_event.id AND delivery_trans.local = :local'
        );

        /** Обложка */
        $qb->addSelect('delivery_cover.ext AS delivery_cover_ext');
        $qb->addSelect('delivery_cover.cdn AS delivery_cover_cdn');

        $qb->addSelect("
			CASE
			 WHEN delivery_cover.name IS NOT NULL THEN
					CONCAT ( '/upload/".DeliveryEntity\Cover\DeliveryCover::TABLE."' , '/', delivery_cover.name)
			   		ELSE NULL
			END AS delivery_cover_name
		"
        );

        $qb->leftJoin('delivery_event',
            DeliveryEntity\Cover\DeliveryCover::TABLE,
            'delivery_cover',
            'delivery_cover.event = delivery_event.id'
        );

        $qb->addSelect('delivery_price.price AS delivery_price');
        $qb->addSelect('delivery_price.excess AS delivery_excess');
        $qb->addSelect('delivery_price.currency AS delivery_currency');
        $qb->leftJoin('delivery_event',
            DeliveryEntity\Price\DeliveryPrice::TABLE,
            'delivery_price',
            'delivery_price.event = delivery_event.id'
        );


        /** Регион */
        $qb->addSelect('region.id AS region_id');
        $qb->addSelect('region.event AS region_event');
        $qb->leftJoin('delivery_event',
            RegionEntity\Region::TABLE,
            'region',
            'region.id = delivery_event.region'
        );

        $qb->leftJoin('region',
            RegionEntity\Event\RegionEvent::TABLE,
            'region_event',
            'region_event.id = region.event'
        );

        $qb->addSelect('region_trans.name AS region_name');
        $qb->addSelect('region_trans.description AS region_description');

        $qb->leftJoin('region_event',
            RegionEntity\Trans\RegionTrans::TABLE,
            'region_trans',
            'region_trans.event = region_event.id AND region_trans.local = :local'
        );

        $qb->setParameter('profile', $profile, TypeProfileUid::TYPE);
        $qb->setParameter('local', new Locale($this->translator->getLocale()), Locale::TYPE);

        $qb->addOrderBy('delivery_event.sort');
        $qb->addOrderBy('region_event.sort');


        /* Кешируем результат DBAL */
        return $qb->enableCache('delivery', 3600)->fetchAllAssociative();

    }
}