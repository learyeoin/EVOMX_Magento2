<?php

namespace Evo\sdk;

require_once('RefundTokenRequest.php');
require_once(__DIR__ . '/../../Payments.php');

class VoidToken extends RefundTokenRequest {

    protected $_params = array(
        "merchantId" => array("type" => "mandatory"),
        "originalMerchantTxId" => array("type" => "mandatory"),
        "password" => array("type" => "mandatory"),
        "action" => array(
            "type" => "mandatory",
            "values" => array(Payments::ACTION_VOID),
        ),
        "timestamp" => array("type" => "mandatory"),
        "allowOriginUrl" => array("type" => "mandatory"),
        "originalTxId" => array("type" => "optional"),
        "agentId" => array("type" => "optional"),
    );

    public function __construct(array $data = []) {
        parent::__construct($data);
        $this->_data["action"] = Payments::ACTION_VOID;
    }

}
