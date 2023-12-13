<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


class Amasty_Stripe_PaymentController extends Mage_Core_Controller_Front_Action
{
    /**
     * Update Payment Intend action
     */
    public function updateAction()
    {
        $checkoutSession = Mage::helper('amstripe')->getSession();
        $quote = $checkoutSession->getQuote();
        $shipping = Mage::helper('amstripe')->prepareShippingAddress($quote);
        $currency = strtolower($quote->getBaseCurrencyCode());

        $result = Mage::getSingleton('amstripe/paymentIntendResource')->update(
            array(
                'amount' => Mage::helper('amstripe')->correctionAmount($quote->getBaseGrandTotal(), $currency),
                'currency' => $currency,
                'receipt_email' => $quote->getCustomerEmail(),
                'shipping' => $shipping
            )
        );

        $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json', true);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result['client_secret']));
    }
}
