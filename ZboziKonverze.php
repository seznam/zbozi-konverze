<?php


/**
 * Provides access to ZboziKonverze service.
 *
 * Example of usage:
 *
 * \code
 * try {
 *     // Initialize
 *     $zbozi = new ZboziKonverze(ID PROVOZOVNY, "TAJNY KLIC");
 *
 *     // Set order details
 *     $zbozi->setOrder(array(
 *         "orderId" => "CISLO OBJEDNAVKY",
 *         "email" => "email@example.com",
 *         "deliveryType" => "CESKA_POSTA",
 *         "deliveryPrice" => 80,
 *         "otherCosts" => 20,
 *         "paymentType" => "dobírka",
 *     ));
 *
 *     // Add bought items
 *     $zbozi->addCartItem(array(
 *         "itemId" => "1357902468",
 *         "productName" => "Samsung Galaxy S3 (i9300)",
 *         "quantity" => 1,
 *         "unitPrice" => 5000.50,
 *     ));
 *
 *     $zbozi->addCartItem(array(
 *         "itemId" => "2468013579",
 *         "productName" => "BARUM QUARTARIS 165/70 R14 81 T",
 *         "quantity" => 4,
 *         "unitPrice" => 600,
 *     ));
 *
 *     // Finally send request
 *     $zbozi->send();
 *
 * } catch (ZboziKonverzeException $e) {
 *     // Error should be handled according to your preference
 *     error_log("Chyba konverze: " . $e->getMessage());
 * }
 * \endcode
 *
 * @author Zbozi.cz <zbozi@firma.seznam.cz>
 */
class ZboziKonverze {

    /**
     * Endpoint URL
     *
     * @var string BASE_URL
     */
    const BASE_URL = 'https://%%DOMAIN%%/action/%%SHOP_ID%%/conversion/backend';

    /**
     * Private identifier of request creator
     *
     * @var string $PRIVATE_KEY
     */
    public $PRIVATE_KEY;

    /**
     * Public identifier of request creator
     *
     * @var string $SHOP_ID
     */
    public $SHOP_ID;

    /**
     * Identifier of this order
     *
     * @var string $orderId
     */
    public $orderId;

    /**
     * Customer email
     * Should not be set unless customer allows to do so.
     *
     * @var string $email
     */
    public $email;

    /**
     * How the order will be transfered to the customer
     *
     * @var string $deliveryType
     */
    public $deliveryType;

    /**
     * Cost of delivery (in CZK)
     *
     * @var float $deliveryPrice
     */
    public $deliveryPrice;

    /**
     * How the order was paid
     *
     * @var string $paymentType
     */
    public $paymentType;

    /**
     * Other fees (in CZK)
     *
     * @var string $otherCosts
     */
    public $otherCosts;

    /**
     * Array of CartItem
     *
     * @var array $cart
     */
    public $cart = array();

    /**
     * Determine URL where the request will be send to
     *
     * @var boolean $sandbox
     */
    private $sandbox;

    /**
     * Set if sandbox URL will be used.
     *
     * @param boolean $val
     */
    public function useSandbox($val) {
        $this->sandbox = $val;
    }

    /**
     * Check if string is not empty
     *
     * @param string $question String to test
     * @return boolean
     */
    private static function isNullOrEmptyString($question) {
        return (!isset($question) || trim($question)==='');
    }

    /**
     * Initialize ZboziKonverze service
     *
     * @param string $shopId Shop identifier
     * @param string $privateKey Shop private key
     * @throws ZboziKonverzeException can be thrown if \p $privateKey and/or \p $shopId
     * is missing or invalid.
     */
    public function __construct($shopId, $privateKey)
    {
        if ($this::isNullOrEmptyString($shopId)) {
            throw new ZboziKonverzeException('shopId si mandatory');
        } else {
            $this->SHOP_ID = $shopId;
        }

        if ($this::isNullOrEmptyString($privateKey)) {
            throw new ZboziKonverzeException('privateKey si mandatory');
        } else {
            $this->PRIVATE_KEY = $privateKey;
        }

        $this->sandbox = false;
    }

    /**
     * Sets customer email
     *
     * @param string $email Customer email address
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Adds order ID
     *
     * @param int $orderId Order identifier
     */
    public function addOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * Adds ordered product using name
     *
     * @param string $productName Ordered product name
     */
    public function addProduct($productName)
    {
        $item = new CartItem();
        $item->productName = $productName;
        $this->cart[] = $item;
    }

    /**
     * Adds ordered product using item ID
     *
     * @param string $itemId Ordered product item ID
     */
    public function addProductItemId($itemId)
    {
        $item = new CartItem();
        $item->itemId = $itemId;
        $this->cart[] = $item;
    }

