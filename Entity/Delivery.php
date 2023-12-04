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

namespace BaksDev\Delivery\Entity;

use BaksDev\Delivery\Entity\Event\DeliveryEvent;
use BaksDev\Delivery\Type\Event\DeliveryEventUid;
use BaksDev\Delivery\Type\Id\DeliveryUid;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

/* Delivery */


#[ORM\Entity]
#[ORM\Table(name: 'delivery')]
class Delivery
{
	public const TABLE = 'delivery';
	
	/** ID */
	#[ORM\Id]
	#[ORM\Column(type: DeliveryUid::TYPE)]
	private DeliveryUid $id;
	
	/** ID События */
	#[ORM\Column(type: DeliveryEventUid::TYPE, unique: true)]
	private DeliveryEventUid $event;
	
	
	public function __construct()
	{
		$this->id = new DeliveryUid();
	}

    public function __toString(): string
    {
        return (string) $this->id;
    }
	
	public function getId() : DeliveryUid
	{
		return $this->id;
	}
	
	
	public function getEvent() : DeliveryEventUid
	{
		return $this->event;
	}
	
	
	public function setEvent(DeliveryEventUid|DeliveryEvent $event) : void
	{
		$this->event = $event instanceof DeliveryEvent ? $event->getId() : $event;
	}
	
}