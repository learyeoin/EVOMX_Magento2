<?php

namespace Evo\sdk;

require_once('AuthActionRequest.php');
require_once(__DIR__ . '/../../Payments.php');

class PurchaseAction extends AuthActionRequest {

    public function __construct($data = array()) {
        parent::__construct($data);
        $this->_data["action"] = Payments::ACTION_PURCHASE;
    }

}
