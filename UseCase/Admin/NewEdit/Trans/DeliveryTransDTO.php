<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Delivery\UseCase\Admin\NewEdit\Trans;

use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Delivery\Entity\Trans\DeliveryTransInterface;
use ReflectionProperty;
use Symfony\Component\Validator\Constraints as Assert;

final class DeliveryTransDTO implements DeliveryTransInterface
{
    /** Локаль */
    #[Assert\NotBlank]
    private readonly Locale $local;

    /** Название  */
    #[Assert\NotBlank]
    private ?string $name;

    /** Краткое описание */
    private ?string $description = null;

    /** Пользовательское соглашение */
    private ?string $agreement;


    /** Локаль */

    public function getLocal(): Locale
    {
        return $this->local;
    }


    public function setLocal(Locale $local): void
    {
        if(!(new ReflectionProperty(self::class, 'local'))->isInitialized($this))
        {
            $this->local = $local;
        }
    }


    /** Название продукта  */

    public function getName(): string
    {
        return $this->name;
    }


    public function setName(?string $name): void
    {
        $this->name = $name;
    }


    /** Краткое описание */

    public function getDescription(): ?string
    {
        return $this->description;
    }


    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }


    /** Пользовательское соглашение */

    public function getAgreement(): ?string
    {
        return $this->agreement;
    }


    public function setAgreement(?string $agreement): void
    {
        $this->agreement = $agreement;
    }

}
