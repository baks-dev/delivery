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

namespace BaksDev\Delivery\Entity\Cover;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Delivery\Entity\Event\DeliveryEvent;
use BaksDev\Delivery\Type\Event\DeliveryEventUid;
use BaksDev\Files\Resources\Upload\UploadEntityInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/* DeliveryCover */


#[ORM\Entity]
#[ORM\Table(name: 'delivery_cover')]
class DeliveryCover extends EntityEvent implements UploadEntityInterface
{
    /** Связь на событие */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: DeliveryEvent::class, inversedBy: 'cover')]
    #[ORM\JoinColumn(name: 'event', referencedColumnName: 'id')]
    private DeliveryEvent $event;

    /** Название файла */
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[ORM\Column(type: Types::STRING)]
    private string $name;

    /** Расширение файла */
    #[Assert\NotBlank]
    #[Assert\Choice(['png', 'gif', 'jpg', 'jpeg', 'webp'])]
    #[ORM\Column(type: Types::STRING)]
    private string $ext;

    /** Размер файла */
    #[Assert\NotBlank]
    #[Assert\Range(max: 1048576)] // 1024 * 1024
    #[ORM\Column(type: Types::INTEGER)]
    private int $size = 0;

    /** Файл загружен на CDN */
    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $cdn = false;


    public function __construct(DeliveryEvent $event)
    {
        $this->event = $event;
    }

    public function __toString(): string
    {
        return (string) $this->event;
    }

    public function getDto($dto): mixed
    {
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

        if($dto instanceof DeliveryCoverInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }


    public function setEntity($dto): mixed
    {
        /* Если размер файла нулевой - не заполняем сущность */
        if(empty($dto->file) && empty($dto->getName()))
        {
            return false;
        }

        //		if(!empty($dto->file))
        //		{
        //			$dto->setEntityUpload($this);
        //		}

        if($dto instanceof DeliveryCoverInterface || $dto instanceof self)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }


    public function updFile(string $name, string $ext, int $size): void
    {
        $this->name = $name;
        $this->ext = $ext;
        $this->size = $size;
        //$this->dir = $this->event->getId();
        $this->cdn = false;
    }


    public function updCdn(?string $ext = null): void
    {
        if($ext)
        {
            $this->ext = $ext;
            $this->cdn = true;
            return;
        }

        $this->cdn = false;
    }


    public function getId(): DeliveryEventUid
    {
        return $this->event->getId();
    }

    /**
     * Ext
     */
    public function getExt(): string
    {
        return $this->ext;
    }

}
