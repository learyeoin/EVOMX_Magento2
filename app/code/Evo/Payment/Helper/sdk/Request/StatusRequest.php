<?php

namespace Evo\sdk;

require_once(__DIR__ . '/../Request.php');
require_once('Token/StatusTokenRequest.php');
require_once('Action/StatusActionRequest.php');

class StatusRequest extends GenericRequest {
    public function __construct($params = array()) {
        parent::__construct(Configurable::ACTION_GET_STATUS, $params);
    }
}
