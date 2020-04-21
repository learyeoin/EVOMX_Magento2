<?php

namespace Evo\sdk;

require_once('RefundActionRequest.php');
require_once(__DIR__ . '/../../Payments.php');

class CaptureAction extends RefundActionRequest {

    public function __construct($data = array()) {
        parent::__construct($data);
        $this->_data["action"] = Payments::ACTION_CAPTURE;
    }

}
