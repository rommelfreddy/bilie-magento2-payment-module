<?php declare(strict_types=1);

namespace Billiepayment\BilliePaymentMethod\Controller\Token;

use Billie\Sdk\Exception\BillieException;
use Billie\Sdk\Model\Request\CreateSessionRequestModel;
use Billie\Sdk\Service\Request\CreateSessionRequest;
use Billiepayment\BilliePaymentMethod\Helper\BillieClientHelper;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Index extends Action
{

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $jsonResultFactory;

    /**
     * @var \Billiepayment\BilliePaymentMethod\Helper\BillieClientHelper
     */
    private $billieClientHelper;

    public function __construct(
        Context $context,
        JsonFactory $jsonResultFactory,
        BillieClientHelper $billieClientHelper
    )
    {
        parent::__construct($context);
        $this->jsonResultFactory = $jsonResultFactory;
        $this->billieClientHelper = $billieClientHelper;
    }

    public function execute()
    {
        $result = $this->jsonResultFactory->create();
        $merchantCustomerId = $this->_request->getParam('merchant_customer_id');

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
                'session_id' => $e->getMessage()
            ]);
        }

        return $result;
    }
}
