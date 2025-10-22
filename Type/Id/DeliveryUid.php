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

namespace BaksDev\Delivery\Type\Id;

use BaksDev\Core\Type\UidType\Uid;
use BaksDev\Delivery\Type\Event\DeliveryEventUid;
use BaksDev\Delivery\Type\Id\Choice\Collection\TypeDeliveryInterface;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;
use Symfony\Component\Uid\AbstractUid;

final class DeliveryUid extends Uid
{
    public const string TEST = '0188a996-df3c-7446-ae07-5ba49bf3e4bd';

    public const string TYPE = 'delivery';

    /** Идентификатор события */
    private ?DeliveryEventUid $event;

    private mixed $attr;

    private mixed $option;

    private ?Money $price;

    private ?Money $excess;

    private ?Currency $currency;

    private int $term;

    public function __construct(
        AbstractUid|TypeDeliveryInterface|self|string|null $value = null,
        DeliveryEventUid|string|null $event = null,
        mixed $attr = null,
        mixed $option = null,
        mixed $price = null,
        mixed $excess = null,
        mixed $currency = null,
        ?int $term = 0,
    )
    {
        if(is_string($value) && class_exists($value))
        {
            $value = new $value();
        }

        if($value instanceof TypeDeliveryInterface)
        {
            $value = $value->getValue();
        }

        parent::__construct($value);

        /** DeliveryEventUid */

        if($event && is_string($event))
        {
            $event = new DeliveryEventUid($event);
        }

        $this->event = $event;

        $this->attr = $attr;
        $this->option = $option;

        if($price !== null && !$price instanceof Money)
        {

            $price = new Money($price);
        }

        if($excess !== null && !$excess instanceof Money)
        {
            $excess = new Money($excess);
        }

        if($currency !== null && !$currency instanceof Currency)
        {
            $currency = new Currency($currency);
        }

        $this->price = $price;
        $this->excess = $excess;
        $this->currency = $currency;
        $this->term = $term ?: 0;
    }


    public function getEvent(): DeliveryEventUid
    {
        return $this->event;
    }

    public function getAttr(): mixed
    {
        return $this->attr;
    }

    public function getOption(): mixed
    {
        return $this->option;
    }

    public function getPrice(): ?Money
    {
        return $this->price;
    }

    public function getExcess(): ?Money
    {
        return $this->excess;
    }

    public function getCurrency(): ?Currency
    {
        return $this->currency;
    }

    public function getTypeDeliveryValue(): string
    {
        return (string) $this->getValue();
    }

    public function getTypeDelivery(): DeliveryUid|TypeDeliveryInterface
    {
        foreach(self::getDeclared() as $declared)
        {
            /** @var TypeDeliveryInterface $declared */
            if($declared::equals($this->getValue()))
            {
                return new $declared();
            }
        }

        return new self($this->getValue());
    }


    public static function getDeclared(): array
    {
        return array_filter(
            get_declared_classes(),
            static function($className) {
                return in_array(TypeDeliveryInterface::class, class_implements($className), true);
            }
        );
    }

    public function getTerm(): int
    {
        return $this->term;
    }

}