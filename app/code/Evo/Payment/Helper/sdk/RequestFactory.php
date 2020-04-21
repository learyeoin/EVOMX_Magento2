<?php

namespace Evo\sdk;

require_once('Request/TokenizeRequest.php');
require_once('Request/AuthRequest.php');
require_once('Request/CaptureRequest.php');
require_once('Request/PurchaseRequest.php');
require_once('Request/RefundRequest.php');
require_once('Request/StatusRequest.php');
require_once('Request/VoidRequest.php');

class RequestFactory {

    public static function newRequest($action, $data) {
        switch (strtoupper($action)) {
            case Configurable::ACTION_TOKENIZE:
                return new TokenizeRequest($data);
            case Configurable::ACTION_AUTH:
                return new AuthRequest($data);
            case Configurable::ACTION_CAPTURE:
                return new CaptureRequest($data);
            case Configurable::ACTION_PURCHASE:
                return new PurchaseRequest($data);
            case Configurable::ACTION_REFUND:
                return new RefundRequest($data);
            case Configurable::ACTION_GET_STATUS:
                return new StatusRequest($data);
            case Configurable::ACTION_VOID:
                return new VoidRequest($data);
        }

        return null;
    }

    public static function newActionRequest($action, $data) {
        switch (strtoupper($action)) {
            case Configurable::ACTION_TOKENIZE:
                return new TokenizeActionRequest($data);
            case Configurable::ACTION_AUTH:
                return new AuthActionRequest($data);
            case Configurable::ACTION_CAPTURE:
                return new CaptureAction($data);
            case Configurable::ACTION_PURCHASE:
                return new PurchaseAction($data);
            case Configurable::ACTION_REFUND:
                return new RefundActionRequest($data);
            case Configurable::ACTION_GET_STATUS:
                return new StatusActionRequest($data);
            case Configurable::ACTION_VOID:
                return new VoidAction($data);
        }

        return null;
    }

    public static function newTokenRequest($action, $data) {
        switch (strtoupper($action)) {
            case Configurable::ACTION_TOKENIZE:
                return new TokenizeTokenRequest($data);
            case Configurable::ACTION_AUTH:
                return new AuthTokenRequest($data);
            case Configurable::ACTION_CAPTURE:
                return new CaptureTokenRequest($data);
            case Configurable::ACTION_PURCHASE:
                return new PurchaseToken($data);
            case Configurable::ACTION_REFUND:
                return new RefundTokenRequest($data);
            case Configurable::ACTION_GET_STATUS:
                return new StatusTokenRequest($data);
            case Configurable::ACTION_VOID:
                return new VoidToken($data);
        }

        return null;
    }
}