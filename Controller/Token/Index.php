<?php

namespace Magento\BilliePaymentMethod\Controller\Token;

use Magento\BilliePaymentMethod\Helper\Data;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
class Index extends Action
{

    protected $helper;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $jsonResultFactory;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \Magento\BilliePaymentMethod\Helper\Data $helper
    )
    {
        $this->helper = $helper;
        $this->jsonResultFactory = $jsonResultFactory;
        parent::__construct($context);
    }

    public function execute()
    {

        $merchantName = $this->_request->getParam('merchant_name');

        $client = $this->helper->clientCreate();

        $billieResponse = $client->checkoutSessionCreate($merchantName);

        $data = ['session_id' => $billieResponse];
        $result = $this->jsonResultFactory->create();
        $result->setData($data);
        return $result;
    }
}