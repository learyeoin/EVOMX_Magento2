<?php

namespace Evo\sdk;

require_once(__DIR__ . '/../../GatewayResponse.php');

class GatewayResponseErrorErrors extends GatewayResponse {
        
    public function __construct($errors = array()) {
        if (is_array($errors)) {
            foreach ($errors as $error) {
                $this->_data[$error] = $error;
            }
        } else {
            $this->_data[$errors] = $errors;
        }
    }

}
