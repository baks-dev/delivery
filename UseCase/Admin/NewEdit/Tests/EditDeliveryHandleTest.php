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

namespace BaksDev\Delivery\UseCase\Admin\NewEdit\Tests;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Type\Field\InputField;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Core\Type\Locale\Locales\Ru;
use BaksDev\Delivery\Entity\Delivery;
use BaksDev\Delivery\Entity\Event\DeliveryEvent;
use BaksDev\Delivery\Type\Event\DeliveryEventUid;
use BaksDev\Delivery\Type\Id\DeliveryUid;
use BaksDev\Delivery\UseCase\Admin\NewEdit\DeliveryDTO;
use BaksDev\Delivery\UseCase\Admin\NewEdit\DeliveryHandler;
use BaksDev\Delivery\UseCase\Admin\NewEdit\Fields\DeliveryFieldDTO;
use BaksDev\Delivery\UseCase\Admin\NewEdit\Fields\Trans\DeliveryFieldTransDTO;
use BaksDev\Delivery\UseCase\Admin\NewEdit\Price\DeliveryPriceDTO;
use BaksDev\Delivery\UseCase\Admin\NewEdit\Trans\DeliveryTransDTO;
use BaksDev\Reference\Currency\Type\Currencies\Collection\CurrencyCollection;
use BaksDev\Reference\Currency\Type\Currencies\RUR;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;
use BaksDev\Reference\Region\Type\Id\RegionUid;
use BaksDev\Users\Profile\TypeProfile\Type\Id\TypeProfileUid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;
use BaksDev\Delivery\UseCase\Admin\NewEdit\Tests\NewDeliveryHandleTest;
use BaksDev\Delivery\Controller\Admin\Tests\DeleteControllerTest;
use BaksDev\Delivery\Controller\Admin\Tests\EditControllerTest;

/**
 * @group delivery
 * @group delivery-handler
 *
 * @depends BaksDev\Delivery\Controller\Admin\Tests\DeleteControllerTest::class
 * @depends BaksDev\Delivery\Controller\Admin\Tests\EditControllerTest::class
 */
#[When(env: 'test')]
final class EditDeliveryHandleTest extends KernelTestCase
{

    public static function setUpBeforeClass(): void
    {
        /** @var CurrencyCollection $CurrencyCollection */
        $CurrencyCollection = self::getContainer()->get(CurrencyCollection::class);
        $CurrencyCollection->cases();
    }


    public function testUseCase(): void
    {
        $container = self::getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $DeliveryEvent = $em->getRepository(DeliveryEvent::class)->find(DeliveryEventUid::TEST);
        self::assertNotNull($DeliveryEvent);



        /**
         * DeliveryDTO
         */

        $DeliveryDTO = new DeliveryDTO();
        $DeliveryEvent->getDto($DeliveryDTO);


        self::assertTrue($DeliveryDTO->getType()->equals($TypeProfileUid = new TypeProfileUid()));
        $DeliveryDTO->setType(clone $TypeProfileUid);


        self::assertSame(123, $DeliveryDTO->getSort());
        $DeliveryDTO->setSort(456);


        self::asserttrue($DeliveryDTO->getRegion()->equals($RegionUid = new RegionUid()));
        $DeliveryDTO->setRegion(clone $RegionUid);


        self::assertTrue($DeliveryDTO->getActive());
        $DeliveryDTO->setActive(false);

        /**
         * DeliveryTransDTO
         */

        /** @var DeliveryTransDTO $DeliveryTransDTO */
        $DeliveryTransDTO = $DeliveryDTO->getTranslate()->current();

        self::assertEquals('Name', $DeliveryTransDTO->getName());
        $DeliveryTransDTO->setName('EditName');

        self::assertTrue($DeliveryTransDTO->getLocal()->equals(new Locale(Ru::class)));

        self::assertEquals('Description', $DeliveryTransDTO->getDescription());
        $DeliveryTransDTO->setDescription('EditDescription');


        self::assertEquals('Agreement', $DeliveryTransDTO->getAgreement());
        $DeliveryTransDTO->setAgreement('EditAgreement');


        /**
         * DeliveryFieldDTO
         */


        /** @var DeliveryFieldDTO $DeliveryFieldDTO */
        $DeliveryFieldDTO = $DeliveryDTO->getField()->current();

        self::assertEquals(123, $DeliveryFieldDTO->getSort());
        $DeliveryFieldDTO->setSort(456);

        self::assertTrue($DeliveryFieldDTO->getRequired());
        $DeliveryFieldDTO->setRequired(false);

        self::assertEquals('input', $DeliveryFieldDTO->getType()->getType());



        /** @var DeliveryFieldTransDTO $DeliveryFieldTransDTO */
        $DeliveryFieldTransDTO = $DeliveryFieldDTO->getTranslate()->current();

        self::assertEquals('Name', $DeliveryFieldTransDTO->getName());
        $DeliveryFieldTransDTO->setName('EditName');

        self::assertTrue($DeliveryFieldTransDTO->getLocal()->equals(new Locale(Ru::class)));

        self::assertSame('Description', $DeliveryFieldTransDTO->getDescription());
        $DeliveryFieldTransDTO->setDescription('EditDescription');


        /**
         * DeliveryPriceDTO
         */

        /** @var DeliveryPriceDTO $DeliveryPriceDTO */
        $DeliveryPriceDTO = $DeliveryDTO->getPrice();

        self::assertEquals(100, $DeliveryPriceDTO->getPrice()->getValue());
        $DeliveryPriceDTO->setPrice(new Money(200));

        self::assertTrue($DeliveryPriceDTO->getCurrency()->equals(RUR::class));

        self::assertEquals(10, $DeliveryPriceDTO->getExcess()->getValue());
        $DeliveryPriceDTO->setExcess(new Money(5));


        self::bootKernel();

        /** @var DeliveryHandler $handler */
        $handler = self::getContainer()->get(DeliveryHandler::class);
        $handle = $handler->handle($DeliveryDTO);

        self::assertTrue(($handle instanceof Delivery), $handle.': Ошибка Delivery');

    }
}