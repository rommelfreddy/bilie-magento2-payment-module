<?php declare(strict_types=1);

namespace Billiepayment\BilliePaymentMethod\Controller\Token;

use Billie\Sdk\Exception\BillieException;
use Billie\Sdk\Exception\UserNotAuthorizedException;
use Billie\Sdk\Model\Request\CreateSessionRequestModel;
use Billie\Sdk\Service\Request\CreateSessionRequest;
use Billiepayment\BilliePaymentMethod\Helper\BillieClientHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;

class Index implements ActionInterface
{

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var JsonFactory
     */
    private $jsonResultFactory;

    /**
     * @var BillieClientHelper
     */
    private $billieClientHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Context            $context,
        JsonFactory        $jsonResultFactory,
        BillieClientHelper $billieClientHelper,
        LoggerInterface    $logger
    )
    {
        $this->request = $context->getRequest();
        $this->jsonResultFactory = $jsonResultFactory;
        $this->billieClientHelper = $billieClientHelper;
        $this->logger = $logger;
    }

    public function execute()
    {
        $result = $this->jsonResultFactory->create();
        $merchantCustomerId = $this->request->getParam('merchant_customer_id');

        try {
            $request = new CreateSessionRequest($this->billieClientHelper->getBillieClientInstance());
            $response = $request->execute((new CreateSessionRequestModel())->setMerchantCustomerId($merchantCustomerId));

            $result->setData([
                'status' => true,
                'session_id' => $response->getCheckoutSessionId()
            ]);
        } catch (BillieException $e) {
            $result->setData([
                'status' => false,
                'message' => $e instanceof UserNotAuthorizedException ? __('This payment can not be performed currently. Place contact the vendor.') : $e->getMessage()
            ]);
            $result->setHttpResponseCode(500);

            $logError = $e instanceof UserNotAuthorizedException ? 'Please verify the api credentials.' : $e->getMessage();
            $this->logger->critical('Billie payment can not be initialized: ' . $logError, [
                'error_code' => $e->getBillieCode()
            ]);
        }

        return $result;
    }
}
