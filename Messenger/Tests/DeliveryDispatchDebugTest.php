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

use BaksDev\Delivery\Messenger\DeliveryDispatch;
use BaksDev\Delivery\Messenger\DeliveryMessage;
use BaksDev\Delivery\Type\Event\DeliveryEventUid;
use BaksDev\Delivery\Type\Id\DeliveryUid;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @covers \BaksDev\Delivery\Messenger\DeliveryDispatch
 * @group delivery
 */
#[Group('delivery')]
final class DeliveryDispatchDebugTest extends KernelTestCase
{
    public function testInvoke(): void
    {
        $DeliveryDispatch = self::getContainer()->get(DeliveryDispatch::class);


        // Создаем экземпляр сообщения, которое будет обрабатываться.
        $DeliveryMessage = new DeliveryMessage(new DeliveryUid(), new DeliveryEventUid());

        // Вызываем обработчик с сообщением.
        $DeliveryDispatch($DeliveryMessage);

        // Эта проверка подтверждает, что тест выполнен успешно.
        $this->assertTrue(true, 'The handler should be invokable without errors.');
    }
}
