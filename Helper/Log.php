<?php

namespace Billiepayment\BilliePaymentMethod\Helper;

use Billie\Sdk\Exception\BillieException;
use Billie\Sdk\Model\Order;
use Billie\Sdk\Model\Request\AbstractRequestModel;
use Billiepayment\BilliePaymentMethod\Model\LogFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Serialize\Serializer\Json;

class Log extends AbstractHelper
{

    /**
     * @var \Billiepayment\BilliePaymentMethod\Model\LogFactory
     */
    protected $_billieLogger;

    /**
     * @var \Billiepayment\BilliePaymentMethod\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $json;

    public function __construct(Context $context, LogFactory $billieLogger, Data $helper, Json $json)
    {
        parent::__construct($context);
        $this->_billieLogger = $billieLogger;
        $this->helper = $helper;
        $this->json = $json;
    }

    public function billieLog(\Magento\Sales\Model\Order $order, AbstractRequestModel $request, $response = null)
    {
        $billieLogger = $this->_billieLogger->create();

        $state = null;
        if ($response instanceof Order) {
            $state = $response->getState();
        } elseif ($response instanceof BillieException) {
            $state = $response->getBillieCode() . ': ' . $response->getMessage();
        } elseif (is_string($response)) {
            $state = $response;
        }

        $logData = [
            'store_id' => $order->getStoreId(),
            'order_id' => $order->getId(),
            'reference_id' => $order->getBillieReferenceId(),
            'transaction_tstamp' => date('Y-m-d H:i:s', time()),
            'created_at' => $order->getCreatedAt(),
            'customer_id' => $order->getCustomerId(),
            'billie_state' => $state,
            'mode' => $this->helper->getMode() ? 'sandbox' : 'live',
            'request' => $this->json->serialize($request->toArray())
        ];

        $billieLogger->addData($logData);
        $billieLogger->save();
    }
}
