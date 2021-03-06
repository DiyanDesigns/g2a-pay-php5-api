<?php

/**
 * @author: Martin Liprt
 * @email: tuxxx128@gmail.com
 */

namespace Tuxxx128\G2aPay;

class G2aPayApi implements IG2aPay
{
    /** @var string */
    private $apiHash;

    /** @var string */
    private $secretKey;

    /** @var string */
    private $urlSuccess;

    /** @var string */
    private $urlFail;

    /** @var boolean */
    private $isProduction;

    /** @var integer */
    private $orderId;

    /** @var integer */
    private $totalPrice;

    /** @var string  */
    private $redirectUrlOnGateway;

    /** @var string */
    private $email;

    /** @var string */
    private $merchantEmail;

    /** @var array */
    private $items = [];

    /** @var string */
    public $currency = 'EUR';

    public function __construct($apiHash, $secretKey, $isProduction = false,
                                $merchantEmail = null)
    {
        $this->apiHash       = $apiHash;
        $this->secretKey     = $secretKey;
        $this->isProduction  = $isProduction;
        $this->merchantEmail = $merchantEmail;
    }

    /**
     * Check is mode in product environment?
     * @return boolean
     */
    public function checkIsProductionEnvironment()
    {
        return (boolean) $this->isProduction;
    }

    /**
     * Check is mode in test environment?
     * @return boolean
     */
    public function checkIsTestEnvironment()
    {
        return (boolean) !$this->isProduction;
    }

    /**
     * Get API URL for checkout
     * @param boolean $mode
     * @return string
     */
    public function getApIendpointUrl($mode)
    {
        if ($mode) {
            return IG2aPay::CHECKOUT_PRODUCTION_URL;
        }

        return IG2aPay::CHECKOUT_TEST_URL;
    }


    /**
     * Build calculate hash - Hash is a string generated by hashing certain payment details using the SHA256 algorithm
     * {userOrderId}{amount}{currency}{ApiSecret}
     * @return string
     */
    private function calculateOrderHash()
    {
        return hash('sha256',
            $this->orderId.round($this->totalPrice, 2).$this->currency.$this->secretKey);
    }

    /**
     * Build IPN calculate hash - Hash is a string generated by hashing certain payment details using the SHA256 algorithm
     * {transactionId}{userOrderId}{amount}{ApiSecret}
     * @return string
     */
    public function calculateIpnHash($transactionId, $orderId, $amount)
    {
        return hash('sha256',
            $transactionId.$orderId.(number_format($amount, 2, '.', '') + 0).$this->secretKey);
    }

    /**
     * Get base fields for make authorized http request.
     * @return array
     */
    private function getAuthorizeHeadFields()
    {
        $authorizeHead = [
            'apiHash' => $this->apiHash,
            'hash' => hash('sha256', $this->apiHash.$this->merchantEmail.$this->secretKey),
        ];

        return $authorizeHead;
    }

    /**
     * Get complete detail of payment by transaction ID
     * @param string $transactionId
     */
    public function getPaymentDetailById($transactionId)
    {
        return $this->buildAuthorizedHttpRequest('/transactions/'.$transactionId,
                [], false);
    }

    /**
     * Build simple CURL http request to resource like authorized API user.
     * @param string $uri
     * @param array $fields
     * @param boolean $post
     * @return mixed
     */
    private function buildAuthorizedHttpRequest($uri, array $fields = [],
                                                $post = true, array $header = [])
    {
        $ch  = curl_init();
        $url = IG2aPay::REST_TEST_URL;
       
        if ($this->checkIsProductionEnvironment()) {
             $url = IG2aPay::REST_PRODUCTION_URL;
        }

        // authorized header
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: '.$this->getAuthorizeHeadFields()['apiHash'].'; '.$this->getAuthorizeHeadFields()['hash'],
        ] + $header);

        curl_setopt($ch, CURLOPT_URL, $url.$uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($post) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        }

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response);

        return $result;
    }

    /**
     * Build HTTP request for get redirect URL to gateway with CURL lib
     * @return boolean
     */
    public function buildHttpRequest()
    {
        $ch = curl_init();

        $fields = [
            'api_hash' => $this->apiHash,
            'order_id' => $this->orderId,
            'hash' => $this->calculateOrderHash(),
            'amount' => $this->totalPrice,
            'currency' => $this->currency,
            'url_ok' => $this->urlSuccess,
            'url_failure' => $this->urlFail,
            'items' => $this->items,
        ];

        if ($this->email) { // customer email
            $fields['email'] = $this->email;
        }

        $url = $this->getApIendpointUrl($this->checkIsProductionEnvironment());

        curl_setopt($ch, CURLOPT_URL, $url.'/index/createQuote');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response);

        if (!isset($result->token)) {
           $result = var_export($result, true); 
           throw new G2aPayException('Something is wrong, returned token is invalid, please check your sent parameters. ' . $result);
        }

        $this->redirectUrlOnGateway = $url.'/index/gateway?token='.$result->token;

        return $this->redirectUrlOnGateway;
    }

    /**
     * Just add item to item list
     * @return array
     */
    public function addItem(G2aPayItem $item)
    {
        $this->items[] = $item->getData();
        $this->totalPrice += $item->amount;

        return $this;
    }

    /**
     * Add item with negative amount for discount, discount is set over percents.
     * @param \Tuxxx128\G2aPay\G2aPayItem $item
     * @param integer $percent
     */
    public function addPercentDiscountItem(G2aPayItem $item, $percent)
    {
        $discount = $this->totalPrice / 100;
        $discount *= $percent;

        $item->price = -$discount;

        $this->addItem($item);
    }

    /**
     * Add item with negative amount for discount, discount is set over fixed amount.
     * @param \Tuxxx128\G2aPay\G2aPayItem $item
     * @param integer $amount
     */
    public function addAmountDiscountItem(G2aPayItem $item, $amount)
    {
        $item->price = -(abs($amount));

        $this->addItem($item);
    }

    /**
     * Set order Id
     * @param integer $id
     * @return self
     */
    public function setOrderId($id)
    {
        $this->orderId = $id;

        return $this;
    }

    /**
     * Get order Id
     * @return integer
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * Get items list
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    public function getRedirectUrlOnGateway()
    {
        if ($this->redirectUrlOnGateway) {
            return $this->redirectUrlOnGateway;
        }

        return $this->buildHttpRequest();
    }

    /**
     * Set Success URL
     * @return self
     */
    public function setUrlSuccess($val)
    {
        $this->urlSuccess = $val;

        return $this;
    }

    /**
     * Get Success URL
     * @return string
     */
    public function getUrlSuccess()
    {
        return $this->urlSuccess;
    }

    /**
     * Set Fail URL
     * @return self
     */
    public function setUrlFail($val)
    {
        $this->urlFail = $val;

        return $this;
    }

    /**
     * Get Fail URL
     * @return string
     */
    public function getUrlFail()
    {
        return $this->urlFail;
    }

    /**
     * Set customer email
     * @return self
     */
    public function setEmail($val)
    {
        $this->email = $val;

        return $this;
    }

    /**
     * Get customer email
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set currency code
     * @return self
     */
    public function setCurrency($val)
    {
        $this->currency = $val;

        return $this;
    }

    /**
     * Get currency code
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Get API hash
     * @return string
     */
    public function getApiHash()
    {
        return $this->apiHash;
    }

    /**
     * Get total price of all items
     * @return integer
     */
    public function getTotalPrice()
    {
        return $this->totalPrice;
    }

    /**
     * Get API secret key
     * @return string
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }
}
