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

namespace BaksDev\Delivery\Forms\Delivery;

use BaksDev\Delivery\Repository\DeliveryChoice\DeliveryChoiceInterface;
use BaksDev\Delivery\Type\Id\DeliveryUid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DeliveryForm extends AbstractType
{
    private DeliveryChoiceInterface $deliveryChoice;

    public function __construct(
        DeliveryChoiceInterface $deliveryChoice
    )
    {
        $this->deliveryChoice = $deliveryChoice;
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => $this->deliveryChoice->findAll(),
            'choice_value' => function(?DeliveryUid $choice) {
                return $choice?->getValue();
            },

            'choice_label' => function(?DeliveryUid $choice) {
                return $choice?->getAttr();
            },

            'choice_attr' => function(DeliveryUid $choice) {
                return [
                    'data-price' => $choice->getPrice()?->getValue(),
                    'data-excess' => $choice->getExcess()?->getValue(),
                    'data-currency' => $choice->getCurrency(),
                ];
            },
            'label' => false,
            'translation_domain' => 'delivery',
            'attr' => ['class' => 'w-100']
            //'expanded' => true,
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'delivery_field';
    }
}