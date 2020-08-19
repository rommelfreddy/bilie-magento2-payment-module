<?php

namespace Billiepayment\BilliePaymentMethod\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use \Billiepayment\BilliePaymentMethod\Model\LogFactory;

class Log extends AbstractHelper
{

    const sandboxMode = 'payment/payafterdelivery/sandbox';

    protected $_billieLogger;
    protected $helper;

    public function __construct(
        \Billiepayment\BilliePaymentMethod\Model\LogFactory  $billieLogger,
        \Billiepayment\BilliePaymentMethod\Helper\Data $helper

    ) {

        $this->_billieLogger = $billieLogger;
        $this->helper = $helper;
    }

    public function billieLog($order, $request, $response)
    {
        $billieLogger = $this->_billieLogger->create();

        $logData = array(

            'store_id' => $order->getStoreId(),
            'order_id' => $order->getId(),
            'reference_id' => $order->getBillieReferenceId(),
            'transaction_tstamp' => date('Y-m-d H:i:s',time()),
            'created_at' => $order->getCreatedAt(),
            'customer_id' => $order->getCustomerId(),
            'billie_state' => $response->state,
            'mode' => $this->helper->getMode() ? 'sandbox' : 'live',
            'request' => json_encode($request)
        );
        $billieLogger->addData($logData);
        $billieLogger->save();

    }

}