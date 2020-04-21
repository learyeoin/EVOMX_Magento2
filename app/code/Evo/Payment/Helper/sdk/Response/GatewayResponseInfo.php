<?php

namespace Evo\sdk;

require_once(__DIR__ . '/../GatewayResponse.php');

class GatewayResponseInfo extends GatewayResponse {

    public function __construct($info = array()) {
        $this->_params = array_keys($info);
        $this->_data = $info;
    }

    public function __debugInfo() {
        return $this->_data;
    }

}
