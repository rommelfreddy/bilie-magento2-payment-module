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
use \Magento\Framework\Message\ManagerInterface;

class UpdateOrder implements ObserverInterface
{

    const paymentmethodCode = 'payafterdelivery';
    const duration = 'payment/payafterdelivery/duration';

    protected $storeManager;
    protected $messageManager;
    protected $billieLogger;

    public function __construct(
        Data $helper,
        \Magento\BilliePaymentMethod\Helper\Log $billieLogger,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger)
    {
        $this->helper = $helper;
        $this->billieLogger = $billieLogger;
        $this->_messageManager = $messageManager;
        $this->_storeManager = $storeManager;
        $this->logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

            $this->logger->debug('creditmemo');
//            $this->logger->debug(print_r($billieResponse->debtor_company,true));
        $creditMemo = $observer->getEvent()->getCreditmemo();
        $order = $creditMemo->getOrder();
        $payment = $order->getPayment()->getMethodInstance();

        if ($payment->getCode() != self::paymentmethodCode) {

            $this->logger->debug('creditmemo '.$payment->getCode());
            return;
        }
        $this->logger->debug('creditmemo2');
        try {

            $client = $this->helper->clientCreate();

            $this->logger->debug('can creditmemo '. $order->canCreditmemo());
            if ($order->canCreditmemo()) {

                $this->logger->debug('can creditmemo');
                $billieUpdateData = $this->helper->reduceAmount($order);
                $billieResponse = $client->reduceOrderAmount($billieUpdateData);

                $this->logger->debug('can creditmemo2');
                $this->billieLogger->billieLog($order, $billieUpdateData, $billieResponse);

                if ($billieResponse->state == 'complete') {

                    $this->logger->debug('can creditmemo3');
                    $this->_messageManager->addNotice(Mage::Helper('billie_core')->__('This transaction is already closed, refunds with billie payment are not possible anymore'));

                } else {

                    $this->logger->debug('can creditmemo4');
                    $order->addStatusHistoryComment(__('Billie PayAfterDelivery:  The amount for transaction with the id %1 was successfully reduced.', $order->getBillieReferenceId()));
                    $order->save();

                }

                $this->logger->debug('can creditmemo5');
            } else {
                $this->logger->debug('cant creditmemo');
                $billieCancelData = $this->helper->cancel($order);
                $client->cancelOrder($billieCancelData);

                $billieResponse = (object)['state' => 'canceled'];
                $this->billieLogger->billieLog($order, $billieCancelData, $billieResponse);

                $order->addStatusHistoryComment(__('Billie PayAfterDelivery:  The transaction with the id %1 was successfully canceled.', $order->getBillieReferenceId()));
                $order->save();

            }

        } catch (Exception $error) {

            $this->logger->debug(print_r($error,true));
            throw new LocalizedException(__($error->getMessage()));

        }

    }
}