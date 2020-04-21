<?php

namespace Evo\sdk;

require_once(__DIR__ . '/../Request.php');
require_once('Token/VoidToken.php');
require_once('Action/VoidAction.php');

class VoidRequest extends GenericRequest {
    public function __construct($params = array()) {
        parent::__construct(Configurable::ACTION_VOID, $params);
    }
}
