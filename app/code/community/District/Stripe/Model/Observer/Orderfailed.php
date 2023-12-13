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

class District_Stripe_Model_Observer_Orderfailed extends Varien_Event_Observer
{
    /**
     * This an observer function for the event 'sales_model_service_quote_submit_failure'.
     *
     * @param Varien_Event_Observer $event
     */
    public function orderFailed(Varien_Event_Observer $observer)
    {
        try {

            //Get quote object
            $quote = $observer->getEvent()->getQuote();

            if ($quote->getPayment()->getMethodInstance()->getCode() === 'stripe_cc') {

                //Get payment
                $payment = $quote->getPayment();

                //Get failed order model
                $model = Mage::getModel('stripe/order_failed');

                //Get additional info (contains error)
                $info = $payment->getAdditionalInformation();

                //Save failed order
                $model->setOrderId($quote->getReservedOrderId());
                $model->setDate(now());
                $model->setCcType($payment->getCcType());
                $model->setCcLast4($payment->getCcLast4());
                $model->setAmount($quote->getBaseGrandTotal());

                if ($customer = Mage::getSingleton('customer/session')->getCustomer()) {
                    $model->setCustomerId($customer->getId());
                }

                if (isset($info['type'])) {
                    $model->setType($info['type']);
                }

                if (isset($info['code'])) {
                    $model->setCode($info['code']);
                }

                if (isset($info['token'])) {
                    $model->setToken(Mage::helper('core')->encrypt($info['token']));
                }

                if (isset($info['message'])) {
                    $model->setMessage($info['message']);
                }

                $model->save();

            }

        } catch(Exception $e) {
            //Graceful fail
        }
    }
}