    /**
     * Adds ordered product using array which can contains
     * \p productName ,
     * \p itemId ,
     * \p unitPrice ,
     * \p quantity
     *
     * @param array $cartItem Array of various CartItem attributes
     */
    public function addCartItem($cartItem)
    {
        $item = new CartItem();
        if (array_key_exists("productName", $cartItem)) {
            $item->productName = $cartItem["productName"];
        }
        if (array_key_exists("itemId", $cartItem)) {
            $item->itemId = $cartItem["itemId"];
        }
        if (array_key_exists("unitPrice", $cartItem)) {
            $item->unitPrice = $cartItem["unitPrice"];
        }
        if (array_key_exists("quantity", $cartItem)) {
            $item->quantity = $cartItem["quantity"];
        }

        $this->cart[] = $item;
    }

    /**
     * Sets order attributes within
     * \p email ,
     * \p deliveryType ,
     * \p deliveryPrice ,
     * \p orderId ,
     * \p otherCosts ,
     * \p paymentType ,
     *
     * @param array $orderAttributes Array of various order attributes
     */
    public function setOrder($orderAttributes) {
        if (array_key_exists("email", $orderAttributes) && $orderAttributes["email"]) {
            $this->email = $orderAttributes["email"];
        }
        if (array_key_exists("deliveryType", $orderAttributes) && $orderAttributes["deliveryType"]) {
            $this->deliveryType = $orderAttributes["deliveryType"];
        }
        if (array_key_exists("deliveryPrice", $orderAttributes) && $orderAttributes["deliveryPrice"]) {
            $this->deliveryPrice = $orderAttributes["deliveryPrice"];
        }
        $this->orderId = $orderAttributes["orderId"];
        if (array_key_exists("otherCosts", $orderAttributes) && $orderAttributes["otherCosts"]) {
            $this->otherCosts = $orderAttributes["otherCosts"];
        }
        if (array_key_exists("paymentType", $orderAttributes) && $orderAttributes["paymentType"]) {
            $this->paymentType = $orderAttributes["paymentType"];
        }
    }


    /**
     * Creates HTTP request and returns response body
     *
     * @param string $url URL
     * @return boolean true if everything is perfect else throws exception
     * @throws ZboziKonverzeException can be thrown if connection to Zbozi.cz
     * server cannot be established.
     */
    protected function sendRequest($url)
    {
        $encoded_json = json_encode(get_object_vars($this));

        if (extension_loaded('curl'))
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3 /* seconds */);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded_json);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            $response = curl_exec($ch);

            if ($response === false) {
                throw new ZboziKonverzeException('Unable to establish connection to ZboziKonverze service: ' . curl_error($ch));
            }
        }
        else
        {
            // use key 'http' even if you send the request to https://...
            $options = array(
                'http' => array(
                    'header'  => "Content-type: application/json",
                    'method'  => 'POST',
                    'content' => $encoded_json,
                ),
            );
            $context  = stream_context_create($options);
            $response = file_get_contents($url, false, $context);

            if ($response === false) {
                throw new ZboziKonverzeException('Unable to establish connection to ZboziKonverze service');
            }
        }

        $decoded_response = json_decode($response, true);
        if ((int)($decoded_response["status"] / 100) === 2) {
            return true;
        } else {
            throw new ZboziKonverzeException('Request was not accepted: ' . $decoded_response['statusMessage']);
        }
    }

    /**
     * Returns endpoint URL
     *
     * @return string URL where the request will be called
     */
    private function getUrl()
    {
        $url = $this::BASE_URL;
        $url = str_replace("%%SHOP_ID%%", $this->SHOP_ID, $url);

        if ($this->sandbox) {
            $url = str_replace("%%DOMAIN%%", "sandbox.zbozi.cz", $url);
        } else {
            $url = str_replace("%%DOMAIN%%", "www.zbozi.cz", $url);
        }

        return $url;
    }

    /**
     * Sends request to ZboziKonverze service and checks for valid response
     *
     * @return boolean true if everything is perfect else throws exception
     * @throws ZboziKonverzeException can be thrown if connection to Zbozi.cz
     * server cannot be established or mandatory values are missing.
     */
    public function send()
    {
        $url = $this->getUrl();

        // send request and check for valid response
        try {
            $status = $this->sendRequest($url);
            return $status;
        } catch (Exception $e) {
            throw new ZboziKonverzeException($e->getMessage());
        }
    }

};

class CartItem {
    /**
     * Item name
     *
     * @var string $productName
     */
    public $productName;

    /**
     * Item identifier
     *
     * @var string $itemId
     */
    public $itemId;

    /**
     * Price per one item (in CZK)
     *
     * @var float $unitPrice
     */
    public $unitPrice;

    /**
     * Number of items ordered
     *
     * @var int $quantity
     */
    public $quantity;
};

/**
 * Thrown when an service returns an exception
 */
class ZboziKonverzeException extends Exception {};
