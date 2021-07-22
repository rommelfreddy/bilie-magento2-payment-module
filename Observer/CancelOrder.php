<?php declare(strict_types=1);

namespace Billiepayment\BilliePaymentMethod\Observer;

use Billie\Sdk\Exception\BillieException;
use Billie\Sdk\Model\Request\OrderRequestModel;
use Billie\Sdk\Service\Request\CancelOrderRequest;
use Billiepayment\BilliePaymentMethod\Helper\BillieClientHelper;
use Billiepayment\BilliePaymentMethod\Helper\Data;
use Billiepayment\BilliePaymentMethod\Helper\Log;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class CancelOrder implements ObserverInterface
{

    /**
     * @var \Billiepayment\BilliePaymentMethod\Helper\Data
     */
    protected $helper;

    /**
     * @var \Billiepayment\BilliePaymentMethod\Helper\Log
     */
    protected $billieLogger;

    /**
     * @var \Billiepayment\BilliePaymentMethod\Helper\BillieClientHelper
     */
    private $billieClientHelper;

    public function __construct(Data $helper, Log $billieLogger, BillieClientHelper $billieClientHelper)
    {
        $this->helper = $helper;
        $this->billieLogger = $billieLogger;
        $this->billieClientHelper = $billieClientHelper;
    }

    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        $payment = $order->getPayment() ? $order->getPayment()->getMethodInstance() : null;

        if ($payment && $payment->getCode() !== Data::PAYMENT_METHOD_CODE) {
            return;
        }

        $requestModel = new OrderRequestModel($order->getBillieReferenceId());
        try {
            $billieClient = $this->billieClientHelper->getBillieClientInstance();

            (new CancelOrderRequest($billieClient))->execute($requestModel);

            $order->addCommentToStatusHistory(__('Billie Payment: The transaction with the id %1 was successfully canceled.', $order->getBillieReferenceId()));
            $order->save();

            $this->billieLogger->billieLog($order, $requestModel, 'canceled');
        } catch (BillieException $exception) {
            $this->billieLogger->billieLog($order, $requestModel, $exception);
            throw new LocalizedException(__($exception->getMessage()));
        }
    }
}
