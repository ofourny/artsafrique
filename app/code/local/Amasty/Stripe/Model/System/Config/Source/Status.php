<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package App for Payments with Stripe
*/

class Amasty_Stripe_Model_System_Config_Source_Status
{
    public function toOptionArray()
    {
        return array(
            Mage_Sales_Model_Order::STATE_PENDING_PAYMENT => Mage::helper('amstripe')->__('Pending'),
            Mage_Sales_Model_Order::STATE_PROCESSING => Mage::helper('amstripe')->__('Processing'),
        );
    }
}
