<?php

namespace Evo\sdk;

require_once(__DIR__ . '/../TokenRequest.php');
require_once(__DIR__ . '/../../Payments.php');

class TokenizeTokenRequest extends TokenRequest {

    protected $_params = array(
        "action" => array(
            "type" => "mandatory",
            "values" => array(Payments::ACTION_TOKENIZE),
        ),
        "merchantId" => array("type" => "mandatory"),
        "password" => array("type" => "mandatory"),
        "timestamp" => array("type" => "mandatory"),
        "allowOriginUrl" => array("type" => "mandatory"),
        "customerId"  => array("type" => "optional"),
    );

    public function __construct(array $data = []) {
        parent::__construct($data);
        $this->_data["action"] = Payments::ACTION_TOKENIZE;
    }

}
