<?php

namespace Evo\sdk;

require_once(__DIR__ . '/../ActionRequest.php');

class TokenizeActionRequest extends ActionRequest {

    protected $_params = array(
        "merchantId" => array("type" => "mandatory"),
        "token" => array("type" => "mandatory"),
        "number" => array("type" => "mandatory"),
        "nameOnCard" => array("type" => "mandatory"),
        "expiryMonth" => array("type" => "mandatory"),
        "expiryYear" => array("type" => "mandatory"),
        "startMonth" => array("type" => "optional"),
        "startYear" => array("type" => "optional"),
        "issueNumber" => array("type" => "optional"),
        "cardDescription" => array("type" => "optional"),
    );

    public function __construct($data = array()) {
        parent::__construct($data);
        $this->_data["action"] = Payments::ACTION_TOKENIZE;
    }
}
