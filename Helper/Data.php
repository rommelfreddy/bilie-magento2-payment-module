<?php declare(strict_types=1);

namespace Billiepayment\BilliePaymentMethod\Helper;

use Billie\Sdk\Model\Amount;
use Billie\Sdk\Model\DebtorCompany;
use Billie\Sdk\Model\Request\CheckoutSessionConfirmRequestModel;
use Billie\Sdk\Model\Request\GetBankDataRequestModel;
use Billie\Sdk\Model\Request\ShipOrderRequestModel;
use Billie\Sdk\Model\Request\UpdateOrderRequestModel;
use Billie\Sdk\Service\Request\GetBankDataRequest;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order;

class Data extends AbstractHelper
{
    const XML_PATH_CONFIG_SANDBOX_MODE = 'payment/payafterdelivery/sandbox';
    const XML_PATH_CONFIG_DURATION = 'payment/payafterdelivery/duration';
    const XML_PATH_CONFIG_INVOICE_URL = 'billie_core/config/invoice_url';
    const XML_PATH_CONFIG_CONSUMER_KEY = 'payment/payafterdelivery/consumer_key';
    const XML_PATH_CONFIG_CONSUMER_SECRET_KEY = 'payment/payafterdelivery/consumer_secret_key';

    const PAYMENT_METHOD_CODE = 'payafterdelivery';

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $json;

    public function __construct(Context $context, Json $json)
    {
        parent::__construct($context);
        $this->json = $json;
    }

    /**
     * @deprecated
     */
    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param Order $order
     * @return \Billie\Sdk\Model\Request\CheckoutSessionConfirmRequestModel|null
     */
    public function createCheckoutSessionConfirmModel(Order $order)
    {
        $payment = $order->getPayment();

        $widgetResponse = $this->json->unserialize($payment->getAdditionalInformation('widget_res'));

        return (new CheckoutSessionConfirmRequestModel())
            ->setSessionUuid($payment->getAdditionalInformation('token'))
            ->setDuration((int)$this->getConfig(self::XML_PATH_CONFIG_DURATION))
            ->setCompany((new DebtorCompany($widgetResponse)))
            ->setAmount((new Amount())
                ->setNet($order->getBaseGrandTotal() - $order->getBaseTaxAmount())
                ->setGross($order->getBaseGrandTotal())
                ->setTax($order->getBaseTaxAmount()));
    }

    public function getReduceOrderModel(Order $order)
    {
        $newTotalAmount = $order->getBaseTotalInvoiced() - $order->getBaseTotalOfflineRefunded() - $order->getBaseTotalOnlineRefunded();
        $newTaxAmount = $order->getTaxInvoiced() - $order->getTaxRefunded();

        return (new UpdateOrderRequestModel($order->getBillieReferenceId()))
            ->setAmount((new Amount())
                ->setNet($newTotalAmount - $newTaxAmount)
                ->setGross($newTotalAmount)
                ->setTax($newTaxAmount));
    }

    /**
     * @param Order $order
     * @return \Billie\Sdk\Model\Request\ShipOrderRequestModel
     */
    public function getShipOrderModel(Order $order)
    {
        return (new ShipOrderRequestModel($order->getBillieReferenceId()))
            ->setInvoiceNumber($order->getInvoiceCollection()->getFirstItem()->getIncrementId())
            ->setInvoiceUrl($this->getConfig(self::XML_PATH_CONFIG_INVOICE_URL) . '/' . $order->getIncrementId() . '.pdf');
    }

    /**
     * @param $bic
     * @return string|null
     */
    public function getBankAccountByBic($bic)
    {
        $bankDataRequest = new GetBankDataRequest();
        $response = $bankDataRequest->execute(new GetBankDataRequestModel());
        return $response->getBankName($bic);
    }

    public function getMode()
    {
        return $this->getConfig(self::XML_PATH_CONFIG_SANDBOX_MODE);
    }
}
