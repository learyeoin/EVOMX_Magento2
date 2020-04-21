<?php

namespace Evo\sdk;

require_once(__DIR__ . '/../ActionRequest.php');
require_once(__DIR__ . '/../../Payments.php');

class StatusActionRequest extends ActionRequest {

    protected $_params = array(
        "merchantId" => array("type" => "mandatory"),
        "token" => array("type" => "mandatory"),
        "action" => array(
            "type" => "mandatory",
            "values" => array(Payments::ACTION_GET_STATUS),
        ),
        "txId" => array("type" => "optional"),
        "merchantTxId" => array("type" => "optional"),
    );

    public function __construct($data = array()) {
        parent::__construct($data);
        $this->_data["action"] = Payments::ACTION_GET_STATUS;
    }

}
