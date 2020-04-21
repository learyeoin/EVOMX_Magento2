<?php

namespace Evo\Payment\Model;

require_once(__DIR__ . '/../Helper/sdk/Config.php');
require_once(__DIR__ . '/../Helper/sdk/Configurable.php');
require_once(__DIR__ . '/../Helper/sdk/Executable.php');
require_once(__DIR__ . '/../Helper/sdk/Request.php');
require_once(__DIR__ . '/../Helper/sdk/GatewayResponse.php');
require_once(__DIR__ . '/../Helper/sdk/Payments.php');

require_once(__DIR__ . '/../Helper/sdk/Exception/ConfigurationEndpointNotSet.php');
require_once(__DIR__ . '/../Helper/sdk/Exception/ExecuteNetworkError.php');
require_once(__DIR__ . '/../Helper/sdk/Exception/MethodNotFound.php');
require_once(__DIR__ . '/../Helper/sdk/Exception/ParamNotExisting.php');
require_once(__DIR__ . '/../Helper/sdk/Exception/ParamNotSet.php');
require_once(__DIR__ . '/../Helper/sdk/Exception/ParamValueNotExisting.php');
require_once(__DIR__ . '/../Helper/sdk/Exception/ProcessDataNotSet.php');

require_once(__DIR__ . '/../Helper/sdk/Request/ActionRequest.php');
require_once(__DIR__ . '/../Helper/sdk/Request/AuthRequest.php');
require_once(__DIR__ . '/../Helper/sdk/Request/RefundRequest.php');
require_once(__DIR__ . '/../Helper/sdk/Request/CaptureRequest.php');
require_once(__DIR__ . '/../Helper/sdk/Request/PurchaseRequest.php');
require_once(__DIR__ . '/../Helper/sdk/Request/StatusRequest.php');
require_once(__DIR__ . '/../Helper/sdk/Request/TokenRequest.php');
require_once(__DIR__ . '/../Helper/sdk/Request/TokenizeRequest.php');
require_once(__DIR__ . '/../Helper/sdk/Request/VoidRequest.php');

require_once(__DIR__ . '/../Helper/sdk/Request/Action/AuthActionRequest.php');
require_once(__DIR__ . '/../Helper/sdk/Request/Action/RefundActionRequest.php');
require_once(__DIR__ . '/../Helper/sdk/Request/Action/CaptureAction.php');
require_once(__DIR__ . '/../Helper/sdk/Request/Action/PurchaseAction.php');
require_once(__DIR__ . '/../Helper/sdk/Request/Action/StatusActionRequest.php');
require_once(__DIR__ . '/../Helper/sdk/Request/Action/TokenizeActionRequest.php');
require_once(__DIR__ . '/../Helper/sdk/Request/Action/VoidAction.php');
require_once(__DIR__ . '/../Helper/sdk/Request/Token/AuthTokenRequest.php');
require_once(__DIR__ . '/../Helper/sdk/Request/Token/RefundTokenRequest.php');
require_once(__DIR__ . '/../Helper/sdk/Request/Token/CaptureTokenRequest.php');
require_once(__DIR__ . '/../Helper/sdk/Request/Token/PurchaseToken.php');
require_once(__DIR__ . '/../Helper/sdk/Request/Token/StatusTokenRequest.php');
require_once(__DIR__ . '/../Helper/sdk/Request/Token/TokenizeTokenRequest.php');
require_once(__DIR__ . '/../Helper/sdk/Request/Token/VoidToken.php');

require_once(__DIR__ . '/../Helper/sdk/Response/Error/Errors.php');
require_once(__DIR__ . '/../Helper/sdk/Response/GatewayResponseError.php');

require_once(__DIR__ . '/../Helper/sdk/Response/GatewayResponseSuccess.php');

use Evo\Payment\Helper\Payments;

class Payment extends \Magento\Payment\Model\Method\Cc
{
    const METHOD_CODE = 'evo';

    protected $_code = self::METHOD_CODE;

    protected $_isGateway = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;

    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;

    protected $_isOffline = false;
    protected $_canVoid = false;


