<?php
class Smartwave_Filterproducts_Block_Filterproducts extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
    	var_dump('her');exit;
		return parent::_prepareLayout();
    }
    
     public function getCatalogextensions()     
     { 
        if (!$this->hasData('filterproducts')) {
            $this->setData('filterproducts', Mage::registry('filterproducts'));
        }
        return $this->getData('filterproducts');
        
    }
} 