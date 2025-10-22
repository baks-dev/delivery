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

namespace BaksDev\Delivery\Repository\DeliveryByProfileChoice;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Delivery\Entity\Delivery;
use BaksDev\Delivery\Entity\Event\DeliveryEvent;
use BaksDev\Delivery\Entity\Price\DeliveryPrice;
use BaksDev\Delivery\Entity\Term\DeliveryTerm;
use BaksDev\Delivery\Entity\Trans\DeliveryTrans;
use BaksDev\Delivery\Type\Id\DeliveryUid;
use BaksDev\Orders\Order\Repository\DeliveryByProfileChoice\DeliveryByProfileChoiceInterface;
use BaksDev\Reference\Region\Entity\Region;
use BaksDev\Reference\Region\Type\Id\RegionUid;
use BaksDev\Users\Profile\TypeProfile\Type\Id\TypeProfileUid;
use Generator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @see DeliveryByProfileChoiceInterface
 */
final class DeliveryByProfileChoiceRepository implements DeliveryByProfileChoiceInterface
{
    private RegionUid|false $region;

    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
        #[Autowire(env: 'PROJECT_REGION')] string|null $region = null,
    )
    {
        $this->region = $region ? new RegionUid($region) : false;
    }

    public function forRegion(RegionUid|Region $region): self
    {
        if($region instanceof Region)
        {
            $region = $region->getId();
        }

        $this->region = $region;

        return $this;
    }

    /**
     * @param TypeProfileUid|null $type
     *
     * @return array<int, DeliveryUid>|null
     */
    public function fetchDeliveryByProfile(?TypeProfileUid $type): ?array
    {
        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();


        $dbal->from(Delivery::class, 'delivery');

        $dbal
            ->join(
                'delivery',
                DeliveryEvent::class,
                'event',
                '
                event.id = delivery.event 
                AND event.active = true  
            '
                .($type instanceof TypeProfileUid ? ' AND (event.type IS NULL OR event.type = :type) ' : ' AND event.type IS NULL ')
                .($this->region instanceof RegionUid ? ' AND (event.region IS NULL OR event.region = :region) ' : ' AND event.region IS NULL '),
            );

        if(true === ($type instanceof TypeProfileUid))
        {
            $dbal->setParameter(
                'type',
                $type,
                TypeProfileUid::TYPE,
            );
        }

        if($this->region instanceof RegionUid)
        {
            $dbal->setParameter(
                'region',
                $this->region,
                RegionUid::TYPE,
            );
        }

        $dbal
            ->leftJoin(
                'delivery',
                DeliveryTrans::class,
                'trans',
                'trans.event = delivery.event AND trans.local = :local',
            );

        $dbal
            ->leftJoin(
                'delivery',
                DeliveryPrice::class,
                'price',
                'price.event = delivery.event',
            );

        $dbal
            ->leftJoin(
                'delivery',
                DeliveryTerm::class,
                'term',
                'term.event = delivery.event',
            );


        $dbal->orderBy('event.sort');

        $dbal->addSelect('delivery.id AS value');
        $dbal->addSelect('delivery.event AS event');
        $dbal->addSelect('trans.name AS attr');
        $dbal->addSelect('trans.description AS option');
        $dbal->addSelect('(price.price / 100) AS price');
        $dbal->addSelect('(price.excess / 100) AS excess');
        $dbal->addSelect('price.currency AS currency');
        $dbal->addSelect('term.value AS term');

        $result = $dbal
            ->enableCache('delivery')
            ->fetchAllHydrate(DeliveryUid::class);

        return $result->valid() ? iterator_to_array($result) : [];
    }


    public function fetchAllDelivery(): ?Generator
    {
        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();


        $dbal->from(Delivery::class, 'delivery');

        $dbal
            ->leftJoin(
                'delivery',
                DeliveryEvent::class,
                'event',
                'event.id = delivery.event AND event.active = true',
            );

        $dbal->leftJoin(
            'delivery',
            DeliveryTrans::class,
            'trans',
            'trans.event = delivery.event AND trans.local = :local',
        );

        $dbal->leftJoin(
            'delivery',
            DeliveryPrice::class,
            'price',
            'price.event = delivery.event',
        );

        $dbal->leftJoin(
            'delivery',
            DeliveryTerm::class,
            'term',
            'term.event = delivery.event',
        );


        $dbal->orderBy('event.sort');

        $dbal->addSelect('delivery.id AS value');
        $dbal->addSelect('delivery.event AS event');
        $dbal->addSelect('trans.name AS attr');
        $dbal->addSelect('trans.description AS option');
        $dbal->addSelect('(price.price / 100) AS price');
        $dbal->addSelect('(price.excess / 100) AS excess');
        $dbal->addSelect('price.currency AS currency');
        $dbal->addSelect('term.value AS term');

        $result = $dbal
            ->enableCache('delivery')
            ->fetchAllHydrate(DeliveryUid::class);

        return $result->valid() ? $result : null;

    }
}
