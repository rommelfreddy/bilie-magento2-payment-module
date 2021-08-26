<?php declare(strict_types=1);

namespace Billiepayment\BilliePaymentMethod\Observer;

use Billie\Sdk\Exception\BillieException;
use Billie\Sdk\Model\Request\UpdateOrderRequestModel;
use Billie\Sdk\Service\Request\CheckoutSessionConfirmRequest;
use Billie\Sdk\Service\Request\UpdateOrderRequest;
use Billiepayment\BilliePaymentMethod\Helper\BillieClientHelper;
use Billiepayment\BilliePaymentMethod\Helper\Data;
use Billiepayment\BilliePaymentMethod\Helper\Log;
use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;

class CreateOrder implements ObserverInterface
{

    const PAYMENT_METHOD_CODE = 'payafterdelivery';

    /**
     * @var \Billiepayment\BilliePaymentMethod\Helper\Log
     */
    protected $billieLogger;

    /**
     * @var Data
     */
    protected $billieHelper;

    /**
     * @var \Billiepayment\BilliePaymentMethod\Helper\BillieClientHelper
     */
    private $billieClientHelper;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;


    public function __construct(
        Data $helper,
        Log $billieLogger,
        ScopeConfigInterface $scopeConfig,
        BillieClientHelper $billieClientHelper
    )
    {
        $this->billieHelper = $helper;
        $this->billieLogger = $billieLogger;
        $this->scopeConfig = $scopeConfig;
        $this->billieClientHelper = $billieClientHelper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {

        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        $payment = $order->getPayment();

        if ($payment && $payment->getCode() !== Data::PAYMENT_METHOD_CODE && $payment->getMethod() !== Data::PAYMENT_METHOD_CODE) {
            return;
        }

        $confirmModel = $this->billieHelper->createCheckoutSessionConfirmModel($order);
        if ($confirmModel === null) {
            return;
        }

        try {
            $billieClient = $this->billieClientHelper->getBillieClientInstance();

            $request = new CheckoutSessionConfirmRequest($billieClient);
            $billieOrder = $request->execute($confirmModel);

            $order->setData('billie_reference_id', $billieOrder->getUuid());

            if (($shippingAddress = $order->getShippingaddress()) && $this->areOrderAdressesIdentical($order)) {
                $deliveryAddress = $billieOrder->getDeliveryAddress();
                $shippingAddress->setData('company', $billieOrder->getCompany()->getName());
                $shippingAddress->setData('street', $deliveryAddress->getStreet() . ' ' . $deliveryAddress->getHouseNumber());
                $shippingAddress->setData('postcode', $deliveryAddress->getPostalCode());
                $shippingAddress->setData('city', $deliveryAddress->getCity());
                $shippingAddress->setData('country_id', $deliveryAddress->getCountryCode());
            }

            $billieDebtorAddress = $billieOrder->getCompany()->getAddress();
            if ($billingAddress = $order->getBillingaddress()) { // there is no reason that the address could be null, just to be safe ;-)
                $billingAddress->setData('company', $billieOrder->getCompany()->getName());
                $billingAddress->setData('street', $billieDebtorAddress->getStreet() . ' ' . $billieDebtorAddress->getHouseNumber());
                $billingAddress->setData('postcode', $billieDebtorAddress->getPostalCode());
                $billingAddress->setData('city', $billieDebtorAddress->getCity());
                $billingAddress->setData('country_id', $billieDebtorAddress->getCountryCode());
            }

            $payment->setData('billie_reference_id', $billieOrder->getUuid());
            $payment->setData('billie_viban', $billieOrder->getBankAccount()->getIban());
            $payment->setData('billie_vbic', $billieOrder->getBankAccount()->getBic());
            $payment->setData('billie_duration', $billieOrder->getDuration());
            $payment->setData('billie_company', $this->scopeConfig->getValue('general/store_information/name', ScopeInterface::SCOPE_STORE, $order->getStoreId()));

            $order->addStatusHistoryComment(__('Billie Payment: payment accepted for %1', $billieOrder->getUuid()));

            $order->save();
            $payment->save();

            // set increment id on billie gateway
            (new UpdateOrderRequest($billieClient))->execute(
                (new UpdateOrderRequestModel($billieOrder->getUuid()))
                    ->setOrderId($order->getIncrementId()));

            $this->billieLogger->billieLog($order, $confirmModel, $billieOrder);

        } catch (BillieException $e) {
            $errorMsg = __($e->getBillieCode());
            $this->billieLogger->billieLog($order, $confirmModel, $errorMsg);
            throw new LocalizedException(__($errorMsg));
        } catch (Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * @param Order $order
     * @return bool
     */
    public function areOrderAdressesIdentical(Order $order)
    {
        $sA = $order->getShippingaddress()->getData();
        $bA = $order->getBillingaddress()->getData();
        $useA = ['company', 'street', 'city', 'postcode', 'country_id'];
        foreach ($useA as $key) {
            if ($sA[$key] !== $bA[$key]) {
                return false;
            }

        }

        return true;
    }
}
