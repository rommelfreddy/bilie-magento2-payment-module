<?php
/**
 * Created by HIGHDIGITAL
 * @package     billie-magento-2
 * @copyright   Copyright (c) 2020 HIGHDIGITAL UG (https://www.highdigital.de)
 * User: ngongoll
 * Date: 19.01.20
 */

namespace Magento\BilliePaymentMethod\Observer;

use Magento\Framework\Event\ObserverInterface;
use \Magento\BilliePaymentMethod\Helper\Data;
use \Magento\BilliePaymentMethod\Helper\Log;
use \Magento\Framework\Exception\LocalizedException;
use \Magento\Store\Model\StoreManagerInterface;

class CancelOrder implements ObserverInterface
{

    const paymentmethodCode = 'payafterdelivery';

    protected $storeManager;
    protected $billieLogger;

    public function __construct(
        Data $helper,
        \Magento\BilliePaymentMethod\Helper\Log $billieLogger,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->helper = $helper;
        $this->billieLogger = $billieLogger;
        $this->_storeManager = $storeManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        $payment = $order->getPayment()->getMethodInstance();

        /** @var \Magento\Sales\Model\Order $order */

        if ($payment->getCode() != self::paymentmethodCode) {
            return;
        }

         try {

            $client = $this->helper->clientCreate();

            $billieCancelData = $this->helper->cancel($order);
            $client->cancelOrder($billieCancelData);

            $billieResponse = (object) ['state' => 'canceled'];
            $this->billieLogger->billieLog($order, $billieCancelData, $billieResponse);
            $order->addStatusHistoryComment(__('Billie PayAfterDelivery:  The transaction with the id %1 was successfully canceled.', $order->getBillieReferenceId()));
            $order->save();

        } catch (Exception $error) {

            throw new LocalizedException(__($error->getMessage()));

        }

    }
}