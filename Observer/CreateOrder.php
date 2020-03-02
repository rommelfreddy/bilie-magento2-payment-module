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
use \Magento\Framework\Message\ManagerInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;

class CreateOrder implements ObserverInterface
{

    const paymentmethodCode = 'payafterdelivery';
    const duration = 'payment/payafterdelivery/duration';

    protected $_storeManager;
    protected $_messageManager;
    protected $logger;
    protected $logHelper;

    public function __construct(
        Data $helper,
        \Magento\BilliePaymentMethod\Helper\Log $logHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->helper = $helper;
        $this->logHelper = $logHelper;
        $this->_messageManager = $messageManager;
        $this->_storeManager = $storeManager;
        $this->logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $order = $observer->getEvent()->getOrder();
        $payment = $order->getPayment();
        if ($payment->getCode() == self::paymentmethodCode) {
            return;
        }
        $this->helper->setStoreId($order->getStoreId());
        $billieSessionData = $this->helper->sessionConfirmOrder($order);

        try {
            // initialize Billie Client

            $client = $this->helper->clientCreate();

            $billieResponse = $client->checkoutSessionConfirm($billieSessionData);


            $order->setData('billie_reference_id', $billieResponse['uuid']);
            $order->addStatusHistoryComment(__('Billie PayAfterDelivery: payment accepted for %1', $billieResponse['uuid']));

            $payment->setData('billie_viban', $billieResponse['bank_account']['iban']);
            $payment->setData('billie_vbic', $billieResponse['bank_account']['bic']);
            $payment->setData('billie_duration', intval( $this->helper->getConfig(self::duration,$order->getStoreId())));
            $payment->setData('billie_company', $payment->getAdditionalInformation('company'));

            $order->save();
            $payment->save();

        }catch (\Billie\Exception\BillieException $e){
            $errorMsg = __($e->getBillieCode());

//            Mage::Helper('billie_core/log')->billieLog($order, $billieOrderData,$errorMsg );
            throw new LocalizedException(__($errorMsg));

        }catch (\Billie\Exception\InvalidCommandException $e){

            $errorMsg = __($e->getViolations()['0']);

//            Mage::Helper('billie_core/log')->billieLog($order, $billieOrderData,$errorMsg );
            throw new LocalizedException(__($errorMsg));

        }
        catch (Exception $e) {
            throw new LocalizedException(__($e->getMessage()));

        }

    }
}