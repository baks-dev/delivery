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

namespace BaksDev\Delivery\Controller\Admin;


use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Delivery\Entity\Delivery;
use BaksDev\Delivery\Entity\Event\DeliveryEvent;
use BaksDev\Delivery\UseCase\Admin\Delete\DeliveryDeleteDTO;
use BaksDev\Delivery\UseCase\Admin\Delete\DeliveryDeleteForm;
use BaksDev\Delivery\UseCase\Admin\Delete\DeliveryDeleteHandler;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
#[RoleSecurity('ROLE_DELIVERY_DELETE')]
final class DeleteController extends AbstractController
{
    #[Route('/admin/delivery/delete/{id}', name: 'admin.delete', methods: ['GET', 'POST'])]
    public function delete(
        Request $request,
        #[MapEntity] DeliveryEvent $DeliveryEvent,
        DeliveryDeleteHandler $DeliveryDeleteHandler,
    ): Response
    {
        $DeliveryDeleteDTO = new DeliveryDeleteDTO();
        $DeliveryEvent->getDto($DeliveryDeleteDTO);
        $form = $this->createForm(DeliveryDeleteForm::class, $DeliveryDeleteDTO, [
            'action' => $this->generateUrl('delivery:admin.delete', ['id' => $DeliveryDeleteDTO->getEvent()]),
        ]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('delivery_delete'))
        {
            $handle = $DeliveryDeleteHandler->handle($DeliveryDeleteDTO);

            $this->addFlash
            (
                'admin.page.delete',
                $handle instanceof Delivery ? 'admin.success.delete' : 'admin.danger.delete',
                'delivery.admin',
                $handle
            );

            return $this->redirectToRoute('delivery:admin.index');
        }

        return $this->render([
            'form' => $form->createView(),
            'name' => $DeliveryEvent->getNameByLocale($this->getLocale()), // название согласно локали
        ]);
    }
}
