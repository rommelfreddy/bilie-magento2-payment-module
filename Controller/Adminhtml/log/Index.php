<?php

namespace Magento\BilliePaymentMethod\Controller\Adminhtml\Log;

class Index extends \Magento\Backend\App\Action
{

    protected $resultPageFactory = false;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_BilliePaymentMethod::index')
            ->getConfig()->getTitle()->prepend(__('Billie Logs'));
        return $resultPage;
    }

}
