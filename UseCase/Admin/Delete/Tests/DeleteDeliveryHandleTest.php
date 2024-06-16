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

namespace BaksDev\Delivery\UseCase\Admin\Delete\Tests;

use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Core\Type\Locale\Locales\Ru;
use BaksDev\Delivery\Controller\Admin\Tests\DeleteControllerTest;
use BaksDev\Delivery\Entity\Delivery;
use BaksDev\Delivery\Entity\Event\DeliveryEvent;
use BaksDev\Delivery\Repository\CurrentDeliveryEvent\CurrentDeliveryEventRepository;
use BaksDev\Delivery\Type\Id\DeliveryUid;
use BaksDev\Delivery\UseCase\Admin\Delete\DeliveryDeleteDTO;
use BaksDev\Delivery\UseCase\Admin\Delete\DeliveryDeleteHandler;
use BaksDev\Delivery\UseCase\Admin\NewEdit\DeliveryDTO;
use BaksDev\Delivery\UseCase\Admin\NewEdit\Fields\DeliveryFieldDTO;
use BaksDev\Delivery\UseCase\Admin\NewEdit\Fields\Trans\DeliveryFieldTransDTO;
use BaksDev\Delivery\UseCase\Admin\NewEdit\Price\DeliveryPriceDTO;
use BaksDev\Delivery\UseCase\Admin\NewEdit\Trans\DeliveryTransDTO;
use BaksDev\Reference\Currency\Type\Currencies\Collection\CurrencyCollection;
use BaksDev\Reference\Currency\Type\Currencies\RUR;
use BaksDev\Reference\Region\Type\Id\RegionUid;
use BaksDev\Users\Profile\TypeProfile\Type\Id\TypeProfileUid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;
use BaksDev\Delivery\UseCase\Admin\NewEdit\Tests\EditDeliveryHandleTest;

/**
 * @group delivery
 * @group delivery-handler
 *
 * @depends BaksDev\Delivery\UseCase\Admin\NewEdit\Tests\EditDeliveryHandleTest::class
 */
#[When(env: 'test')]
final class DeleteDeliveryHandleTest extends KernelTestCase
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
        /** @var CurrentDeliveryEventRepository $CurrentDeliveryEvent */
        $CurrentDeliveryEvent = $container->get(CurrentDeliveryEventRepository::class);


        $DeliveryEvent = $CurrentDeliveryEvent->get(DeliveryUid::TEST);
        self::assertNotNull($DeliveryEvent);


        /**
         * DeliveryDTO
         */

        $DeliveryDTO = new DeliveryDTO();
        $DeliveryEvent->getDto($DeliveryDTO);


        self::assertFalse($DeliveryDTO->getType()->equals($TypeProfileUid = new TypeProfileUid()));
        self::assertSame(456, $DeliveryDTO->getSort());
        self::assertFalse($DeliveryDTO->getRegion()->equals($RegionUid = new RegionUid()));
        self::assertFalse($DeliveryDTO->getActive());

        /**
         * DeliveryTransDTO
         */

        /** @var DeliveryTransDTO $DeliveryTransDTO */
        $DeliveryTransDTO = $DeliveryDTO->getTranslate()->current();

        self::assertEquals('EditName', $DeliveryTransDTO->getName());
        self::assertTrue($DeliveryTransDTO->getLocal()->equals(new Locale(Ru::class)));
        self::assertEquals('EditDescription', $DeliveryTransDTO->getDescription());
        self::assertEquals('EditAgreement', $DeliveryTransDTO->getAgreement());


        /**
         * DeliveryFieldDTO
         */


        /** @var DeliveryFieldDTO $DeliveryFieldDTO */
        $DeliveryFieldDTO = $DeliveryDTO->getField()->current();

        self::assertEquals(456, $DeliveryFieldDTO->getSort());
        self::assertFalse($DeliveryFieldDTO->getRequired());
        self::assertEquals('input', $DeliveryFieldDTO->getType()->getType());


        /** @var DeliveryFieldTransDTO $DeliveryFieldTransDTO */
        $DeliveryFieldTransDTO = $DeliveryFieldDTO->getTranslate()->current();

        self::assertEquals('EditName', $DeliveryFieldTransDTO->getName());
        self::assertTrue($DeliveryFieldTransDTO->getLocal()->equals(new Locale(Ru::class)));
        self::assertSame('EditDescription', $DeliveryFieldTransDTO->getDescription());


        /**
         * DeliveryPriceDTO
         */

        /** @var DeliveryPriceDTO $DeliveryPriceDTO */
        $DeliveryPriceDTO = $DeliveryDTO->getPrice();

        self::assertEquals(200, $DeliveryPriceDTO->getPrice()->getValue());
        self::assertTrue($DeliveryPriceDTO->getCurrency()->equals(RUR::class));
        self::assertEquals(5, $DeliveryPriceDTO->getExcess()->getValue());


        $DeliveryDeleteDTO = new DeliveryDeleteDTO();
        $DeliveryEvent->getDto($DeliveryDeleteDTO);


        /** @var DeliveryDeleteHandler $handler */
        $handler = self::getContainer()->get(DeliveryDeleteHandler::class);
        $handle = $handler->handle($DeliveryDeleteDTO);

        self::assertTrue(($handle instanceof Delivery), $handle.': Ошибка Delivery');


    }

    public static function tearDownAfterClass(): void
    {
        /** @var CurrencyCollection $CurrencyCollection */
        $CurrencyCollection = self::getContainer()->get(CurrencyCollection::class);
        $CurrencyCollection->cases();

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $main = $em->getRepository(Delivery::class)
            ->find(DeliveryUid::TEST);

        if($main)
        {
            $em->remove($main);
        }

        $events = $em->getRepository(DeliveryEvent::class)
            ->findBy(['main' => DeliveryUid::TEST]);

        foreach($events as $remove)
        {
            $em->remove($remove);
        }

        $em->flush();
        $em->clear();
    }

}
