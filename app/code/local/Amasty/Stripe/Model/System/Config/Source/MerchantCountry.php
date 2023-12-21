<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package App for Payments with Stripe
*/

class Amasty_Stripe_Model_System_Config_Source_MerchantCountry
{
    protected $allowedCountries = array(
        'AT', 'AU', 'BE', 'BR', 'CA', 'CH', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GB', 'HK', 'IE', 'IN', 'IT', 'JP',
        'LT', 'LU', 'LV', 'MX', 'NL', 'NZ', 'NO', 'PH', 'PL', 'PT', 'RO', 'SE', 'SG', 'SK', 'US'
    );

    protected $_options;

    public function toOptionArray()
    {
        if (!$this->_options) {
            $countryCollection = Mage::getResourceModel('directory/country_collection')
                ->addCountryCodeFilter($this->allowedCountries)
                ->loadData();

            $this->_options = $countryCollection->toOptionArray(false);
        }

        return $this->_options;
    }
}
