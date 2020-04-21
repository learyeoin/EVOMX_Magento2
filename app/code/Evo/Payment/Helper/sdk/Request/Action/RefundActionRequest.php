<?php

namespace Evo\sdk;

require_once(__DIR__ . '/../ActionRequest.php');
require_once(__DIR__ . '/../../Payments.php');

class RefundActionRequest extends ActionRequest {

    protected $_params = array(
        "merchantId" => array("type" => "mandatory"),
        "token" => array("type" => "mandatory"),
    );

    public function __construct($data = array()) {
        parent::__construct($data);
        $this->_data["action"] = Payments::ACTION_REFUND;
    }

}
