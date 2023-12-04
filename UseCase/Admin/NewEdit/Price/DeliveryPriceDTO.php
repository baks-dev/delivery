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

namespace BaksDev\Delivery\UseCase\Admin\NewEdit\Price;

use BaksDev\Delivery\Entity\Price\DeliveryPriceInterface;
use BaksDev\Reference\Currency\Type\Currencies\RUR;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;
use Symfony\Component\Validator\Constraints as Assert;

final class DeliveryPriceDTO implements DeliveryPriceInterface
{
	/** Стоимость */
	#[Assert\NotBlank]
	private ?Money $price = null;
	
	/** Стоимость 1 км свыше */
	#[Assert\NotBlank]
	private ?Money $excess = null;
	
	/** Валюта */
	#[Assert\NotBlank]
	private Currency $currency;
	
	
	public function __construct()
	{
		$this->currency = new Currency(RUR::class);
	}
	
	
	/** Стоимость */
	
	public function getPrice() : ?Money
	{
		return $this->price;
	}
	
	
	public function setPrice(Money $price) : void
	{
		$this->price = $price;
	}
	
	
	/** Стоимость 1 км свыше */
	
	public function getExcess() : ?Money
	{
		return $this->excess;
	}
	
	
	public function setExcess(?Money $excess) : void
	{
		$this->excess = $excess;
	}
	
	
	/** Валюта */
	
	public function getCurrency() : Currency
	{
		return $this->currency;
	}
	
	
	public function setCurrency(Currency|string $currency) : void
	{
		$this->currency = $currency instanceof Currency ? $currency : new Currency($currency);
	}
	
}