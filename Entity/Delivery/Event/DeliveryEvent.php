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

namespace BaksDev\Delivery\Entity\Delivery\Event;

use App\System\Type\Locale\Locale;
use BaksDev\Core\Type\Modify\ModifyAction;
use BaksDev\Core\Type\Modify\ModifyActionEnum;
use BaksDev\Delivery\Entity\Delivery\Modify\DeliveryModify;
use BaksDev\Delivery\Type\Event\DeliveryEventUid;
use BaksDev\Delivery\Type\Id\DeliveryUid;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Core\Entity\EntityState;
use InvalidArgumentException;

/* DeliveryEvent */


#[ORM\Entity]
#[ORM\Table(name: 'delivery_event')]
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
	
	/** One To One */
	//#[ORM\OneToOne(mappedBy: 'event', targetEntity: DeliveryLogo::class, cascade: ['all'])]
	//private ?DeliveryOne $one = null;
	
	/** Модификатор */
	#[ORM\OneToOne(mappedBy: 'event', targetEntity: DeliveryModify::class, cascade: ['all'])]
	private DeliveryModify $modify;
	
	/** Перевод */
	//#[ORM\OneToMany(mappedBy: 'event', targetEntity: DeliveryTrans::class, cascade: ['all'])]
	//private Collection $translate;
	
	public function __toString() : string
	{
		return $this->id;
	}
	
	
	public function __construct()
	{
		$this->id = new DeliveryEventUid();
		$this->modify = new DeliveryModify($this);
		
	}
	
	
	public function __clone()
	{
		$this->id = new DeliveryEventUid();
	}
	
	
	public function getId() : DeliveryEventUid
	{
		return (string) $this->id;
	}
	
	
	public function setMain(DeliveryUid|Delivery $main) : void
	{
		$this->main = $main instanceof Delivery ? $main->getId() : $main;
	}
	
	
	public function getMain() : ?DeliveryUid
	{
		return $this->main;
	}
	
	
	public function getDto($dto) : mixed
	{
		if($dto instanceof DeliveryEventInterface)
		{
			return parent::getDto($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	
	public function setEntity($dto) : mixed
	{
		if($dto instanceof DeliveryEventInterface)
		{
			return parent::setEntity($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	
	public function isModifyActionEquals(ModifyActionEnum $action) : bool
	{
		return $this->modify->equals($action);
	}
	
	//	public function getUploadClass() : DeliveryImage
	//	{
	//		return $this->image ?: $this->image = new DeliveryImage($this);
	//	}
	
	//	public function getNameByLocale(Locale $locale) : ?string
	//	{
	//		$name = null;
	//		
	//		/** @var DeliveryTrans $trans */
	//		foreach($this->translate as $trans)
	//		{
	//			if($name = $trans->name($locale))
	//			{
	//				break;
	//			}
	//		}
	//		
	//		return $name;
	//	}
}