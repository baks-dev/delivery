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

namespace BaksDev\Delivery\Type\Id\Tests;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Delivery\Type\Id\Choice\Collection\TypeDeliveryCollection;
use BaksDev\Delivery\Type\Id\Choice\Collection\TypeDeliveryInterface;
use BaksDev\Delivery\Type\Id\DeliveryType;
use BaksDev\Delivery\Type\Id\DeliveryUid;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;


/**
 * @group delivery
 */
#[Group('delivery')]
#[When(env: 'test')]
class DeliveryTypeTest extends KernelTestCase
{
    public function testUseCase(): void
    {
        /** @var TypeDeliveryCollection $TypeDeliveryCollection */
        $TypeDeliveryCollection = self::getContainer()->get(TypeDeliveryCollection::class);

        /** @var TypeDeliveryInterface $case */
        foreach($TypeDeliveryCollection->cases() as $case)
        {
            $DeliveryUid = new DeliveryUid($case->getValue());

            self::assertTrue($DeliveryUid->equals($case::class)); // немспейс интерфейса
            self::assertTrue($DeliveryUid->equals($case)); // объект интерфейса
            self::assertTrue($DeliveryUid->equals($case->getValue())); // срока
            self::assertTrue($DeliveryUid->equals($DeliveryUid)); // объект класса

            $DeliveryType = new DeliveryType();

            $platform = $this
                ->getMockBuilder(AbstractPlatform::class)
                ->getMock();

            $convertToDatabase = $DeliveryType->convertToDatabaseValue($DeliveryUid, $platform);
            self::assertEquals($DeliveryUid->getValue(), $convertToDatabase);

            $convertToPHP = $DeliveryType->convertToPHPValue($convertToDatabase, $platform);
            self::assertInstanceOf(DeliveryUid::class, $convertToPHP);
            self::assertEquals($case->getValue(), $convertToPHP->getValue());

        }

        self::assertTrue(true);

    }
}