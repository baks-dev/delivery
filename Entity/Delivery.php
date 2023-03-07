<?php

declare(strict_types=1);

namespace BaksDev\Delivery\Entity;

use BaksDev\Delivery\Entity\Event\DeliveryEvent;
use BaksDev\Delivery\Event\DeliveryEventUid;
use BaksDev\Delivery\Id\DeliveryUid;
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