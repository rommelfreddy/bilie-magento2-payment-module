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
use \Magento\Framework\Exception\LocalizedException;
use \Magento\Store\Model\StoreManagerInterface;

class CancelOrder implements ObserverInterface
{

    const paymentmethodCode = 'payafterdelivery';

    protected $storeManager;

    public function __construct(Data $helper, \Magento\Store\Model\StoreManagerInterface $storeManager )
    {
        $this->helper = $helper;
        $this->_storeManager = $storeManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

//        $order->setData('billie_reference_id','13703b10-e2a2-4d77-becc-7880d30c564b');
        $payment = $order->getPayment()->getMethodInstance();

        /** @var \Magento\Sales\Model\Order $order */

        if ($payment->getCode() == self::paymentmethodCode) {
            return;
        }

         try {

            $client = $this->helper->clientCreate();

            $billieCancelData = $this->helper->cancel($order);
            $billieResponse = $client->cancelOrder($billieCancelData);

//             Mage::Helper('billie_core/log')->billieLog($order, $billieCancelData, $billieResponse);
            $order->addStatusHistoryComment(__('Billie PayAfterDelivery:  The transaction with the id %s was successfully canceled.', $order->getBillieReferenceId()));
            $order->save();

        } catch (Exception $error) {

            throw new LocalizedException(__($error->getMessage()));

        }

    }
}