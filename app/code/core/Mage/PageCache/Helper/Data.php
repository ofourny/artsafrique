<?php
/**
 * OpenMage
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @package    Mage_PageCache
 * @copyright  Copyright (c) 2006-2020 Magento, Inc. (https://www.magento.com)
 * @copyright  Copyright (c) 2022 The OpenMage Contributors (https://www.openmage.org)
 * @license    https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Page cache data helper
 *
 * @category   Mage
 * @package    Mage_PageCache
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_PageCache_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Pathes to external cache config options
     */
    public const XML_PATH_EXTERNAL_CACHE_ENABLED  = 'system/external_page_cache/enabled';
    public const XML_PATH_EXTERNAL_CACHE_LIFETIME = 'system/external_page_cache/cookie_lifetime';
    public const XML_PATH_EXTERNAL_CACHE_CONTROL  = 'system/external_page_cache/control';

    /**
     * Path to external cache controls
     */
    public const XML_PATH_EXTERNAL_CACHE_CONTROLS = 'global/external_cache/controls';

    /**
     * Cookie name for disabling external caching
     *
     * @var string
     */
    public const NO_CACHE_COOKIE = 'external_no_cache';

    protected $_moduleName = 'Mage_PageCache';

    /**
     * Check whether external cache is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_EXTERNAL_CACHE_ENABLED);
    }

    /**
     * Return all available external cache controls
     *
     * @return array
     */
    public function getCacheControls()
    {
        $controls = Mage::app()->getConfig()->getNode(self::XML_PATH_EXTERNAL_CACHE_CONTROLS);
        return $controls->asCanonicalArray();
    }

    /**
     * Initialize proper external cache control model
     *
     * @throws Mage_Core_Exception
     * @return Mage_PageCache_Model_Control_Interface
     */
    public function getCacheControlInstance()
    {
        $usedControl = Mage::getStoreConfig(self::XML_PATH_EXTERNAL_CACHE_CONTROL);
        if ($usedControl) {
            foreach ($this->getCacheControls() as $control => $info) {
                if ($control == $usedControl && !empty($info['class'])) {
                    return Mage::getSingleton($info['class']);
                }
            }
        }
        Mage::throwException($this->__('Failed to load external cache control'));
    }

    /**
     * Disable caching on external storage side by setting special cookie
     */
    public function setNoCacheCookie()
    {
        $cookie   = Mage::getSingleton('core/cookie');
        $lifetime = Mage::getStoreConfig(self::XML_PATH_EXTERNAL_CACHE_LIFETIME);
        $noCache  = $cookie->get(self::NO_CACHE_COOKIE);

        if ($noCache) {
            $cookie->renew(self::NO_CACHE_COOKIE, $lifetime);
        } else {
            $cookie->set(self::NO_CACHE_COOKIE, 1, $lifetime);
        }
    }

    /**
     * Returns a lifetime of cookie for external cache
     *
     * @return string Time in seconds
     */
    public function getNoCacheCookieLifetime()
    {
        return Mage::getStoreConfig(self::XML_PATH_EXTERNAL_CACHE_LIFETIME);
    }
}
