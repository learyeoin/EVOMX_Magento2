<?php

namespace Evo\sdk;

require_once(__DIR__ . '/../Request.php');
require_once('Token/RefundTokenRequest.php');
require_once('Action/RefundActionRequest.php');

class RefundRequest extends GenericRequest {
    public function __construct($params = array()) {
        parent::__construct(Configurable::ACTION_REFUND, $params);
    }
}
