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

namespace BaksDev\Delivery\Repository\FieldByDeliveryChoice;

use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Delivery\Entity\Delivery;
use BaksDev\Delivery\Entity\Fields\DeliveryField;
use BaksDev\Delivery\Entity\Fields\Trans\DeliveryFieldTrans;
use BaksDev\Delivery\Type\Field\DeliveryFieldUid;
use BaksDev\Delivery\Type\Id\DeliveryUid;
use BaksDev\Orders\Order\Repository\FieldByDeliveryChoice\FieldByDeliveryChoiceInterface;

final class FieldByDeliveryChoiceRepository implements FieldByDeliveryChoiceInterface
{
    private ORMQueryBuilder $ORMQueryBuilder;

    public function __construct(ORMQueryBuilder $ORMQueryBuilder)
    {
        $this->ORMQueryBuilder = $ORMQueryBuilder;
    }


    public function fetchDeliveryFields(DeliveryUid $delivery): ?array
    {
        $qb = $this->ORMQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $select = sprintf('new %s(field.id, trans.name, trans.description, field.type, field.required)', DeliveryFieldUid::class);

        $qb->select($select);

        $qb
            ->from(Delivery::class, 'delivery', 'delivery.id')
            ->where('delivery.id = :delivery')
            ->setParameter('delivery', $delivery, DeliveryUid::TYPE);

        $qb->join(
            DeliveryField::class,
            'field',
            'WITH',
            'field.event = delivery.event'
        );

        $qb->leftJoin(
            DeliveryFieldTrans::class,
            'trans',
            'WITH',
            'trans.field = field.id AND trans.local = :local'
        );


        $qb->orderBy('field.sort');

        return $qb->enableCache('delivery', 86400)->getResult();
    }

}
