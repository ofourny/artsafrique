<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


class Amasty_Stripe_Model_Method_Stripe extends Mage_Payment_Model_Method_Abstract
{
    protected $_isGateway = true;
    protected $_canCapture = true;
    protected $_canOrder = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canAuthorize = true;
    protected $_code = 'amasty_stripe';
    protected $_formBlockType = 'amstripe/form_stripe';
    protected $_infoBlockType = 'amstripe/info_stripe';

    /**
     * @param mixed $data
     * @return $this|Mage_Payment_Model_Info
     * @throws Mage_Core_Exception
     */
    public function assignData($data)
    {
        parent::assignData($data);

        /** @var Mage_Payment_Model_Info $infoInstance */
        $infoInstance = $this->getInfoInstance();

        if ($secret = Mage::helper('amstripe')->getStoredPaymentIntendSecret()) {
            $infoInstance->setAdditionalInformation('stripe_secret', $secret);
            $secret = Mage::helper('amstripe')->getStoredPaymentIntendId();
            $infoInstance->setAdditionalInformation('stripe_payment_intend_id', $secret);
        }

        return $this;
    }

    /**
     * @return Mage_Payment_Model_Abstract
     * @throws Mage_Core_Exception
     */
    public function validate()
    {
        /** @var Mage_Payment_Model_Info $infoInstance */
        $infoInstance = $this->getInfoInstance();
        if (!$infoInstance->getAdditionalInformation('stripe_secret')
            || !$infoInstance->getAdditionalInformation('stripe_payment_intend_id')
        ) {
            Mage::throwException(Mage::helper('amstripe')->__('Access token is not valid'));
        }

        return parent::validate();
    }

    /**
     * @return string
     */
    public function getConfigPaymentAction()
    {
        return  Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE;
    }

    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param float $amount
     *
     * @return void
     *
     * @throws Exception
     * @throws Mage_Core_Exception
     */
    public function capture(Varien_Object $payment, $amount)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $payment->getOrder();
        $paymentIntendId = $this->getInfoInstance()->getAdditionalInformation('stripe_payment_intend_id');
        $currency = $order->getBaseCurrencyCode();
        $amount = Mage::helper('amstripe')->correctionAmount($amount, $currency);

        $result = Mage::getSingleton('amstripe/api')->call(
            'payment_intents/' . $paymentIntendId . '/capture',
            array(
                'amount_to_capture' => $amount
            )
        );

        if (!isset($result['status']) || $result['status'] != 'succeeded') {
            $message = '';
            if ($result['error']['message']) {
                $message = ' ' . $result['error']['message'];
            }
            $order->setState(
                Mage::getStoreConfig('payment/amasty_stripe/fail_status', $order->getStore()),
                true,
                Mage::helper('amstripe')->__('Stripe: payment processing error.') . $message
            )->save();

            Mage::throwException(
                Mage::helper('amstripe')->__('Transaction failed for order %s', $order->getIncrementId()) . $message
            );
        }

        $this->fillStripeDetails($order);

        $order->setState(
            Mage::getStoreConfig('payment/amasty_stripe/success_status', $order->getStore()),
            true,
            Mage::helper('amstripe')->__('Stripe: paid successfully.')
        );

        $payment->setTransactionId($result['charges']['data'][0]['id']);
    }

    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @return bool
     */
    protected function fillStripeDetails(Mage_Sales_Model_Order $order)
    {
        $billing = $order->getBillingAddress();

        $paymentIntendId = $this->getInfoInstance()->getAdditionalInformation('stripe_payment_intend_id');
        if (!$billing || !$paymentIntendId) {
            return false;
        }
        Mage::getSingleton('amstripe/api')->call(
            'payment_intents/' . $paymentIntendId,
            array(
                'description' => "{$order->getIncrementId()}, {$order->getCustomerEmail()}",
            )
        );

        return true;
    }

    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param float $amount
     * @return Mage_Payment_Model_Abstract|void
     * @throws Varien_Exception
     * @throws Mage_Core_Exception
     */
    public function refund(Varien_Object $payment, $amount)
    {
        $order = $payment->getOrder();

        $transactionId = $payment->getParentTransactionId();
        $currency = $order->getBaseCurrencyCode();
        $amount = Mage::helper('amstripe')->correctionAmount($amount, $currency);
        $result = Mage::getSingleton('amstripe/api')->call('refunds', array(
            'charge' => $transactionId,
            'amount' => $amount,
        ));

        if ($result['status'] != 'succeeded') {
            $message = '';
            if ($result['error']['message']) {
                $message = ' ' . $result['error']['message'];
            }
            Mage::throwException(
                Mage::helper('amstripe')->__('Transaction failed for order %s', $order->getIncrementId()) . $message
            );
        }

        $payment
            ->setTransactionId($transactionId . '-' . Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND)
            ->setParentTransactionId($transactionId)
            ->setIsTransactionClosed(1)
            ->setShouldCloseParentTransaction(1);

        return $this;
    }

    /**
     * Set transaction ID into creditmemo for informational purposes
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Mage_Payment_Model_Method_Abstract
     */
    public function processCreditmemo($creditmemo, $payment)
    {
        $creditmemo->setTransactionId($payment->getParentTransactionId());

        return $this;
    }
}
