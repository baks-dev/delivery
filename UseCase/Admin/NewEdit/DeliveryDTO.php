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

namespace BaksDev\Delivery\UseCase\Admin\NewEdit;

use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Delivery\Entity\Event\DeliveryEventInterface;
use BaksDev\Delivery\Type\Event\DeliveryEventUid;
use BaksDev\Delivery\Type\Id\DeliveryUid;
use BaksDev\Delivery\UseCase\Admin\NewEdit\Cover\DeliveryCoverDTO;
use BaksDev\Delivery\UseCase\Admin\NewEdit\Price\DeliveryPriceDTO;
use BaksDev\Delivery\UseCase\Admin\NewEdit\Term\DeliveryTermDTO;
use BaksDev\Reference\Region\Type\Id\RegionUid;
use BaksDev\Users\Profile\TypeProfile\Type\Id\TypeProfileUid;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/** @see DeliveryEvent */
final class DeliveryDTO implements DeliveryEventInterface
{
    /** Идентификатор события */
    #[Assert\Uuid]
    private ?DeliveryUid $delivery = null;

    /** Идентификатор события */
    #[Assert\Uuid]
    private ?DeliveryEventUid $id = null;

    /** Профиль пользователя, которому доступна доставка (null - все) */
    #[Assert\Uuid]
    private ?TypeProfileUid $type = null;

    /** Регион, которому доступна доставка (null - все) */
    #[Assert\Uuid]
    private ?RegionUid $region = null;

    /** Стомиость доставки */
    #[Assert\Valid]
    private Price\DeliveryPriceDTO $price;

    /** Перевод (настройки локали) доставки */
    #[Assert\Valid]
    private ArrayCollection $translate;

    /** Поля для заполнения */
    #[Assert\Valid]
    private ArrayCollection $field;

    /** Обложка способа доставки */
    #[Assert\Valid]
    private Cover\DeliveryCoverDTO $cover;

    /** Сортировка */
    #[Assert\NotBlank]
    private int $sort = 500;

    /** Флаг активности */
    private bool $active = true;


    private DeliveryTermDTO $term;


    public function __construct(?DeliveryUid $delivery = null)
    {
        if($delivery)
        {
            $this->delivery = $delivery;
        }

        $this->translate = new ArrayCollection();
        $this->field = new ArrayCollection();

        $this->cover = new DeliveryCoverDTO();
        $this->price = new DeliveryPriceDTO();
        $this->term = new DeliveryTermDTO();
    }


    public function getEvent(): ?DeliveryEventUid
    {
        return $this->id;
    }

    /**
     * Delivery
     */
    public function getDeliveryUid(): ?DeliveryUid
    {
        return $this->delivery;
    }

    /** Перевод */

    public function setTranslate(ArrayCollection $trans): void
    {
        $this->translate = $trans;
    }


    public function getTranslate(): ArrayCollection
    {
        /* Вычисляем расхождение и добавляем неопределенные локали */
        foreach(Locale::diffLocale($this->translate) as $locale)
        {
            $DeliveryTransDTO = new Trans\DeliveryTransDTO();
            $DeliveryTransDTO->setLocal($locale);
            $this->addTranslate($DeliveryTransDTO);
        }

        return $this->translate;
    }


    public function addTranslate(Trans\DeliveryTransDTO $trans): void
    {
        if(empty($trans->getLocal()->getLocalValue()))
        {
            return;
        }

        if(!$this->translate->contains($trans))
        {
            $this->translate->add($trans);
        }
    }


    public function removeTranslate(Trans\DeliveryTransDTO $trans): void
    {
        $this->translate->removeElement($trans);
    }


    /** Поля для заполнения */

    public function getField(): ArrayCollection
    {
        return $this->field;
    }


    public function setField(ArrayCollection $field): void
    {
        $this->field = $field;
    }


    public function addField(Fields\DeliveryFieldDTO $field): void
    {
        if(!$this->translate->contains($field))
        {
            $this->field->add($field);
        }
    }


    public function removeField(Fields\DeliveryFieldDTO $field): void
    {
        $this->field->removeElement($field);
    }


    /** Обложка способа доставки */

    public function getCover(): Cover\DeliveryCoverDTO
    {
        return $this->cover;
    }


    public function setCover(Cover\DeliveryCoverDTO $cover): void
    {
        $this->cover = $cover;
    }


    /** Сортировка */

    public function getSort(): int
    {
        return $this->sort;
    }


    public function setSort(int $sort): void
    {
        $this->sort = $sort;
    }


    /** Флаг активности */

    public function getActive(): bool
    {
        return $this->active;
    }


    public function setActive(bool $active): void
    {
        $this->active = $active;
    }


    /** Профиль пользователя, которому доступна доставка (null - все) */

    public function getType(): ?TypeProfileUid
    {
        return $this->type;
    }


    public function setType(?TypeProfileUid $type): void
    {
        $this->type = $type;
    }


    /** Регион, которому доступна доставка (null - все) */

    public function getRegion(): ?RegionUid
    {
        return $this->region;
    }


    public function setRegion(?RegionUid $region): void
    {
        $this->region = $region;
    }


    /** Стомиость доставки */

    public function getPrice(): Price\DeliveryPriceDTO
    {
        return $this->price;
    }


    public function setPrice(Price\DeliveryPriceDTO $price): void
    {
        $this->price = $price;
    }

    public function getTerm(): DeliveryTermDTO
    {
        return $this->term;
    }

    public function setTerm(DeliveryTermDTO $term): self
    {
        $this->term = $term;
        return $this;
    }

}
