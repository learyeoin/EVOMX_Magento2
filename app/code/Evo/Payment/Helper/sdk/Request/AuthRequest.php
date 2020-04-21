<?php

namespace Evo\sdk;

require_once(__DIR__ . '/../Request.php');
require_once('Token/AuthTokenRequest.php');
require_once('Action/AuthActionRequest.php');

class AuthRequest extends GenericRequest {
    public function __construct($params = array()) {
        parent::__construct(Configurable::ACTION_AUTH, $params);
    }
}
