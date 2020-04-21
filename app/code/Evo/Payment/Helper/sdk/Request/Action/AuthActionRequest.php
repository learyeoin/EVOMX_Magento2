<?php

namespace Evo\sdk;

require_once(__DIR__ . '/../ActionRequest.php');

class AuthActionRequest extends ActionRequest {

    protected $_params = array(
        "merchantId" => array("type" => "mandatory"),
        "token" => array("type" => "mandatory"),
        "specinCreditCardCVV" => array(
            "type" => "conditional",
            "mandatory" => array(
                "paymentMethod" => "CreditCard",
                "channel" => "ECOM"
            ),
        ),
        "freeText" => array("type" => "optional"),
    );

    public function __construct($data = array()) {
        parent::__construct($data);
        $this->_data["action"] = Payments::ACTION_AUTH;
    }
}
