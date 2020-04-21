<?php

namespace Evo\sdk;

require_once(__DIR__ . '/../Request.php');
require_once(__DIR__ . '/../Config.php');

class ActionRequest extends Request {

    public function __construct($data = array()) {
        parent::__construct($data);
    }

    public function execute($callback = NULL, $result_from_prev = array()) {
        try {
            foreach ($result_from_prev as $k => $v) {
                $this->_data[$k] = $v;
            }
			//throw new \Magento\Framework\Validator\Exception(__(json_encode($this->_data)));
            $data = $this->validate();
            return $this->_exec_post(Config::$PaymentOperationActionUrl, $data, $callback);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function _get_data_from_prev($result_from_prev) {
        $this->_data["merchantId"] = $result_from_prev["merchantId"];
        $this->_data["token"] = $result_from_prev["token"];
    }

}
