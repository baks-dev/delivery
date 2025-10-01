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

namespace BaksDev\Delivery\UseCase\Admin\NewEdit;

use BaksDev\Reference\Region\Repository\ReferenceRegionChoice\ReferenceRegionChoiceInterface;
use BaksDev\Reference\Region\Type\Id\RegionUid;
use BaksDev\Users\Profile\TypeProfile\Repository\TypeProfileChoice\TypeProfileChoiceInterface;
use BaksDev\Users\Profile\TypeProfile\Type\Id\TypeProfileUid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DeliveryForm extends AbstractType
{
    public function __construct(
        private readonly TypeProfileChoiceInterface $TypeProfileChoiceRepository,
        private readonly ReferenceRegionChoiceInterface $ReferenceRegionChoiceRepository
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** Профиль пользователя, которому доступна доставка (null - все) */

        $profileChoice = $this->TypeProfileChoiceRepository->getAllTypeProfileChoice();

        $builder
            ->add('type', ChoiceType::class, [
                'choices' => $profileChoice,
                'choice_value' => function(?TypeProfileUid $type) {
                    return $type?->getValue();
                },
                'choice_label' => function(TypeProfileUid $type) {
                    return $type->getAttr();
                },
                'label' => false,
                'expanded' => false,
                'multiple' => false,
                'required' => false,
            ]);


        $regionChoice = $this->ReferenceRegionChoiceRepository->getRegionChoice();

        $builder
            ->add('region', ChoiceType::class, [
                'choices' => $regionChoice,
                'choice_value' => function(?RegionUid $region) {
                    return $region?->getValue();
                },
                'choice_label' => function(RegionUid $region) {
                    return $region->getOption();
                },
                'label' => false,
                'expanded' => false,
                'multiple' => false,
                'required' => false,
            ]);


        /** Обложка способа оплаты */

        $builder->add('cover', Cover\DeliveryCoverForm::class);

        $builder->add('price', Price\DeliveryPriceForm::class);


        /** Настройки локали службы доставки */

        $builder->add('translate', CollectionType::class, [
            'entry_type' => Trans\DeliveryTransForm::class,
            'entry_options' => ['label' => false],
            'label' => false,
            'by_reference' => false,
            'allow_delete' => true,
            'allow_add' => true,
            'prototype_name' => '__delivery_translate__',
        ]);


        /** Настройки локали службы доставки */

        $builder->add('field', CollectionType::class, [
            'entry_type' => Fields\DeliveryFieldForm::class,
            'entry_options' => ['label' => false],
            'label' => false,
            'by_reference' => false,
            'allow_delete' => true,
            'allow_add' => true,
            'prototype_name' => '__delivery_field__',
        ]);


        /** Сортировка поля в секции */

        $builder->add(
            'sort',
            IntegerType::class,
            [
                'label' => false,
                'attr' => ['min' => 0, 'max' => 999],
            ],
        );


        /** Флаг активности */

        $builder->add(
            'active',
            CheckboxType::class,
            [
                'label' => false,
                'required' => false,
            ],
        );

        /** Сохранить */

        $builder->add(
            'delivery',
            SubmitType::class,
            ['label' => 'Save', 'label_html' => true, 'attr' => ['class' => 'btn-primary']],
        );
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DeliveryDTO::class,
        ]);
    }

}