    protected $_supportedCurrencyCodes = array('USD', 'GBP', 'EUR');
    protected $_logfile = 'system.log';
    protected $logger;
    public $customLogger;
    public $loggerWriter;
    CONST MERCHANT_ID_PREFIX = 'NIPG2-';
    CONST PAYMENT_SOLUTION_ID = '500';

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        array $data = array()
    )
    {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $moduleList,
            $localeDate,
            null,
            null,
            $data
        );

        $ds = DIRECTORY_SEPARATOR;

        $this->logger = $logger;
        $this->_code = 'evo';
        $this->loggerWriter = new \Zend\Log\Writer\Stream(BP . $ds .'var' . $ds .'log' . $ds .'NIPG_' . DATE("y-m-d") . '.log');
        $this->customLogger = new \Zend\Log\Logger();
    }


    /**
     * Construct IPG payment gateway
     * @return paymentObject
     */
    public function _constructIPG()
    {
        if ($this->getConfigData('testmode') == 1) :
            return (new Payments())->environmentUrls(array(
                "merchantId" => $this->getConfigData('merchant_key'),
                "password" => $this->getConfigData('merchant_password'),
                "sessionToken" => $this->getConfigData('testtoken'),
                "action" => $this->getConfigData('testpayment'),
                "baseUrl" => $this->getConfigData('testbase'),
                "jsToken" => $this->getConfigData('testjs'),
            ));
        endif;
        return (new Payments())->environmentUrls(array(
            "merchantId" => $this->getConfigData('merchant_key'),
            "password" => $this->getConfigData('merchant_password'),
            "sessionToken" => $this->getConfigData('livetoken'),
            "action" => $this->getConfigData('livepayment'),
            "baseUrl" => $this->getConfigData('livebase'),
            "jsToken" => $this->getConfigData('livejs'),
        ));
    }

    public function logData($data)
    {
        $priority = \Zend\Log\Logger::INFO;
        $this->customLogger->addWriter($this->loggerWriter);
        $this->customLogger->log($priority, $data);
    }

    public function getMerchantOrderPrefix($orderId)
    {
        $merchantIdFormat = self::MERCHANT_ID_PREFIX . time() . '_' . $orderId;
        return $merchantIdFormat;
    }


    public function canUseForCurrency($currencyCode)
    {
        if (!in_array($currencyCode, $this->_supportedCurrencyCodes)) {
            return false;
        }
        return true;
    }

    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $this->logData('NIPG transaction started at :' . Date('y-m-d h:i:s'));
        try {

            $payments = $this->_constructIPG();
            $token = $this->tokenizePayment($payments, $payment, $amount);
            $this->logData('Token result :' . $token->result);
            if ($token->result === 'success') {
                /******* save payment additional details which is used for refund functionality**********/

                $transactionDetails = array();
                $transactionDetails['originalMerchantTxId'] = $token->merchantTxId;
                $transactionDetails['txnId'] = $token->txId;

                $payment->setTransactionId($token->txId);
                $payment->setIsTransactionClosed(0);
                $payment->setAdditionalInformation($transactionDetails);

                /******* save payment additional details which is used for refund functionality**********/
            }
            return $this;

        } catch (\Exception $e) {
            throw new \Magento\Framework\Validator\Exception(__($e->getMessage()));
        }
    }

    public function processPayments($payments, $payment, $token, $amount)
    {
        try {
            $this->logData('Process payment :' . $token->cardToken . ' amount ' . $amount);

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $urlInterface = $objectManager->get('\Magento\Framework\UrlInterface');

            $billingAddress = $payment->getOrder()->getBillingAddress();
            $shipingAddress = $payment->getOrder()->getBillingAddress();

            if (is_array($billingAddress->getStreet(1))) {
                $street_arr = $billingAddress->getStreet(1);
                $str_add = substr($street_arr[0], 0, 40);
            } else {
                $str_add = $billingAddress->getStreet(1);
            }
            if (is_array($shipingAddress->getStreet(1))) {
                $street_arr1 = $billingAddress->getStreet(1);
                $str_add1 = substr($street_arr1[0], 0, 40);
            } else {
                $str_add1 = $billingAddress->getStreet(1);
            }

            $order = $payment->getOrder();
            $orderid = $order->getIncrementId();
            $order_id = $this->getMerchantOrderPrefix($order->getIncrementId());

            $purchase = $payments->purchase();
            $purchase->allowOriginUrl($urlInterface->getBaseUrl())->
            merchantNotificationUrl($urlInterface->getUrl('evo_checkout/index/response/orderid/' . $orderid))->
            channel(Payments::CHANNEL_ECOM)->
            userDevice(Payments::USER_DEVICE_DESKTOP)->
            amount($amount)->
            country($this->getConfigData('countryid'))->
            currency($this->getConfigData('currencyid'))->
            paymentSolutionId(self::PAYMENT_SOLUTION_ID)->
            customerId($token->customerId)->
            specinCreditCardToken($token->cardToken)->
            specinCreditCardCVV($payment->getCcCid())->

            customerEmail($billingAddress->getEmail())->
            customerFirstName($billingAddress->getFirstname())->
            customerLastName($billingAddress->getLastname())->

            customerAddressCountry($billingAddress->getcountryId())->
            customerAddressStreet($str_add)->
            customerAddressCity($billingAddress->getCity())->
            customerAddressPostalCode($billingAddress->getPostcode())->
            customerAddressPhone($billingAddress->getTelephone())->
            customerPhone($billingAddress->getTelephone())->

            customerBillingAddressStreet($billingAddress->getStreet())->
            customerBillingAddressCity($billingAddress->getCity())->
            customerBillingAddressCountry($billingAddress->getcountryId())->
            customerBillingAddressPostalCode($billingAddress->getPostcode())->
            customerBillingAddressPhone($billingAddress->getTelephone())->

            customerShippingAddressStreet($str_add1)->
            customerShippingAddressCity($shipingAddress->getCity())->
            customerShippingAddressCountry($shipingAddress->getcountryId())->
            customerShippingAddressPostalCode($shipingAddress->getPostcode())->
            customerShippingAddressPhone($shipingAddress->getTelephone())->
            merchantTxId($order_id);

            $this->logData('Process payment response: start ' . $amount);

            $result = $purchase->execute();
            $this->logData('Process payment response:' . print_r($result, true) . ' amount ' . $amount);
            if ($result->result === 'success') {
                return $result;
            } else {
                throw new \Magento\Framework\Validator\Exception(__($result->errors));
            }

        } catch (Exception $e) {
            throw new \Magento\Framework\Validator\Exception(__($e->getMessage()));
        }

    }

    public function tokenizePayment($payments, $payment, $amount)
    {
        try {
            $month = $payment->getCcExpMonth();
            if ($month < 10) {
                if (strlen($month) == 1) {
                    $mon = '0' . $month;
                } else {
                    $mon = $month;
                }
            } else {
                $mon = $month;
            }
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $urlInterface = $objectManager->get('\Magento\Framework\UrlInterface');

            $tokenize = $payments->tokenize();
            $tokenize->allowOriginUrl($urlInterface->getBaseUrl())->number($payment->getCcNumber())->nameOnCard($payment->getCcOwner())->expiryMonth($mon)->expiryYear($payment->getCcExpYear());

            $token = $tokenize->execute();
            $this->logData('Process token request :' . print_r($token, true) . ' amount ' . $amount);
            if ($token->result === 'success') {
                $paymentResult = $this->processPayments($payments, $payment, $token, $amount);

                return $paymentResult;

            } else {
                throw new \Magento\Framework\Validator\Exception(__($token->errors));
            }

        } catch (Exception $e) {
            throw new \Magento\Framework\Validator\Exception(__($e->getMessage()));
        }
    }

    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return $this->getConfigData('active');
    }


    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        // construct IPG Object
        $payments = $this->_constructIPG();

        // get merchant information
        $additinalInformation = $payment->getAdditionalInformation();

        if (!isset($additinalInformation['originalMerchantTxId']) && $additinalInformation['originalMerchantTxId'] == '') {
            Mage::throwException(
                Mage::helper('sales')->__('Merchant Transaction Details not found for this order.')
            );
        }

        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $urlInterface = $objectManager->get('\Magento\Framework\UrlInterface');

            $refund = $payments->refund();
            $refund->allowOriginUrl($urlInterface->getBaseUrl())
                ->amount($amount)
                ->originalMerchantTxId($additinalInformation['originalMerchantTxId'])
                ->txId($additinalInformation['txnId']);

            $result = $refund->execute();

            return $this;
        } catch (Exception $e) {
            throw $e->getMessage();
        }

    }

}

