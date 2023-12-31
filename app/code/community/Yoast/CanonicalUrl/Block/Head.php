<?php
/**
 * Yoast
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 *
 * @category   Yoast
 * @package    Yoast_CanonicalUrl
 * @copyright  Copyright (c) 2009 Yoast (http://yoast.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Yoast_CanonicalUrl_Block_Head extends Mage_Page_Block_Html_Head
{

	public function getHeadUrl()
	{
		if (empty($this->_data['urlKey'])) 
		{
			$url = Mage::helper('core/url')->getCurrentUrl();
			$parsedUrl = parse_url($url);

			$scheme = $parsedUrl['scheme'];
			$host = $parsedUrl['host'];
			$port = isset($parsedUrl['port']) ? $parsedUrl['port'] : null;
			$path = $parsedUrl['path'];

			$headUrl = $scheme . '://' . $host . ($port && '80' != $port ? ':' . $port : '') . $path;
			
			if (Mage::getStoreConfig('web/seo/trailingslash')) 
			{
				if (!preg_match('/\\.(rss|html|htm|xml|php?)$/', strtolower($headUrl)) && substr($headUrl, -1) != '/') 
				{
					$headUrl .= '/';
				}
			}
			return $headUrl;
			$this->_data['urlKey'] =$headUrl;
        }
		
		return $this->_data['urlKey'];
	}

	public function getHeadProductUrl()
    {  
		if (empty($this->_data['urlKey'])) 
		{
			$product_id = $this->getRequest()->getParam('id');
			$_item = Mage::getModel('catalog/product')->load($product_id);
			$this->_data['urlKey'] = Mage::getBaseUrl().$_item->getUrlKey().Mage::helper('catalog/product')->getProductUrlSuffix();

		}
		return $this->_data['urlKey'];
	} 
}
