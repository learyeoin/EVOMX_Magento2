<?php

namespace Evo\sdk;

require_once(__DIR__ . '/../Request.php');
require_once('Token/AuthTokenRequest.php');
require_once('Action/AuthActionRequest.php');

abstract class GenericRequest extends Request {

    protected $_token_request;
    protected $_action_request;

    public function __construct($action, $params = array()) {
        parent::__construct($params);
        $this->_token_request = RequestFactory::newTokenRequest($action, $params);
        $this->_action_request = RequestFactory::newActionRequest($action, $params);
    }

    public function execute($callback = NULL, $result_from_prev = array()) {
        try {
            $this->_data["merchantId"] = Config::$MerchantId;
            $this->_data["password"] = Config::$Password;
            $token_result = $this->_token_request->execute(NULL, $this->_data);
            if (is_a($token_result, "Evo\ResponseError")) {
                return $token_result;
            }
            foreach ($token_result as $k => $v) {
                $this->_data[$this->_keys($k)] = $v;
            }
            return $this->_action_request->execute($callback, $this->_data);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
