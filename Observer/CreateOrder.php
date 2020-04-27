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
use Magento\Payment\Observer\AbstractDataAssignObserver;

class CreateOrder implements ObserverInterface
{

    const paymentmethodCode = 'payafterdelivery';
    const duration = 'payment/payafterdelivery/duration';

    protected $_storeManager;
    protected $_messageManager;
    protected $billieLogger;
    protected $logger;



    public function __construct(
        Data $helper,
        \Magento\BilliePaymentMethod\Helper\Log $logHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\BilliePaymentMethod\Helper\Log $billieLogger,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->helper = $helper;
        $this->logHelper = $logHelper;
        $this->_messageManager = $messageManager;
        $this->_storeManager = $storeManager;
        $this->billieLogger = $billieLogger;
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
            $billieResponse = (object)$client->checkoutSessionConfirm($billieSessionData);

            $order->setData('billie_reference_id', $billieResponse->uuid);

            if($this->compareAddress($order)){

                $shippingAddress = $order->getShippingaddress();
                $shippingAddress->setData('company', $billieResponse->debtor_company['name']);;
                $shippingAddress->setData('street', $billieResponse->debtor_company['address_street'] . ' ' . $billieResponse->debtor_company['address_house_number']);;
                $shippingAddress->setData('postcode', $billieResponse->debtor_company['address_postal_code']);
                $shippingAddress->setData('city', $billieResponse->debtor_company['address_city']);
                $shippingAddress->setData('country_id', $billieResponse->debtor_company['address_country']);

            }
            $billingAddress = $order->getBillingaddress();
            $billingAddress->setData('company', $billieResponse->debtor_company['name']);;
            $billingAddress->setData('street', $billieResponse->debtor_company['address_street'] . ' ' . $billieResponse->debtor_company['address_house_number']);;
            $billingAddress->setData('postcode', $billieResponse->debtor_company['address_postal_code']);
            $billingAddress->setData('city', $billieResponse->debtor_company['address_city']);
            $billingAddress->setData('country_id', $billieResponse->debtor_company['address_country']);


            $payment->setData('billie_viban', $billieResponse->bank_account['iban']);
            $payment->setData('billie_vbic', $billieResponse->bank_account['bic']);
            $payment->setData('billie_duration', intval( $this->helper->getConfig(self::duration,$order->getStoreId())));
            $payment->setData('billie_company', $payment->getAdditionalInformation('company'));

            $order->addStatusHistoryComment(__('Billie PayAfterDelivery: payment accepted for %1', $billieResponse->uuid));

            $order->save();
            $payment->save();

            $this->billieLogger->billieLog($order, $billieSessionData, $billieResponse);


        }catch (\Billie\Exception\BillieException $e){
            $errorMsg = __($e->getBillieCode());

            //$this->billieLogger->billieLog($order, $billieSessionData, $errorMsg);
            throw new LocalizedException(__($errorMsg));

        }catch (\Billie\Exception\InvalidCommandException $e){

            $errorMsg = __($e->getViolations()['0']);

            $this->billieLogger->billieLog($order, $billieSessionData, $billieResponse);
            throw new LocalizedException(__($errorMsg));

        }
        catch (Exception $e) {
            throw new LocalizedException(__($e->getMessage()));

        }

    }
    public function compareAddress($order) {
        $sA = $order->getShippingaddress()->getData();
        $bA = $order->getBillingaddress()->getData();
        $useA = array('company','street','city','postcode','country_id');
        $same = true;
        foreach($useA as $key){

            if($sA[$key] != $bA[$key]){
                $same = false;
                break;
            }

        }
        return $same;
    }
}