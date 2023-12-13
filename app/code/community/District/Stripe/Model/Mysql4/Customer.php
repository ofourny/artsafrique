<?php
/**
 * District Commerce
 *
 * @category    District
 * @package     Stripe
 * @author      District Commerce <support@districtcommerce.com>
 * @copyright   Copyright (c) 2016 District Commerce (http://districtcommerce.com)
 * @license     http://store.districtcommerce.com/license
 * 
 */

class District_Stripe_Model_Mysql4_Customer extends Mage_Core_Model_Mysql4_Abstract
{
  protected function _construct()
  {
    $this->_init('stripe/customer', 'id');
  }
}