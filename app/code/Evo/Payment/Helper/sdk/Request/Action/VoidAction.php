<?php

namespace Evo\sdk;

require_once('RefundActionRequest.php');

class VoidAction extends RefundActionRequest {
    public function __construct($data = array()) {
        parent::__construct($data);
        $this->_data["action"] = Payments::ACTION_VOID;
    }
}
