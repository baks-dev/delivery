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

namespace BaksDev\Delivery\Messenger\Tests;


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

use BaksDev\Delivery\Messenger\DeliveryMessage;
use BaksDev\Delivery\Type\Event\DeliveryEventUid;
use BaksDev\Delivery\Type\Id\DeliveryUid;
use PHPUnit\Framework\TestCase;

/**
 * @covers \BaksDev\Delivery\Messenger\DeliveryMessage
 * @group delivery
 */
final class DeliveryMessageTest extends TestCase
{
    public function testGettersWithLastEvent(): void
    {
        $deliveryId = new DeliveryUid();
        $deliveryEventId = new DeliveryEventUid();
        $lastDeliveryEventId = new DeliveryEventUid();

        // Создаем экземпляр сообщения с тремя параметрами
        $message = new DeliveryMessage($deliveryId, $deliveryEventId, $lastDeliveryEventId);

        // Проверяем, что геттеры возвращают те же самые объекты, что были переданы в конструктор
        self::assertSame($deliveryId, $message->getId());
        self::assertInstanceOf(DeliveryUid::class, $message->getId());

        self::assertSame($deliveryEventId, $message->getEvent());
        self::assertInstanceOf(DeliveryEventUid::class, $message->getEvent());

        self::assertSame($lastDeliveryEventId, $message->getLast());
        self::assertInstanceOf(DeliveryEventUid::class, $message->getLast());
    }

    public function testGettersWithNullLastEvent(): void
    {
        $deliveryId = new DeliveryUid();
        $deliveryEventId = new DeliveryEventUid();

        // Создаем экземпляр сообщения без необязательного параметра $last
        $message = new DeliveryMessage($deliveryId, $deliveryEventId);

        // Проверяем, что геттеры возвращают правильные объекты
        self::assertSame($deliveryId, $message->getId());
        self::assertInstanceOf(DeliveryUid::class, $message->getId());

        self::assertSame($deliveryEventId, $message->getEvent());
        self::assertInstanceOf(DeliveryEventUid::class, $message->getEvent());

        // Проверяем, что getLast() возвращает null, как и ожидалось
        self::assertNull($message->getLast());
    }
}