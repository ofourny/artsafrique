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
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2006-2020 Magento, Inc. (https://www.magento.com)
 * @copyright  Copyright (c) 2019-2022 The OpenMage Contributors (https://www.openmage.org)
 * @license    https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml form container block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Widget_Form_Container extends Mage_Adminhtml_Block_Widget_Container
{
    protected $_objectId = 'id';
    protected $_formScripts = [];
    protected $_formInitScripts = [];
    protected $_mode = 'edit';
    protected $_blockGroup = 'adminhtml';

    public function __construct()
    {
        parent::__construct();

        if (!$this->hasData('template')) {
            $this->setTemplate('widget/form/container.phtml');
        }

        $this->_addButton('back', [
            'label'     => Mage::helper('adminhtml')->__('Back'),
            'onclick'   => 'setLocation(\'' . $this->getBackUrl() . '\')',
            'class'     => 'back',
        ], -1);
        $this->_addButton('reset', [
            'label'     => Mage::helper('adminhtml')->__('Reset'),
            'onclick'   => 'setLocation(window.location.href)',
        ], -1);

        $objId = $this->getRequest()->getParam($this->_objectId);

        if (! empty($objId)) {
            $this->_addButton('delete', [
                'label'     => Mage::helper('adminhtml')->__('Delete'),
                'class'     => 'delete',
                'onclick'   => 'deleteConfirm(\''
                    . Mage::helper('core')->jsQuoteEscape(
                        Mage::helper('adminhtml')->__('Are you sure you want to do this?')
                    )
                    . '\', \''
                    . $this->getDeleteUrl()
                    . '\')',
            ]);
        }

        $this->_addButton('save', [
            'label'     => Mage::helper('adminhtml')->__('Save'),
            'onclick'   => 'editForm.submit();',
            'class'     => 'save',
        ], 1);
    }

    protected function _prepareLayout()
    {
        if ($this->_blockGroup && $this->_controller && $this->_mode) {
            $this->setChild('form', $this->getLayout()->createBlock($this->_blockGroup
                . '/'
                . $this->_controller
                . '_'
                . $this->_mode
                . '_form'));
        }
        return parent::_prepareLayout();
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/');
    }

    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', [
            $this->_objectId => $this->getRequest()->getParam($this->_objectId),
            Mage_Core_Model_Url::FORM_KEY => $this->getFormKey()
        ]);
    }

    /**
     * Get form save URL
     *
     * @deprecated
     * @see getFormActionUrl()
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getFormActionUrl();
    }

    /**
     * Get form action URL
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        if ($this->hasFormActionUrl()) {
            return $this->getData('form_action_url');
        }
        return $this->getUrl('*/' . $this->_controller . '/save');
    }

    public function getFormHtml()
    {
        $this->getChild('form')->setData('action', $this->getSaveUrl());
        return $this->getChildHtml('form');
    }

    public function getFormInitScripts()
    {
        if (!empty($this->_formInitScripts) && is_array($this->_formInitScripts)) {
            return '<script type="text/javascript">' . implode("\n", $this->_formInitScripts) . '</script>';
        }
        return '';
    }

    public function getFormScripts()
    {
        if (!empty($this->_formScripts) && is_array($this->_formScripts)) {
            return '<script type="text/javascript">' . implode("\n", $this->_formScripts) . '</script>';
        }
        return '';
    }

    public function getHeaderWidth()
    {
        return '';
    }

    public function getHeaderCssClass()
    {
        return 'icon-head head-' . strtr($this->_controller, '_', '-');
    }

    public function getHeaderHtml()
    {
        return '<h3 class="' . $this->getHeaderCssClass() . '">' . $this->getHeaderText() . '</h3>';
    }

    /**
     * Set data object and pass it to form
     *
     * @param Varien_Object $object
     * @return $this
     */
    public function setDataObject($object)
    {
        $this->getChild('form')->setDataObject($object);
        return $this->setData('data_object', $object);
    }
}