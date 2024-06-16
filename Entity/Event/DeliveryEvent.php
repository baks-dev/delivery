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

namespace BaksDev\Delivery\Entity\Event;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Delivery\Entity\Cover\DeliveryCover;
use BaksDev\Delivery\Entity\Delivery;
use BaksDev\Delivery\Entity\Fields\DeliveryField;
use BaksDev\Delivery\Entity\Modify\DeliveryModify;
use BaksDev\Delivery\Entity\Price\DeliveryPrice;
use BaksDev\Delivery\Entity\Trans\DeliveryTrans;
use BaksDev\Delivery\Type\Event\DeliveryEventUid;
use BaksDev\Delivery\Type\Id\DeliveryUid;
use BaksDev\Reference\Region\Type\Id\RegionUid;
use BaksDev\Users\Profile\TypeProfile\Type\Id\TypeProfileUid;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

/* DeliveryEvent */


#[ORM\Entity]
#[ORM\Table(name: 'delivery_event')]
#[ORM\Index(columns: ['active'])]
#[ORM\Index(columns: ['sort'])]
#[ORM\Index(columns: ['type'])]
#[ORM\Index(columns: ['region'])]
class DeliveryEvent extends EntityEvent
{
    public const TABLE = 'delivery_event';

    /** ID */
    #[ORM\Id]
    #[ORM\Column(type: DeliveryEventUid::TYPE)]
    private DeliveryEventUid $id;

    /** ID Delivery */
    #[ORM\Column(type: DeliveryUid::TYPE, nullable: false)]
    private ?DeliveryUid $main = null;

    /** Обложка способа доставки */
    #[ORM\OneToOne(targetEntity: DeliveryCover::class, mappedBy: 'event', cascade: ['all'])]
    private ?DeliveryCover $cover = null;

    /** Модификатор */
    #[ORM\OneToOne(targetEntity: DeliveryModify::class, mappedBy: 'event', cascade: ['all'])]
    private DeliveryModify $modify;

    /** Перевод */
    #[ORM\OneToMany(targetEntity: DeliveryTrans::class, mappedBy: 'event', cascade: ['all'])]
    private Collection $translate;

    /** Поля для заполнения */
    #[ORM\OneToMany(targetEntity: DeliveryField::class, mappedBy: 'event', cascade: ['all'])]
    #[ORM\OrderBy(['sort' => 'ASC'])]
    private Collection $field;

    /** Стоимость доставки (null - бесплатно) */
    #[ORM\OneToOne(targetEntity: DeliveryPrice::class, mappedBy: 'event', cascade: ['all'])]
    private ?DeliveryPrice $price = null;

    /** Сортировка */
    #[ORM\Column(type: Types::SMALLINT, length: 3, options: ['default' => 500])]
    private int $sort = 500;

    /** Флаг активности */
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $active = true;


    /** Профиль пользователя, которому доступна доставка  (null - всем) */
    #[ORM\Column(type: TypeProfileUid::TYPE, nullable: true)]
    private ?TypeProfileUid $type = null;

    /** Регион, которому доступна доставка (null - всем) */
    #[ORM\Column(type: RegionUid::TYPE, nullable: true)]
    private ?RegionUid $region = null;


    public function __construct()
    {
        $this->id = new DeliveryEventUid();
        $this->modify = new DeliveryModify($this);
    }

    public function __clone()
    {
        $this->id = clone $this->id;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getId(): DeliveryEventUid
    {
        return $this->id;
    }


    public function setMain(DeliveryUid|Delivery $main): void
    {
        $this->main = $main instanceof Delivery ? $main->getId() : $main;
    }


    public function getMain(): ?DeliveryUid
    {
        return $this->main;
    }


    public function getDto($dto): mixed
    {
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

        if($dto instanceof DeliveryEventInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }


    public function setEntity($dto): mixed
    {
        if($dto instanceof DeliveryEventInterface || $dto instanceof self)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function getNameByLocale(Locale $locale): ?string
    {
        $name = null;

        /** @var DeliveryTrans $trans */
        foreach($this->translate as $trans)
        {
            if($name = $trans->name($locale))
            {
                break;
            }
        }

        return $name;
    }


    public function getUploadCover(): DeliveryCover
    {
        return $this->cover ?: $this->cover = new DeliveryCover($this);
    }

}
