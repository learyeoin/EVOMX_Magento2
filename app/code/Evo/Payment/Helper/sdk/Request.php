<?php

namespace Evo\sdk;

require_once('Executable.php');
require_once('Config.php');
require_once('RequestFactory.php');
require_once('Exception/ParamNotSet.php');
require_once('Exception/ConfigurationEndpointNotSet.php');
require_once('Exception/ProcessDataNotSet.php');
require_once('Exception/ExecuteNetworkError.php');
require_once('Response/GatewayResponseSuccess.php');
require_once('Response/GatewayResponseError.php');

abstract class Request extends Configurable implements Executable {

    protected $_keys = array(
        "token" => "token",
        "merchantId" => "merchantId",
    );

    public function __construct(array $data = []) {
        parent::__construct($data);
        $this->_data["timestamp"] = time() * 1000;
        call_user_func_array(array($this, "_set"), func_get_args());
    }

    public function __call($name, $value) {
		$req = RequestFactory::newRequest($name, $value);
        if (isset($req))
            return $req;

        return parent::__call($name, $value);
    }

    protected function _keys($k) {
        if (isset($this->_keys[$k])) {
            return $this->_keys[$k];
        }
        return $k;
    }

    protected function _exec_post($url, $data, $callback = null) {
        if (empty($url)) {
            throw new PaymentsExceptionConfigurationEndpointNotSet('Evo not configured');
        }
        if ((empty($data)) or ( !is_array($data)) or ( count($data) == 0)) {
            throw new PaymentsExceptionProcessDataNotSet('Evo not configured');
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, Config::$Method);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        $info = curl_getinfo($ch);

        if ($response === false) {
            throw new PaymentsExceptionExecuteNetworkError(curl_error($ch), curl_errno($ch));
        }
        curl_close($ch);
        $response = json_decode(trim($response), TRUE);
        if ((!is_null($callback)) && (is_callable($callback))) {
            call_user_func($callback, $response);
        } else {
            if (!isset($response["result"]) or $response["result"] != "success") {
                return new GatewayResponseError($response, $data, $info);
            }
            return new GatewayResponseSuccess($response, $info);
        }
    }

    public function token() {
        $this->_data["merchantId"] = Config::$MerchantId;
        $this->_data["password"] = Config::$Password;
        return $this->_token_request->execute(null, $this->_data);
    }

    public function __debugInfo() {
        $data = array();
        $data["request"] = $this->_data;
        if ($this->_token_request instanceof Request) {
            $data["token_request"] = $this->_token_request->_data;
        }
        if ($this->_action_request instanceof Request) {
            $data["action_request"] = $this->_action_request->_data;
        }
        return $data;
    }

    public function validate() {
        $data = $this->_data;
        if ((is_array($this->_params)) and ( count($this->_params) > 0) and ( is_array($this->_data))) {
            foreach ($this->_params as $key => $value) {
                if ($value["type"] == "mandatory") {
                    if (!isset($this->_data[$key])) {
                        $ex = new PaymentsExceptionParamNotSet($key, NULL, isset($ex) ? $ex : NULL);
                    }
                } else if ($value["type"] == "conditional") {
                    if (is_array($value["mandatory"])) {
                        foreach ($value["mandatory"] as $check => $value) {
                            if ((isset($this->_data[$check])) and ( $this->_data[$check] == $value) and ( !isset($this->_data[$key]))) {
                                $ex = new PaymentsExceptionParamNotSet($key, NULL, isset($ex) ? $ex : NULL);
                            }
                        }
                    }
                }
            }
            foreach ($this->_data as $check => $value) {
                if (!isset($this->_params[$check])) {
                    unset($data[$check]);
                }
            }
        }
        if (isset($ex)) {
            throw $ex;
        }
        return $data;
    }
}
