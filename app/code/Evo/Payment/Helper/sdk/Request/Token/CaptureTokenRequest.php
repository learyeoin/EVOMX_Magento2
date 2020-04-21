<?php

namespace Evo\sdk;

require_once('RefundTokenRequest.php');
require_once(__DIR__ . '/../../Payments.php');

class CaptureTokenRequest extends RefundTokenRequest {

    public function __construct(array $data = []) {
        parent::__construct($data);
        $this->_data["action"] = Payments::ACTION_CAPTURE;
    }

}
