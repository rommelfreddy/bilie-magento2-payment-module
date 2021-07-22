<?php declare(strict_types=1);

namespace Billiepayment\BilliePaymentMethod\Observer;

use Billie\Sdk\Exception\BillieException;
use Billie\Sdk\Service\Request\ShipOrderRequest;
use Billiepayment\BilliePaymentMethod\Helper\BillieClientHelper;
use Billiepayment\BilliePaymentMethod\Helper\Data;
use Billiepayment\BilliePaymentMethod\Helper\Log;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class ShipOrder implements ObserverInterface
{

    /**
     * @var \Billiepayment\BilliePaymentMethod\Helper\Log
     */
    protected $billieLogger;

    /**
     * @var \Billiepayment\BilliePaymentMethod\Helper\BillieClientHelper
     */
    private $billieClientHelper;

    /**
     * @var \Billiepayment\BilliePaymentMethod\Helper\Data
     */
    private $billieHelper;

    public function __construct(
        Data $billieHelper,
        BillieClientHelper $billieClientHelper,
        Log $billieLogger
    )
    {
        $this->billieHelper = $billieHelper;
        $this->billieLogger = $billieLogger;
        $this->billieClientHelper = $billieClientHelper;
    }

    public function execute(Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();

        /** @var \Magento\Sales\Model\Order $order */
        $order = $shipment->getOrder();
        $payment = $order->getPayment() ? $order->getPayment()->getMethodInstance() : null;

        if ($payment && $payment->getCode() !== Data::PAYMENT_METHOD_CODE) {
            return;
        }

        $invoiceIds = $order->getInvoiceCollection()->getAllIds();
        if (!$invoiceIds) {
            throw new LocalizedException(__('You have to create a invoice first'));
        }

        $requestModel = $this->billieHelper->getShipOrderModel($order);
        try {
            $billieClient = $this->billieClientHelper->getBillieClientInstance();
            $billieOrder = (new ShipOrderRequest($billieClient))->execute($requestModel);

            $this->billieLogger->billieLog($order, $requestModel, $billieOrder);
            $order->addCommentToStatusHistory(__('Billie Payment: shipping information was send for %1. The customer will be charged now', $order->getIncrementId()));
            $order->save();
        } catch (BillieException $exception) {
            $this->billieLogger->billieLog($order, $requestModel, $exception);
            throw new LocalizedException(__($exception->getMessage()));
        }
    }
}
