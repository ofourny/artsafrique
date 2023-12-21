<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package App for Payments with Stripe
*/

class Amasty_Stripe_Model_PaymentIntendResource
{
    public function getCurrentId()
    {
        return Mage::helper('amstripe')->getStoredPaymentIntendId();
    }

    public function setCurrentIdAndSecret($paymentIntendId, $clientSecret)
    {
        Mage::helper('amstripe')->storePaymentIntendId($paymentIntendId);
        Mage::helper('amstripe')->storePaymentIntendSecret($clientSecret);
    }

    protected function resolveId($paymentIntendId)
    {
        if ($paymentIntendId === null) {
            return $this->getCurrentId();
        }

        return $paymentIntendId;
    }

    /**
     * @param string|null $paymentIntendId
     *
     * @return array
     */
    public function load($paymentIntendId = null)
    {
        $paymentIntendId = $this->resolveId($paymentIntendId);
        return  Mage::getSingleton('amstripe/api')->get(
            'payment_intents/' . $paymentIntendId
        );
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function create($data)
    {
        return Mage::getSingleton('amstripe/api')->call(
            'payment_intents',
            $data
        );
    }

    /**
     * @param array $data
     * @param string|null $paymentIntendId
     *
     * @return array
     */
    public function update($data, $paymentIntendId = null)
    {
        $paymentIntendId = $this->resolveId($paymentIntendId);
        $result = Mage::getSingleton('amstripe/api')->call(
            'payment_intents/' . $paymentIntendId,
            $data
        );
        if (!empty($result['error'])) {
            Mage::log(
                'Stripe update a payment error. Trying to resolve. Cancelling the payment and creating a new one. Error: '
                . $result['error']['message'],
                Zend_Log::CRIT
            );

            Mage::getSingleton('amstripe/paymentIntendResource')->cancel($paymentIntendId, 'duplicate');

            $data += array(
                'capture_method' => 'manual',
                'payment_method_types' => array('card')
                );

            $result = Mage::getSingleton('amstripe/paymentIntendResource')->create(
                $data
            );
            $this->setCurrentIdAndSecret($result['id'], $result['client_secret']);
        }

        return $result;
    }

    /**
     * Possible values are duplicate, fraudulent, requested_by_customer, or abandoned
     *
     * @param string $reason
     * @param string|null $paymentIntendId
     *
     * @return array
     */
    public function cancel($paymentIntendId = null, $reason = 'duplicate')
    {
        $paymentIntendId = $this->resolveId($paymentIntendId);
        if (!in_array($reason, array('duplicate', 'fraudulent', 'requested_by_customer', 'abandoned'))) {
            $reason = 'duplicate';
        }

        return Mage::getSingleton('amstripe/api')->call(
            'payment_intents/' . $paymentIntendId . '/cancel',
            array(
                'cancellation_reason' => $reason
            )
        );
    }
}
