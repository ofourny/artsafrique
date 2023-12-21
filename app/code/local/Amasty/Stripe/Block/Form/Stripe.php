<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package App for Payments with Stripe
*/

class Amasty_Stripe_Block_Form_Stripe extends Mage_Payment_Block_Form
{
    protected $_template = 'amasty/stripe/form/stripe.phtml';

    public function getPublicKey()
    {
        return $this->getMethod()->getConfigData('public_key');
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    protected function getQuote()
    {
        return Mage::helper('amstripe')->getSession()->getQuote();
    }

    /**
     * @return string
     */
    public function getPaymentIntendId()
    {
        $quote = $this->getQuote();
        $shipping = Mage::helper('amstripe')->prepareShippingAddress($quote);
        $currency = strtolower($quote->getBaseCurrencyCode());
        $amount = Mage::helper('amstripe')->correctionAmount($quote->getBaseGrandTotal(), $currency);
        if (!Mage::helper('amstripe')->getStoredPaymentIntendId()) {
            //create
            $result = Mage::getSingleton('amstripe/paymentIntendResource')->create(
                array(
                    'amount' => $amount,
                    'currency' => $currency,
                    'capture_method' => 'manual',
                    'payment_method_types' => array('card'),
                    'receipt_email' => $quote->getCustomerEmail(),
                    'shipping' => $shipping
                )
            );
        } else {
            //update
            $result = Mage::getSingleton('amstripe/paymentIntendResource')->update(
                array(
                    'amount' => $amount,
                    'currency' => $currency,
                    'receipt_email' => $quote->getCustomerEmail(),
                    'shipping' => $shipping
                )
            );
        }
        Mage::helper('amstripe')->storePaymentIntendSecret($result['client_secret']);
        Mage::helper('amstripe')->storePaymentIntendId($result['id']);

        return Mage::helper('amstripe')->getStoredPaymentIntendSecret();
    }

    /**
     * @return array
     */
    public function getPaymentRequestData()
    {
        $quote = $this->getQuote();
        /** @var Mage_Sales_Model_Quote_Address $billing */
        $billing = $quote->getBillingAddress();
        return array(
            'payment_method_data' => array(
                'billing_details' =>  array(
                    'address' =>
                        array (
                            'city' => $billing->getCity(),
                            'country' => $billing->getCountry(),
                            'line1' => $billing->getStreet(1),
                            'line2' => $billing->getStreet(2),
                            'postal_code' => $billing->getPostcode(),
                            'state' => $billing->getRegion(),
                        ),
                    'email' => $billing->getEmail(),
                    'name' => $billing->getName(),
                    'phone' => $billing->getTelephone(),
                )
            )
        );
    }

    /**
     * @return string
     */
    public function getController()
    {
        return Mage::app()->getFrontController()->getRequest()->getControllerName();
    }

    /**
     * @return bool
     */
    public function isIwdOpc()
    {
        return (bool)Mage::helper('core')->isModuleEnabled('IWD_Opc');
    }
}
