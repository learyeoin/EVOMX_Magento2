<?php

namespace UniversalPay\Payment\Controller\Hosted;

require_once(__DIR__ . '/../../Helper/sdk/Configurable.php');
require_once(__DIR__ . '/../../Helper/sdk/RequestFactory.php');

use UniversalPay\sdk\Configurable;
use UniversalPay\sdk\RequestFactory;
use UniversalPay\Payment\Helper\Helper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Transaction;

class Response extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;

    protected  $invoiceService;

    /**
     * Registry
     *
     * @var \Magento\Framework\Registry\Registry
     */
    private $registry;

    /**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $_transaction;

    /**
     * @var \UniversalPay\Payment\Helper\Helper
     */
    private $_helper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     * @param Helper $helper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        Helper $helper,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction
    )
    {
        parent::__construct($context);
        $this->registry = $registry;
        $this->invoiceService = $invoiceService;
        $this->_transaction = $transaction;
        $this->_helper = $helper;
        // Fix for Magento2.3 adding isAjax to the request params, CsrfAwareAction Magento2.3 compatibility
        if (interface_exists("\Magento\Framework\App\CsrfAwareActionInterface")) {
            $request = $this->getRequest();
            if ($request instanceof \Magento\Framework\App\Request\Http && $request->isPost()) {
                $request->setParam('isAjax', true);
            }
        }
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $request = $objectManager->get('\Magento\Framework\App\Request\Http');
        $requestPostPayload = $request->getPost();
        $urlInterface = $objectManager->get('\Magento\Framework\UrlInterface');

        $checkoutSession = $objectManager->get('\Magento\Checkout\Model\Session');

        $redirectUrl = $this->_url->getUrl('checkout/onepage/failure/');
        if (isset($checkoutSession)) {
            $orderId = $checkoutSession->getOrderId();
            if (!isset($orderId)) {
                $orderId = $request->getParam('orderid');
            }
        } else {
            $orderId = $request->getParam('orderid');
        }
        if (!isset($orderId)) {
            $redirectUrl = $urlInterface->getUrl('checkout/onepage/failure/');
            $this->_redirect($redirectUrl);
            return;
        }
        $orders = $objectManager->get('Magento\Sales\Model\Order');
        $order = $orders->loadByIncrementId($orderId);
        if (false && isset($requestPostPayload) && isset($requestPostPayload['result'])) {
            if ($requestPostPayload['result'] == 'success') {
                $redirectUrl = $urlInterface->getUrl('checkout/onepage/success/');
            } else {
                $redirectUrl = $urlInterface->getUrl('checkout/onepage/failure/');
            }
        }
        $payment = $order->getPayment();
        try {
			$params = array(
				"allowOriginUrl" => $urlInterface->getBaseUrl(),
				"merchantTxId" => $order->getRealOrderId()
			);
            $gatewayTransaction = $this->executeGatewayTransaction(Configurable::ACTION_GET_STATUS, $params);
        } catch (Exception $e) {
            $this->_redirect($urlInterface->getUrl('checkout/onepage/failure/'));
            return;
        }

        if ($gatewayTransaction['result'] == 'success') {
            $realStatus = $gatewayTransaction->status;
            if ($realStatus == 'SET_FOR_CAPTURE') { //PURCHASE was successful
                if($order->getStatus() != \Magento\Sales\Model\Order::STATE_PROCESSING && $order->getStatus() != \Magento\Sales\Model\Order::STATE_COMPLETE){
                    $order->setState("Paid")
                        ->setStatus("pending")
                        ->addStatusHistoryComment(__('Order status paid'))
                        ->setIsCustomerNotified(true);
                    $order->save();
                    try {
                        $this->_helper->generateInvoice($order, $this->invoiceService, $this->_transaction);
                    } catch (\Exception $e) {
                        //log
                    }
                }
                $redirectUrl = $urlInterface->getUrl('checkout/onepage/success/');
            } else if ($realStatus == 'NOT_SET_FOR_CAPTURE') { // AUTH was successful
                if($order->getState() == 'Authorized'){
                }else{
                    $order->setState('Authorized')
                    ->setStatus("pending")
                    ->addStatusHistoryComment(__('Order payment authorized'))
                    ->setIsCustomerNotified(true);
                    $order->save();
                    $payment->setIsTransactionClosed(false);
                    
                    $payment->resetTransactionAdditionalInfo()
                    ->setTransactionId($order->getRealOrderId());
                    
                    $transaction = $payment->addTransaction(Transaction::TYPE_AUTH, null, true);
                    $transaction->setIsClosed(0);
                    $transaction->save();
                    $payment->save();
                }
                // TODO: add auto-capture??
                $redirectUrl = $urlInterface->getUrl('checkout/onepage/success/');
            } else if (
                $realStatus == 'CAPTURED') { // transaction captured
                    if($order->getStatus() != \Magento\Sales\Model\Order::STATE_PROCESSING && $order->getStatus() != \Magento\Sales\Model\Order::STATE_COMPLETE){
                        $order->setState("Paid")
                            ->setStatus("pending")
                            ->addStatusHistoryComment(__('Order status paid'))
                            ->setIsCustomerNotified(true);
                        $order->save();
                        try {
                            $this->_helper->generateInvoice($order, $this->invoiceService, $this->_transaction);
                        } catch (\Exception $e) {
                            //log
                        }
                    }
                $redirectUrl = $urlInterface->getUrl('checkout/onepage/success/');
            } else if ($realStatus == 'SUCCESS') {
                $redirectUrl = $urlInterface->getUrl('checkout/onepage/success/');
            } else if ($realStatus == 'DECLINED' ||
                $realStatus == 'ERROR') {
                $message = 'Order cancelled due to failed transaction: ' . $gatewayTransaction->merchantTxId . '(' . $gatewayTransaction->txId . ') failed: ';
                if ($gatewayTransaction->errors != null) {
                    $message = $message . implode("|", $gatewayTransaction->errors);
                }
                $order->setState("canceled")
                    ->setStatus("canceled")
                    ->addStatusHistoryComment(__('Order cancelled due to failed transaction'))
                    ->setIsCustomerNotified(true);
                $order->save();
                $redirectUrl = $urlInterface->getUrl('checkout/onepage/failure/');
            } else if ($realStatus == 'VERIFIED') {
                $redirectUrl = $urlInterface->getUrl('checkout/onepage/success/');
            } else {
                $order->setState(Order::STATE_CANCELED)
                    ->setStatus('canceled')
                    ->addStatusHistoryComment(__('Order status canceled'))
                    ->setIsCustomerNotified(true);
                $order->save();
                $redirectUrl = $urlInterface->getUrl('checkout/onepage/failure/');
            }
        } else if ($gatewayTransaction['result'] == 'failure') {
            $order->setState("canceled")
                ->setStatus("canceled")
                ->addStatusHistoryComment(__('Order cancelled due to failed transaction'))->setIsCustomerNotified(true);
            $order->save();
            $redirectUrl = $urlInterface->getUrl('checkout/onepage/failure/');
        } else {
            $order->setState("canceled")
                ->setStatus("canceled")
                ->addStatusHistoryComment('Order cancelled due to failed transaction: ' . $gatewayTransaction->merchantTxId . '(' . $gatewayTransaction->txId . ') failed: ' . implode("|", $gatewayTransaction->errors))->setIsCustomerNotified(true);
            $order->save();
            $redirectUrl = $urlInterface->getUrl('checkout/onepage/failure/');
        }
        $params['redirectUrl'] = $redirectUrl;

        $this->registry->register(\UniversalPay\Payment\Block\Response::REGISTRY_PARAMS_KEY, $params);

        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }

    /**
     * @param $order
     * @param $action
     * @return mixed
     */
    protected function executeGatewayTransaction($action, $params = array())
    {
		$apiOperation = RequestFactory::newRequest($action, $params);
		$this->_helper->setCommonParams($apiOperation);
		$result = $apiOperation->execute();
		
        return $result;
    }
}