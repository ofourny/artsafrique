<?php

/**
 * Copyright (c) 2008-13 Owebia
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @website    http://www.owebia.com/
 * @project    Magento Owebia Shipping 2 module
 * @author     Antoine Lemoine
 * @license    http://www.opensource.org/licenses/MIT  The MIT License (MIT)
**/

class Owebia_Shipping2_Model_Os2_Data_Cart extends Owebia_Shipping2_Model_Os2_Data_Abstract
{
	protected $additional_attributes = array('coupon_code', 'weight_unit', 'weight_for_charge', 'free_shipping');
	protected $_free_shipping;
	protected $_items;
	protected $_options;

	public function __construct($arguments) {
		parent::__construct();
		$request = $arguments['request'];
		$this->_options = $arguments['options'];

		$this->_data = array(
			'price-tax+discount' => $request->getData('package_value_with_discount'),
			'price-tax-discount' => $request->getData('package_value'),
			'weight' => $request->getData('package_weight'),
			'qty' => $request->getData('package_qty'),
		);

		$tax_amount = 0;
		$full_price = 0;
		$cart_items = array();
		$items = $request->getAllItems();
		$quote_total_collected = false;
		foreach ($items as $item) {
			if ($item->getProduct() instanceof Mage_Catalog_Model_Product) {
				switch (get_class($item)) {
					case 'Mage_Sales_Model_Quote_Address_Item':		$key = $item->getQuoteItemId(); break;	// Multishipping
					case 'Mage_Sales_Model_Quote_Item':				$key = $item->getId(); break;			// Onepage checkout
					default: $key = null;
				}
				$cart_items[$key] = $item;
				$tax_amount += $item->getData('tax_amount');
				$full_price += Mage::helper('checkout')->getSubtotalInclTax($item); // ok
			}
		}

		$this->_data['price+tax+discount'] = $tax_amount+$this->_data['price-tax+discount'];
		$this->_data['price+tax-discount'] = $full_price;
		$this->_items = array();
		$bundle_process_children = isset($this->_options['bundle']['process_children']) && $this->_options['bundle']['process_children'];
		foreach ($cart_items as $item) {
			$type = $item->getProduct()->getTypeId();
			//echo $item->getProduct()->getTypeId().', '.$item->getQty().'<br/>';
			if ($type!='configurable') {
				if ($type=='bundle') {
					if ($bundle_process_children) {
						$this->_data['qty'] -= $item->getQty();
						continue;
					}
				}
				$parent_item_id = $item->getData('parent_item_id');
				$parent_item = isset($cart_items[$parent_item_id]) ? $cart_items[$parent_item_id] : null;
				if ($parent_item && $parent_item->getProduct()->getTypeId()=='bundle') {
					if (!$bundle_process_children) continue;
					else $this->_data['qty'] += $item->getQty();
				}
				$this->_items[] = Mage::getModel('owebia-shipping2/Os2_Data_CartItem', array('item' => $item, 'parent_item' => $parent_item, 'options' => $this->_options));
			}
		}
	}

	protected function _load($name) {
		switch ($name) {
			case 'weight_for_charge':
				$weight_for_charge = $this->weight;
				foreach ($this->_items as $item) {
					if ($item->free_shipping) $weight_for_charge -= $item->weight;
				}
				return $weight_for_charge;
			case 'coupon_code':
				$coupon_code = null;
				$session = Mage::getSingleton('checkout/session');
				if ($session && ($quote = $session->getQuote()) && $quote->hasCouponCode() && $quote->getCouponCode()) {
					$coupon_code = $quote->getCouponCode();
				} else { // Pour les commandes depuis Adminhtml
					$session = Mage::getSingleton('adminhtml/session_quote');
					if ($session && ($quote = $session->getQuote()) && $quote->hasCouponCode() && $quote->getCouponCode()) {
						$coupon_code = $quote->getCouponCode();
					}
				}
				return $coupon_code;
			case 'weight_unit':
				return Mage::getStoreConfig('owebia-shipping2/general/weight_unit');
		}
		return parent::_load($name);
	}

	public function __set($name, $value) {
		switch ($name) {
			case 'items':
				return $this->_items = $value;
		}
		return parent::__set($name, $value);
	}

	public function __get($name) {
		switch ($name) {
			case 'items':
				return $this->_items;
			case 'free_shipping':
				if (isset($this->_free_shipping)) return $this->_free_shipping;
				$free_shipping = parent::__get('free_shipping');
				if (!$free_shipping) {
					foreach ($this->_items as $item) {
						$free_shipping = $item->free_shipping;
						if (!$free_shipping) break;
					}
				}
				return $this->_free_shipping = $free_shipping;
		}
		return parent::__get($name);
	}
}

?>