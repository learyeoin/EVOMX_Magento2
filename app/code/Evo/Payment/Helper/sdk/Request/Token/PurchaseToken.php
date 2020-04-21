<?php

namespace Evo\sdk;

require_once('AuthTokenRequest.php');
require_once(__DIR__ . '/../../Payments.php');

class PurchaseToken extends AuthTokenRequest {

    public function __construct(array $data = []) {
        parent::__construct($data);
        $this->_data["action"] = Payments::ACTION_PURCHASE;
    }

}
