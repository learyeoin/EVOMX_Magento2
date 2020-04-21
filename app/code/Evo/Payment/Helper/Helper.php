<?php

namespace Evo\Payment\Helper;

use http\Params;
use Evo\Payment\Model\Config\Source\DisplayMode;
use Evo\sdk\RequestFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Evo\Payment\Model\Config\Source\Environment;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Helper extends AbstractHelper
{
    const METHOD_CODE = 'evo_payment';
    const CUSTOMER_ID = 'customer';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;
    private $_encryptor;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_encryptor = $encryptor;
    }

    /**
     * @desc Returns true if integration is in sandbox mode
     *
     * @return bool
     */
    public function isSandboxMode()
    {
        return $this->getConfigData('environment') == Environment::ENVIRONMENT_SANDBOX;
    }

    /**
     * @desc Returns the URL of the payment form according to the configured integration mode
     *
     * @return string
     */
    public function getFormUrl()
    {
        $displayMode = $this->getConfigData('display_mode');
        if ($displayMode === DisplayMode::DISPLAY_MODE_REDIRECT) {
            // redirect to the cashier URL
            return $this->getCashierUrl();
        } else if ($displayMode === DisplayMode::DISPLAY_MODE_HOSTEDPAY) {
            // hostedpaypage redirect to the cashier URL
            return $this->getCashierUrl();
        }else if ($displayMode === DisplayMode::DISPLAY_MODE_EMBEDDED) {

        } else if ($displayMode === DisplayMode::DISPLAY_MODE_IFRAME) {
            return $this->getCashierUrl();
        } else {
            $this->logDebug("Display mode not valid: " . $displayMode);
            return '';
        }
    }

    public function getNotificationRoute($orderId)
    {
        $displayMode = $this->getConfigData('display_mode');
        if ($displayMode === DisplayMode::DISPLAY_MODE_REDIRECT) {
            return 'evo/hosted/callback/orderid/' . $orderId;
        } else if ($displayMode === DisplayMode::DISPLAY_MODE_HOSTEDPAY) {
            return 'evo/hosted/callback/orderid/' . $orderId;
        }else if ($displayMode === DisplayMode::DISPLAY_MODE_EMBEDDED) {

        } else if ($displayMode === DisplayMode::DISPLAY_MODE_IFRAME) {
            return 'evo/hosted/callback/orderid/' . $orderId;
        } else {
            $this->logDebug("Display mode not valid: " . $displayMode);
            return '';
        }
    }

    public function getLandingPageOnReturnAfterRedirect($orderId)
    {
        $displayMode = $this->getConfigData('display_mode');
        if ($displayMode === DisplayMode::DISPLAY_MODE_REDIRECT) {
            return 'evo/hosted/response/orderid/' . $orderId;
        } else if ($displayMode === DisplayMode::DISPLAY_MODE_HOSTEDPAY) {
            return 'evo/hosted/response/orderid/' . $orderId;
        }else if ($displayMode === DisplayMode::DISPLAY_MODE_EMBEDDED) {

        } else if ($displayMode === DisplayMode::DISPLAY_MODE_IFRAME) {
            return 'evo/hosted/response/orderid/' . $orderId;
        } else {
            $this->logDebug("Display mode not valid: " . $displayMode);
            return '';
        }
    }

    /**
     * @desc Get Cashier URL
     *
     * @return string
     */
    public function getCashierUrl()
    {
        if ($this->isSandboxMode()) {
            return $this->getConfigData('cashier_url_sandbox');
        }

        return $this->getConfigData('cashier_url_production');
    }
    /**
     * @desc Get Cashier JS API URL
     *
     * @return string
     */
    public function getJsUrl()
    {
        if ($this->isSandboxMode()) {
            return $this->getConfigData('js_url_sandbox');
        }

        return $this->getConfigData('js_url_production');
    }
    /**
     * @desc Returns the method of the HTTP Request that the form will execute
     *
     * @return string
     */
    public function getFormMethod()
    {
        $displayMode = $this->getConfigData('display_mode');
        if ($displayMode === DisplayMode::DISPLAY_MODE_REDIRECT) {
            return "GET";
        } else if ($displayMode === DisplayMode::DISPLAY_MODE_HOSTEDPAY) {
            return "GET";
        }else if ($displayMode === DisplayMode::DISPLAY_MODE_EMBEDDED) {

        } else if ($displayMode === DisplayMode::DISPLAY_MODE_IFRAME) {
            return "GET";
        } else {
            $this->logDebug("Display mode not valid: " . $displayMode);
            return '';
        }
    }

    public function getIntegrationMode()
    {
        $displayMode = $this->getConfigData('display_mode');
        $brand = $this->getConfigData('title');
        if ($displayMode === DisplayMode::DISPLAY_MODE_REDIRECT) {
            /*if ( $brand == 'evo_pl' || $brand == 'eservice') {
                return 'hostedPayPage';
            }*/
            return "standalone";
        } else if ($displayMode === DisplayMode::DISPLAY_MODE_HOSTEDPAY) {
                return 'hostedPayPage';
        } else if ($displayMode === DisplayMode::DISPLAY_MODE_EMBEDDED) {

        } else if ($displayMode === DisplayMode::DISPLAY_MODE_IFRAME) {
            return "iframe";
        } else {
            $this->logDebug("Display mode not valid: " . $displayMode);
            return '';
        }
    }

    /**
     * @desc Get remote api url
     *
     * @return string
     */
    public function getRemoteApiUrl()
    {
        if ($this->isSandboxMode()) {
            return $this->getConfigData('sandbox_api_url');
        }

        return $this->getConfigData('live_api_url');
    }

    public function logDebug($message)
    {
        if ($this->getConfigData('debug_log') == '1') {
            $this->_logger->debug($message);
        }
    }

    /**
 * @desc Cancels the order
 *
 * @param \Magento\Sales\Mode\Order $order
 */
    public function cancelOrder($order)
    {
        $orderStatus = $this->getConfigData('payment_cancelled');
        $order->setActionFlag($orderStatus, true);
        $order->cancel()->save();
    }

    /**
     * @desc Sets the order to pending
     *
     * @param \Magento\Sales\Mode\Order $order
     */
    public function setPendingOrder($order)
    {
        $order->setState("pending_payment")->setStatus("pending_payment")->addStatusHistoryComment('Payment is pending')->setIsCustomerNotified(true);
        $order->save();
    }

    public function getConfigData($field, $storeId = null)
    {
        $fieldData = $this->getConfig($field, self::METHOD_CODE, $storeId);
        if ($field == 'merchant_password') {
            $fieldData = $this->_encryptor->decrypt($fieldData);
        }
        return $fieldData;
    }

    /**
     * @desc Gives back configuration values as flag
     *
     * @param $field
     * @param null $storeId
     *
     * @return mixed
     */
    public function getConfigDataFlag($field, $storeId = null)
    {
        return $this->getConfig($field, self::METHOD_CODE, $storeId, true);
    }

    /**
     * @desc Retrieve information from payment configuration
     *
     * @param $field
     * @param $paymentMethodCode
     * @param $storeId
     * @param bool|false $flag
     *
     * @return bool|mixed
     */
    public function getConfig($field, $paymentMethodCode, $storeId, $flag = false)
    {
        $path = 'payment/'.$paymentMethodCode.'/'.$field;
        if (null === $storeId) {
            $storeId = $this->_storeManager->getStore();
        }

        if (!$flag) {
            return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        } else {
            return $this->scopeConfig->isSetFlag($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        }
    }

    /**
     * Construct IPG payment gateway
     * @param Configurable $configurable
     * @param array $params
     * @return Configurable|Payments
     */
    public function constructIPG(Configurable $configurable = null, $params = array())
    {
        $baseParams = array();
        if ($this->isSandboxMode()) {
            $baseParams["tokenURL"] = $this->getConfigData('token_url_sandbox');
            $baseParams["cashierURL"] = $this->getConfigData('cashier_url_sandbox');
            $baseParams["paymentsURL"] = $this->getConfigData('payments_url_sandbox');
        } else {
            //return (new Payments())->productionEnvironment($params);
        }
        if (!isset($configurable)) {
            $configurable = new Payments($params);
        }
        $configurable->environmentUrls($baseParams);
        $baseParams =  array(
            "merchantId" => $this->getConfigData('merchant_id'),
            "password" => $this->getConfigData('merchant_password'),
            "timestamp" => time() * 1000,
            "channel" => 'ECOM',

        );
        foreach($baseParams as $key => $value) {
            call_user_func_array(array($configurable, $key), array($value));
        }
        /*foreach($params as $key => $value) {
            call_user_func_array(array($configurable, $key), array($value));
        }*/
		//throw new \Magento\Framework\Validator\Exception(__(json_encode($configurable->_data)));
        return $configurable;
    }

    public function setCommonParams($configurable)
    {
        $baseParams = array();
        if ($this->isSandboxMode()) {
            $baseParams["tokenURL"] = $this->getConfigData('token_url_sandbox');
            $baseParams["cashierURL"] = $this->getConfigData('cashier_url_sandbox');
            $baseParams["paymentsURL"] = $this->getConfigData('payments_url_sandbox');
        } else {
            $baseParams["tokenURL"] = $this->getConfigData('token_url_production');
            $baseParams["cashierURL"] = $this->getConfigData('cashier_url_production');
            $baseParams["paymentsURL"] = $this->getConfigData('payments_url_production');
        }

        $configurable->environmentUrls($baseParams);
        $baseParams =  array(
            "merchantId" => $this->getConfigData('merchant_id'),
            "password" => $this->getConfigData('merchant_password'),
            "timestamp" => time() * 1000,
            "channel" => 'ECOM',

        );
        foreach($baseParams as $key => $value) {
            call_user_func_array(array($configurable, $key), array($value));
        }

        //throw new \Magento\Framework\Validator\Exception(__(json_encode($configurable->_data)));
        return $configurable;
    }

    public function getMagentoTransactionType($action)
    {
        switch ($action){
            case Configurable::ACTION_AUTH:
                return \Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH;
            case Configurable::ACTION_PURCHASE:
                return \Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE;
        }
        return null;
    }

    public function executeGatewayTransaction($action, $params = array()) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $urlInterface = $objectManager->get('\Magento\Framework\UrlInterface');

        //$ipgApi = $this->constructIPG(null, $params);
		//throw new \Magento\Framework\Validator\Exception(__(json_encode($ipgApi->_data)));
        //$apiOperation = call_user_func(array($ipgApi, $action), $ipgApi->_data);
		//$apiOperation = RequestFactory::newRequest($action, $ipgApi->_data);
		//throw new \Magento\Framework\Validator\Exception(__(json_encode($apiOperation->_data)));
        /*foreach ($params as $name => $value) {
            call_user_func_array(array($apiOperation, $name), array($value));
        }*/
        $apiOperation = RequestFactory::newRequest($action, $params);
        $this->setCommonParams($apiOperation);
        $result = $apiOperation->execute();
//throw new \Magento\Framework\Validator\Exception(__(json_encode($result)));
        return $result;
    }

    public function generateInvoice($order, $invoiceService, $transaction){
        try {
            if (!$order->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('The order no longer exists.'));
            }
            if(!$order->canInvoice()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The order does not allow an invoice to be created.')
                );
            }

            $invoice = $invoiceService->prepareInvoice($order);
            if (!$invoice) {
                throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t save the invoice right now.'));
            }
            if (!$invoice->getTotalQty()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('You can\'t create an invoice without products.')
                );
            }
            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
            $invoice->register();
            $invoice->save();
            $invoice->getOrder()->setCustomerNoteNotify(false);
            $invoice->getOrder()->setIsInProcess(true);
            $order->addStatusHistoryComment(__('Automatically INVOICED'), true);
            $transactionSave = $transaction->addObject($invoice)->addObject($invoice->getOrder());
            $transactionSave->save();
        } catch (\Exception $e) {
            throw new \Magento\Framework\Validator\Exception(__(json_encode($e->getMessage())));
        }

        return $invoice;
    }
}
