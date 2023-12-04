<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

namespace BaksDev\Delivery\UseCase\Admin\NewEdit\Price;

use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Measurement\Type\Measurement;
use BaksDev\Reference\Money\Type\Money;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DeliveryPriceForm extends AbstractType
{
	
	public function buildForm(FormBuilderInterface $builder, array $options) : void
	{
		
		$builder->add
		(
			'price',
			MoneyType::class,
			['currency' => false, 'required' => false]
		);
		
		$builder->get('price')->addModelTransformer(
			new CallbackTransformer(
				function($price) {
					return $price instanceof Money ? $price->getValue() : $price;
				},
				function($price) {
					
					return new Money($price);
				}
			)
		);
		
		
		
		$builder->add
		(
			'excess',
			MoneyType::class,
			['currency' => false, 'required' => false]
		);
		
		$builder->get('excess')->addModelTransformer(
			new CallbackTransformer(
				function($excess) {
					return $excess instanceof Money ? $excess->getValue() : $excess;
				},
				function($excess) {
					
					return new Money($excess);
				}
			)
		);
		
		$builder->add
		(
			'currency',
			ChoiceType::class,
			[
				'choices' => Currency::cases(),
				'choice_value' => function(?Currency $currency) {
					return $currency?->getCurrencyValue();
				},
				'choice_label' => function(?Currency $currency) {
					return $currency?->getCurrencyValue();
				},
				'translation_domain' => 'reference.currency',
				'label' => false,
			]
		);
		
	}
	
	
	public function configureOptions(OptionsResolver $resolver) : void
	{
		$resolver->setDefaults([
			'data_class' => DeliveryPriceDTO::class,
		]);
	}
	
}
