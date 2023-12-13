<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


class Amasty_Stripe_Model_Observer_PlaceOrderAfter
{
    /**
     * event: sales_order_payment_place_end
     * scope: global
     *
     * @param Varien_Event_Observer $observer
     */
    public function execute($observer)
    {
        Mage::helper('amstripe')->clearSession();
    }
}
