<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


class Amasty_Stripe_Model_Api extends Mage_Core_Helper_Abstract
{
    const API_BASE_URL = 'https://api.stripe.com/v1/';
    const API_VERSION = '2019-09-09';

    protected $privateKey;

    public function __construct()
    {
        $this->privateKey = trim(Mage::getStoreConfig('payment/amasty_stripe/private_key'));
    }

    public function get($method)
    {
        return $this->call($method, array(), true);
    }

    /**
     * @param string $method
     * @param array  $data
     * @param bool $isGet
     *
     * @return mixed
     * @throws Exception
     */
    public function call($method, $data, $isGet = false)
    {
        if (!$this->privateKey) {
            throw new Exception('API private key is not specified');
        }

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, self::API_BASE_URL . $method);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        if ($isGet) {
            curl_setopt($curl, CURLOPT_HTTPGET, 1);
        } else {
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($curl, CURLOPT_POST, 1);
        }

        curl_setopt($curl, CURLOPT_USERPWD, $this->privateKey . ':');

        $headers = array();

        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        $headers[] = 'Stripe-Version: ' . self::API_VERSION;
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($curl);

        if (curl_errno($curl)) {
            throw new Exception('API request failed: ' . curl_error($curl));
        }

        curl_close($curl);

        return json_decode($result, true);
    }
}
