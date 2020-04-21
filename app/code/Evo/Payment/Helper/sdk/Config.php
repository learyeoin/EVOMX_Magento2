<?php

namespace Evo\sdk;

class Config {
    
    static $SessionTokenRequestUrl;
    static $PaymentOperationActionUrl;
    static $BaseUrl;
    static $JavaScriptUrl;
    static $TestUrls = array(
        "SessionTokenRequestUrl" => "https://api-turnkeyuat.test.myriadpayments.com/token",
        "PaymentOperationActionUrl" => "https://api-turnkeyuat.test.myriadpayments.com/payments",
        "JavaScriptUrl" => "https://cashier-turnkeyuat.test.myriadpayments.com/js/api.js",
        "BaseUrl" => "https://cashier-turnkeyuat.test.myriadpayments.com/ui/cashier",
    );
    static $ProductionUrls = array(
        "SessionTokenRequestUrl" => "https://api-turnkey.myriadpayments.com/token",
        "PaymentOperationActionUrl" => "https://api-turnkey.myriadpayments.com/payments",
        "JavaScriptUrl" => "https://api-turnkey.myriadpayments.com/js/api.js",
        "BaseUrl" => "https://api-turnkey.myriadpayments.com/ui/cashier",
    );
    static $Protocol = "https";
    static $Method = "POST";
    static $ContentType = "application/x-www-form-urlencoded";
    static $MerchantId = "";
    static $Password = "";

    public static function factory() {
        foreach (func_get_args()[0] as $var => $value) {
            self::${ucfirst($var)} = $value;
        }
    }

    public static function setUrls($tokenURL='', $paymentsURL='' ,$baseUrl ='', $jslibUrl='' ) {
        self::$SessionTokenRequestUrl = $tokenURL;
        self::$PaymentOperationActionUrl = $paymentsURL;
        self::$BaseUrl = $baseUrl;
        self::$JavaScriptUrl = $jslibUrl;
    }
    
    public static function baseUrl() {
        return self::$BaseUrl;
    }
    
    public static function javaScriptUrl() {
        return self::$JavaScriptUrl;
    }

}
