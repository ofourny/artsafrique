<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


class Amasty_Stripe_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @param string $currency
     * @return bool
     */
    private function isZeroDecimal($currency)
    {
        return in_array(strtolower($currency), array(
            'bif', 'djf', 'jpy', 'krw', 'pyg', 'vnd', 'xaf',
            'xpf', 'clp', 'gnf', 'kmf', 'mga', 'rwf', 'vuv', 'xof'));
    }

    /**
     * @param float $amount
     * @param string $currency
     * @return float
     */
    public function correctionAmount($amount, $currency)
    {
        $cents = 100;

        if ($this->isZeroDecimal($currency)) {
            $cents = 1;
        }

        return round($amount * $cents);
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return array
     */
    public function prepareShippingAddress($quote)
    {
        if ($quote->isVirtual()) {
            return null;
        }
        $address = $quote->getShippingAddress();
        return array(
            'address' =>
                array (
                    'city' => $address->getCity(),
                    'country' => $address->getCountry(),
                    'line1' => $address->getStreet(1),
                    'line2' => $address->getStreet(2),
                    'postal_code' => $address->getPostcode(),
                    'state' => $address->getRegion(),
                ),
            'carrier' => $address->getShippingMethod(),
            'name' => $address->getName(),
            'phone' => $address->getTelephone(),
        );
    }
    /**
     * @return string
     */
    public function getStoredPaymentIntendSecret()
    {
        return $this->getSession()->getData('amasty_stripe_secret');
    }

    /**
     * @param string $secret
     *
     * @return $this
     */
    public function storePaymentIntendSecret($secret)
    {
        $this->getSession()->setData('amasty_stripe_secret', $secret);
        return $this;
    }

    /**
     * @return string
     */
    public function getStoredPaymentIntendId()
    {
        return $this->getSession()->getData('amasty_stripe_pi_id');
    }

    /**
     * @param string $intendId
     *
     * @return $this
     */
    public function storePaymentIntendId($intendId)
    {
        $this->getSession()->setData('amasty_stripe_pi_id', $intendId);
        return $this;
    }

    /**
     * @return $this
     */
    public function clearSession()
    {
        $this->getSession()->unsetData('amasty_stripe_pi_id')
            ->unsetData('amasty_stripe_secret');
        return $this;
    }

    /**
     * @return Mage_Adminhtml_Model_Session_Quote|Mage_Checkout_Model_Session|Mage_Core_Model_Abstract
     */
    public function getSession()
    {
        if (Mage::app()->getStore()->isAdmin()) {
            return Mage::getSingleton('adminhtml/session_quote');
        }

        return Mage::getSingleton('checkout/session');
    }
}
