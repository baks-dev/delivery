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

/**
 * @group delivery
 * @group delivery-handler
 */
#[When(env: 'test')]
final class NewDeliveryHandleTest extends KernelTestCase
{

    public static function setUpBeforeClass(): void
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


        /* WbBarcodeEvent */

        $events = $em->getRepository(DeliveryEvent::class)
            ->findBy(['main' => DeliveryUid::TEST]);

        foreach($events as $remove)
        {
            $em->remove($remove);
        }

        $em->flush();
        $em->clear();
    }


    public function testUseCase(): void
    {
        /**
         * DeliveryDTO
         */

        $DeliveryDTO = new DeliveryDTO();

        $DeliveryDTO->setType($TypeProfileUid = new TypeProfileUid());
        self::assertSame($TypeProfileUid, $DeliveryDTO->getType());

        $DeliveryDTO->setSort(123);
        self::assertSame(123, $DeliveryDTO->getSort());

        $DeliveryDTO->setRegion($RegionUid = new RegionUid());
        self::assertSame($RegionUid, $DeliveryDTO->getRegion());

        $DeliveryDTO->setActive(true);
        self::assertTrue($DeliveryDTO->getActive());

        /**
         * DeliveryTransDTO
         */

        $DeliveryTransDTO = new  DeliveryTransDTO();

        $DeliveryTransDTO->setName('Name');
        self::assertEquals('Name', $DeliveryTransDTO->getName());

        $DeliveryTransDTO->setLocal($LocaleDelivery = new Locale(Ru::class));
        self::assertSame($LocaleDelivery, $DeliveryTransDTO->getLocal());

        $DeliveryTransDTO->setDescription('Description');
        self::assertEquals('Description', $DeliveryTransDTO->getDescription());

        $DeliveryTransDTO->setAgreement('Agreement');
        self::assertEquals('Agreement', $DeliveryTransDTO->getAgreement());

        $DeliveryDTO->addTranslate($DeliveryTransDTO);
        self::assertTrue($DeliveryDTO->getTranslate()->contains($DeliveryTransDTO));

        /**
         * DeliveryFieldDTO
         */

        $DeliveryFieldDTO = new DeliveryFieldDTO();


        $DeliveryFieldDTO->setSort(123);
        self::assertEquals(123, $DeliveryFieldDTO->getSort());

        $DeliveryFieldDTO->setRequired(true);
        self::assertTrue($DeliveryFieldDTO->getRequired());


        $DeliveryFieldDTO->setType($InputField = new InputField('input'));
        self::assertSame($InputField, $DeliveryFieldDTO->getType());



        $DeliveryFieldTransDTO = new DeliveryFieldTransDTO();

        $DeliveryFieldTransDTO->setName('Name');
        self::assertEquals('Name', $DeliveryFieldTransDTO->getName());

        $DeliveryFieldTransDTO->setLocal($LocaleField = new Locale(Ru::class));
        self::assertSame($LocaleField, $DeliveryFieldTransDTO->getLocal());

        $DeliveryFieldTransDTO->setDescription('Description');
        self::assertSame('Description', $DeliveryFieldTransDTO->getDescription());

        $DeliveryFieldDTO->addTranslate($DeliveryFieldTransDTO);
        self::assertTrue($DeliveryFieldDTO->getTranslate()->contains($DeliveryFieldTransDTO));

        $DeliveryDTO->addField($DeliveryFieldDTO);
        self::assertTrue($DeliveryDTO->getField()->contains($DeliveryFieldDTO));


        /**
         * DeliveryPriceDTO
         */

        $DeliveryPriceDTO = new DeliveryPriceDTO();

        $DeliveryDTO->setPrice($DeliveryPriceDTO);
        self::assertSame($DeliveryPriceDTO, $DeliveryDTO->getPrice());

        $DeliveryPriceDTO->setPrice($MoneyPrice = new Money(100));
        self::assertSame($MoneyPrice, $DeliveryPriceDTO->getPrice());



        $DeliveryPriceDTO->setCurrency($Currency = new Currency(new RUR()));
        self::assertSame($Currency, $DeliveryPriceDTO->getCurrency());

        $DeliveryPriceDTO->setExcess($MoneyExcess = new Money(10));
        self::assertSame($MoneyExcess, $DeliveryPriceDTO->getExcess());


        self::bootKernel();

        /** @var DeliveryHandler $handler */
        $handler = self::getContainer()->get(DeliveryHandler::class);
        $handle = $handler->handle($DeliveryDTO);

        self::assertTrue(($handle instanceof Delivery), $handle.': Ошибка Delivery');

    }

    public function testComplete(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var DBALQueryBuilder $dbal */
        $dbal = $container->get(DBALQueryBuilder::class);

        $dbal->createQueryBuilder(self::class);
        $dbal
            ->from(Delivery::class, 'main')
            ->where('main.id = :id')
            ->setParameter('id', DeliveryUid::TEST)
        ;

        self::assertTrue($dbal->fetchExist());
    }
}