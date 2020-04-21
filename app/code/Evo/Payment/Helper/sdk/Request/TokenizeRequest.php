<?php

namespace Evo\sdk;

require_once('GenericRequest.php');
require_once(__DIR__ . '/../Request.php');
require_once('Token/TokenizeTokenRequest.php');
require_once('Action/TokenizeActionRequest.php');

use Evo\sdk\GenericRequest;

class TokenizeRequest extends GenericRequest {
    public function __construct($params = array()) {
        parent::__construct(Configurable::ACTION_TOKENIZE, $params);
    }
}
