<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

namespace BaksDev\Delivery\Entity\Price;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Delivery\Entity\Event\DeliveryEvent;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

/* Стоимость Продукта */


#[ORM\Entity]
#[ORM\Table(name: 'delivery_price')]
class DeliveryPrice extends EntityEvent
{
    public const TABLE = 'delivery_price';

    /** ID события */
    #[ORM\Id]
    #[ORM\OneToOne(inversedBy: 'price', targetEntity: DeliveryEvent::class)]
    #[ORM\JoinColumn(name: 'event', referencedColumnName: 'id')]
    private DeliveryEvent $event;

    /** Стоимость */
    #[ORM\Column(type: Money::TYPE, nullable: true)]
    private ?Money $price;

    /** Превышение за 1 км */
    #[ORM\Column(type: Money::TYPE, nullable: true)]
    private ?Money $excess;

    /** Валюта */
    #[ORM\Column(type: Currency::TYPE, length: 3, nullable: false)]
    private Currency $currency;


    public function __construct(DeliveryEvent $event)
    {
        $this->event = $event;
        $this->currency = new Currency();
    }

    public function __toString(): string
    {
        return (string) $this->event;
    }

    public function getDto($dto): mixed
    {
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

        if($dto instanceof DeliveryPriceInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }


    public function setEntity($dto): mixed
    {
        if($dto instanceof DeliveryPriceInterface || $dto instanceof self)
        {
            if(empty($dto->getPrice()))
            {
                return false;
            }

            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

}
