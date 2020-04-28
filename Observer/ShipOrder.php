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

class ShipOrder implements ObserverInterface
{

    const paymentmethodCode = 'payafterdelivery';
    const duration = 'payment/payafterdelivery/duration';

    protected $billieLogger;

    public function __construct(
        Data $helper,
        \Magento\BilliePaymentMethod\Helper\Log $billieLogger
    )
    {
        $this->helper = $helper;
        $this->billieLogger = $billieLogger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();

        $order = $shipment->getOrder();
        $payment = $order->getPayment()->getMethodInstance();

        /** @var \Magento\Sales\Model\Order $order */

        if ($payment->getCode() != self::paymentmethodCode) {
            return;
        }

        $invoiceIds = $order->getInvoiceCollection()->getAllIds();

        if (!$invoiceIds) {

            throw new LocalizedException(__('You have to create a invoice first'));

        } else {

            try {

                $billieShipData = $this->helper->mapShipOrderData($order);
                $client = $this->helper->clientCreate();
                $billieResponse = $client->shipOrder($billieShipData);
                $this->billieLogger->billieLog($order, $billieShipData, $billieResponse);
                $order->addStatusHistoryComment(__('Billie PayAfterDelivery: shipping information was send for %1. The customer will be charged now', $order->getIncrementId()));
                $order->save();

            } catch (Exception $error) {

                $this->billieLogger->billieLog($order, $billieShipData, $billieResponse);
                throw new LocalizedException(__($error->getMessage()));

            }
        }

    }
}