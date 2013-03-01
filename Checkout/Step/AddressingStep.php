<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\SandboxBundle\Process\Step;

use Sylius\Bundle\AddressingBundle\Model\AddressInterface;
use Sylius\Bundle\FlowBundle\Process\Context\ProcessContextInterface;
use Sylius\Bundle\FlowBundle\Process\Step\ControllerStep;

/**
 * The addressing step of checkout.
 * User enters the delivery and shipping address.
 *
 * @author Paweł Jędrzejewski <pjedrzejewski@diweb.pl>
 */
class AddressingCheckoutStep extends ControllerStep
{
    /**
     * {@inheritdoc}
     */
    public function displayAction(ProcessContextInterface $context)
    {
        $form = $this->createCheckoutAddressingForm();

        return $this->render('SyliusSandboxBundle:Frontend/Checkout/Step:addressing.html.twig', array(
            'form'    => $form->createView(),
            'context' => $context
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function forwardAction(ProcessContextInterface $context)
    {
        $request = $this->getRequest();
        $form = $this->createCheckoutAddressingForm();

        if ($request->isMethod('POST') && $form->bindRequest($request)->isValid()) {
            $data = $form->getData();

            $deliveryAddress = $data['deliveryAddress'];
            $billingAddress = $data['billingAddress'];

            $this->saveAddress($deliveryAddress);
            $this->saveAddress($billingAddress);

            $context->getStorage()->set('delivery.address', $deliveryAddress->getId());
            $context->getStorage()->set('billing.address', $billingAddress->getId());

            return $this->complete();
        }

        return $this->render('SyliusSandboxBundle:Frontend/Checkout/Step:addressing.html.twig', array(
            'form'    => $form->createView(),
            'context' => $context
        ));
    }

    private function createCheckoutAddressingForm()
    {
        return $this->createForm('sylius_sandbox_checkout_addressing');
    }

    private function saveAddress(AddressInterface $address)
    {
        $addressManager = $this->get('sylius.manager.address');

        $addressManager->persist($address);
        $addressManager->flush($address);
    }

    private function getAddress($id)
    {
        $addressRepository = $this->container->get('sylius.repository.address');

        return $addressRepository->find($id);
    }
}
