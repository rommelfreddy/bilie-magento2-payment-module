<?php

namespace Magento\BilliePaymentMethod\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    const sandboxMode = 'payment/payafterdelivery/sandbox';
    const apiConsumerKey = 'payment/payafterdelivery/consumer_key';
    const apiConsumerSecretKey = 'payment/payafterdelivery/consumer_secret_key';
    const duration = 'payment/payafterdelivery/duration';
    const housenumberField = 'billie_core/config/housenumber';
    const invoiceUrl = 'billie_core/config/invoice_url';


    /** @var mixed */
    protected $storeId = null;

    /**
     * @param mixed $storeId
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
    }

    /**
     * @return mixed
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function clientCreate()
    {
        return \Billie\HttpClient\BillieClient::create($this->getConfig(self::apiConsumerKey), $this->getConfig(self::apiConsumerSecretKey), $this->getMode());

    }


    public function sessionConfirmOrder($order){


        $billingAddress = $order->getBillingAddress();
        $payment = $order->getPayment();

        $command = new \Billie\Command\CheckoutSessionConfirm($order->getPayment()->getAdditionalInformation('token'));
        $command->duration = intval( $this->getConfig(self::duration,$this->getStoreId()) );

        // Company information
        $command->debtorCompany = new \Billie\Model\DebtorCompany();
        $command->debtorCompany->name = $payment->getBillieCompany()?$payment->getBillieCompany():$billingAddress->getCompany();
        $command->debtorCompany->addressStreet = $billingAddress->getStreet()[0];
        $command->debtorCompany->addressCity = $billingAddress->getCity();
        $command->debtorCompany->addressPostalCode = $billingAddress->getPostcode();
        $command->debtorCompany->addressCountry = $billingAddress->getCountryId();
        $command->debtorCompany->addressHouseNumber = $billingAddress->getStreet()[1];


        $command->amount = new \Billie\Model\Amount(($order->getBaseGrandTotal() - $order->getBaseTaxAmount())*100, $order->getGlobalCurrencyCode(), $order->getBaseTaxAmount()*100); // amounts are in cent!

        return $command;

    }


    public function mapCreateOrderData($order)
    {

        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();
        $payment = $order->getPayment();

        $customerId = $order->getCustomerId()?$order->getCustomerId():$order->getCustomerEmail();

        $command = new \Billie\Command\CreateOrder();

        // Company information
        $command->debtorCompany = new \Billie\Model\Company($customerId, $payment->getBillieCompany()?$payment->getBillieCompany():$shippingAddress->getCompany(), $this->mapAddress($billingAddress));
        $command->debtorCompany->legalForm = $payment->getBillieLegalForm()?$payment->getBillieLegalForm():'10001';
        $command->debtorCompany->taxId = $payment->getBillieTaxId()?$payment->getBillieTaxId():'123456';
        $command->debtorCompany->registrationNumber = $payment->getBillieRegistrationNumber()?$payment->getBillieRegistrationNumber():'Amtsgericht Charlottenburg';

        // Information about the person
        $command->debtorPerson = new \Billie\Model\Person($order->getCustomerEmail());
        $command->debtorPerson->salution = ($payment->getBillieSalutation() ? 'm' : 'f');

        $command->deliveryAddress = $this->mapAddress($shippingAddress);

        // Amount
        $command->amount = new \Billie\Model\Amount(($order->getBaseGrandTotal() - $order->getBaseTaxAmount())*100, $order->getGlobalCurrencyCode(), $order->getBaseTaxAmount()*100); // amounts are in cent!

        // Define the due date in DAYS AFTER SHIPPMENT
        $command->duration = intval( $this->getConfig(self::duration,$this->getStoreId()) );

        return $command;
    }

    /**
     * @param $order
     * @return CancelOrder
     *
     */

    public function cancel($order){

        return  new \Billie\Command\CancelOrder($order->getBillieReferenceId());

    }


    public function updateOrder($order)
    {
        $command = new \Billie\Command\UpdateOrder($order->getBillieReferenceId());
        $command->orderId = $order->getIncrementId();

        return $command;
    }

    public function reduceAmount($order){

        $command = new \Billie\Command\ReduceOrderAmount($order->getBillieReferenceId());
        $newTotalAmount = $order->getData('base_total_invoiced') - $order->getData('base_total_offline_refunded') - $order->getData('base_total_online_refunded');
        $newTaxAmount = $order->getData('base_tax_invoiced') - $order->getData('base_tax_refunded');
//        $command->invoiceNumber = $order->getInvoiceCollection()->getFirstItem()->getIncrementId();
//        $command->invoiceUrl = $this->getConfig(self::invoiceUrl,$this->getStoreId()).'/'.$order->getIncrementId().'.pdf';
        $command->amount = new \Billie\Model\Amount(($newTotalAmount-$newTaxAmount)*100, $order->getData('base_currency_code'), $newTaxAmount*100);

        return $command;

    }

    public function mapShipOrderData($order)
    {
        $command = new \Billie\Command\ShipOrder($order->getBillieReferenceId());

        $command->orderId = $order->getIncrementId();
        $command->invoiceNumber = $order->getInvoiceCollection()->getFirstItem()->getIncrementId();

        $command->invoiceUrl = $this->getConfig(self::invoiceUrl) . '/' . $order->getIncrementId() . '.pdf';

        return $command;
    }

    public function mapAddress($address)
    {

//        if(!$this->getConfig(self::housenumberField,$this->getStoreId())) {
//            $housenumber = '';
//        }else if($this->getConfig(self::housenumberField,$this->getStoreId()) != 'street'){
//            $housenumber = $address->getData($this->getConfig(self::housenumberField,$this->getStoreId()));
//        }else{
//            $housenumber = $address->getStreet()[1];
//        }

        $housenumber = $address->getStreet()[1];

        $addressObj = new \Billie\Model\Address();
        $addressObj->street = $address->getStreet()[0];
        $addressObj->houseNumber = $housenumber;
        $addressObj->postalCode = $address->getPostcode();
        $addressObj->city = $address->getCity();
        $addressObj->countryCode = $address->getCountryId();

        return $addressObj;
    }

    /**
     * @param $bic
     * @return \Billie\Util\BillieBankaccountProvider
     */

    public function getBankAccountByBic($bic)
    {
        $billieBankaccountProvider = new \Billie\Util\BillieBankaccountProvider;
        return $billieBankaccountProvider->get($bic);

    }
    public function getMode(){

        return $this->getConfig(self::sandboxMode);

    }
}