<?php

namespace Evo\sdk;

require_once(__DIR__ . '/../Request.php');
require_once(__DIR__ . '/../Config.php');

class TokenRequest extends Request {

    public function execute($callback = NULL, $result_from_prev = array()) {
        try {
            foreach ($result_from_prev as $k => $v) {
                $this->_data[$k] = $v;
            }
            $data = $this->validate();
            return $this->_exec_post(Config::$SessionTokenRequestUrl, $data, $callback);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
