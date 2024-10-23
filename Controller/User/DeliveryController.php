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
    ): Response
    {

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
