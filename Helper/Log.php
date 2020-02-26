<?php

namespace Magento\BilliePaymentMethod\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;

class Log extends AbstractHelper
{

    public function billieLog($order, $request, $response)
    {

        $log = $this->_objectManager->create('Magento\BilliePaymentMethod\Model\log');

        $logData = array(

            'store_id' => $order->getStoreId(),
            'order_id' => $order->getId(),
            'reference_id' => $response->referenceId ? $response->referenceId : $order->getBillieReferenceId(),
            'transaction_tstamp' => time(),
            'created_at' => $order->getCreatedAt(),
            'customer_id' => $order->getCustomerId(),
            'billie_state' => $response->state,
            'mode' => 'sandbox',
//            'mode' => $this->helper->getMode() ? 'sandbox' : 'live',
            'request' => serialize($request),
            'billie_state' => $response->state
        );
        $log->setData($logData);
        $log->save();

    }

}