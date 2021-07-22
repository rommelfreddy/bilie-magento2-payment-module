<?php declare(strict_types=1);

namespace Billiepayment\BilliePaymentMethod\Observer;

use Billie\Sdk\Exception\BillieException;
use Billie\Sdk\Model\Order;
use Billie\Sdk\Model\Request\OrderRequestModel;
use Billie\Sdk\Service\Request\CancelOrderRequest;
use Billie\Sdk\Service\Request\GetOrderDetailsRequest;
use Billie\Sdk\Service\Request\UpdateOrderRequest;
use Billiepayment\BilliePaymentMethod\Helper\BillieClientHelper;
use Billiepayment\BilliePaymentMethod\Helper\Data;
use Billiepayment\BilliePaymentMethod\Helper\Log;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class UpdateOrder implements ObserverInterface
{

    /**
     * @var \Billiepayment\BilliePaymentMethod\Helper\Log
     */
    protected $billieLogger;

    /**
     * @var \Billiepayment\BilliePaymentMethod\Helper\Data
     */
    protected $billieHelper;

    /**
     * @var \Billiepayment\BilliePaymentMethod\Helper\BillieClientHelper
     */
    private $billieClientHelper;

    public function __construct(Data $helper, Log $billieLogger, BillieClientHelper $billieClientHelper)
    {
        $this->billieHelper = $helper;
        $this->billieLogger = $billieLogger;
        $this->billieClientHelper = $billieClientHelper;
    }

    public function execute(Observer $observer)
    {

        $creditMemo = $observer->getEvent()->getCreditmemo();
        /** @var \Magento\Sales\Model\Order $order */
        $order = $creditMemo->getOrder();
        $payment = $order->getPayment() ? $order->getPayment()->getMethodInstance() : null;

        if ($payment && $payment->getCode() !== Data::PAYMENT_METHOD_CODE) {
            return;
        }

        $requestModel = null;
        try {

            $billieClient = $this->billieClientHelper->getBillieClientInstance();

            if ($order->canCreditmemo()) {

                $billieOrder = (new GetOrderDetailsRequest($billieClient))->execute(new OrderRequestModel($order->getBillieReferenceId()));

                if ($billieOrder->getState() === Order::STATE_COMPLETED) {
                    throw new LocalizedException(__('This transaction is already completed, refunds with billie payment are not possible anymore'));
                } else if ($billieOrder->getState() === Order::STATE_CANCELLED) {
                    throw new LocalizedException(__('This transaction is already canceled, refunds with billie payment are not possible anymore'));
                }

                $requestModel = $this->billieHelper->getReduceOrderModel($order);
                (new UpdateOrderRequest($billieClient))->execute($requestModel);

                $order->addCommentToStatusHistory(__('Billie Payment: The amount for transaction with the id %1 was successfully reduced.', $order->getBillieReferenceId()));
                $order->save();

                $this->billieLogger->billieLog($order, $requestModel);
            } else {
                $requestModel = new OrderRequestModel($order->getBillieReferenceId());
                (new CancelOrderRequest($billieClient))->execute($requestModel);

                $order->addCommentToStatusHistory(__('Billie Payment: The transaction with the id %1 was successfully canceled.', $order->getBillieReferenceId()));
                $order->save();

                $this->billieLogger->billieLog($order, $requestModel, 'canceled');
            }

        } catch (BillieException $exception) {
            $this->billieLogger->billieLog($order, $requestModel, $exception);
            throw new LocalizedException(__($exception->getMessage()));
        }
    }
}
