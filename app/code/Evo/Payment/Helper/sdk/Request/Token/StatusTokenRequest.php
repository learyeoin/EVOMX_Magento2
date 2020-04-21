<?php

namespace Evo\sdk;

require_once(__DIR__ . '/../TokenRequest.php');
require_once(__DIR__ . '/../../Payments.php');

class StatusTokenRequest extends TokenRequest {

    protected $_params = array(
        "merchantId" => array("type" => "mandatory"),
        "password" => array("type" => "mandatory"),
        "action" => array(
            "type" => "mandatory",
            "values" => array(Payments::ACTION_GET_STATUS),
        ),
        "timestamp" => array("type" => "mandatory"),
        "allowOriginUrl" => array("type" => "mandatory"),
    );

    public function __construct(array $data = []) {
        parent::__construct($data);
        $this->_data["action"] = Payments::ACTION_GET_STATUS;
    }

}
