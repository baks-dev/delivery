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

namespace BaksDev\Delivery\Controller\User;

use BaksDev\Contacts\Region\Repository\ContactCallByRegion\ContactCallByRegionInterface;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Form\Search\SearchForm;
use BaksDev\Delivery\Forms\RegionFilter\RegionFilterDTO;
use BaksDev\Delivery\Forms\RegionFilter\RegionFilterForm;
use BaksDev\Delivery\Repository\AllDeliveryDetail\DeliveryByTypeProfileInterface;
use BaksDev\Delivery\Repository\DeliveryRegionDefault\DeliveryRegionDefaultInterface;
use BaksDev\Users\Profile\TypeProfile\Repository\AllProfileType\AllProfileTypeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class DeliveryController extends AbstractController
{
    #[Route('/delivery', name: 'user.delivery')]
    public function index(
        Request $request,
        AllProfileTypeInterface $allTypeProfile, // Типы профилей
        DeliveryByTypeProfileInterface $delivery,
        DeliveryRegionDefaultInterface $defaultRegion,
        ContactCallByRegionInterface $callRegion,
    ): Response {

        $profiles = $allTypeProfile->getTypeProfile();

        $RegionFilterDTO = new RegionFilterDTO();
        $DefaultRegion = $defaultRegion->getDefaultDeliveryRegion();
        $RegionFilterDTO->setRegion($DefaultRegion);

        // Форма
        $form = $this->createForm(RegionFilterForm::class, $RegionFilterDTO);
        $form->handleRequest($request);

        /** Способы доставки согласно профилю пользователя */
        $delivers = null;

        if($profiles)
        {
            foreach($profiles as $profile)
            {
                $delivers[(string) $profile['id']] =
                    $delivery->fetchAllDeliveryAssociative($profile['id'], $RegionFilterDTO->getRegion());
            }
        }


        /** Пункты выдачи товаров */
        $calls =
            $callRegion->fetchContactCallByRegionAssociative($RegionFilterDTO->getRegion(), true);


        // Поиск по всему сайту
        $allSearch = new SearchDTO($request);
        $allSearchForm = $this->createForm(SearchForm::class, $allSearch, [
            'action' => $this->generateUrl('delivery:user.delivery'),
        ]);

        // 'all_search' => $allSearchForm->createView(),
        return $this->render([
            'profiles' => $profiles,
            'delivers' => $delivers,
            'calls' => $calls,
            'form' => $form->createView(),
            'all_search' => $allSearchForm->createView(),
        ]);
    }
}
