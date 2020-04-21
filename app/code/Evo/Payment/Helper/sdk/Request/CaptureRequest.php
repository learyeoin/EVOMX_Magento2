<?php

namespace Evo\sdk;

require_once('RefundRequest.php');
require_once('Token/CaptureTokenRequest.php');
require_once('Action/CaptureAction.php');

class CaptureRequest extends GenericRequest {
    public function __construct($params = array()) {
        parent::__construct(Configurable::ACTION_CAPTURE, $params);
    }
}
