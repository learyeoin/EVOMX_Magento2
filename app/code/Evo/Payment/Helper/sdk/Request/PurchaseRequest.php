<?php

namespace Evo\sdk;

require_once(__DIR__ . '/../Request.php');
require_once('Token/PurchaseToken.php');
require_once('Action/PurchaseAction.php');

class PurchaseRequest extends GenericRequest {
    public function __construct($params = array()) {
        parent::__construct(Configurable::ACTION_PURCHASE, $params);
    }
}
