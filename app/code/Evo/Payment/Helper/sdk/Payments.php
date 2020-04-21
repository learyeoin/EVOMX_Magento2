<?php

namespace Evo\sdk;

require_once('RequestFactory.php');
require_once('Request.php');

class Payments extends Configurable {
    protected $_request;

    public function __construct($params = array()) {
        parent::__construct($params);
        //$this->_request = new Request($params);
    }

    public function __call($name, $value) {
		//throw new \Magento\Framework\Validator\Exception(__(json_encode($value)));
        $req = RequestFactory::newRequest($name, $value);
		//throw new \Magento\Framework\Validator\Exception(__(json_encode($this->_data)));
        if (isset($req))
            return $req;

        return parent::__call($name, $value);
    }

}
