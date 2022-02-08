<?php

namespace Billiepayment\BilliePaymentMethod\Observer;

use Billiepayment\BilliePaymentMethod\Helper\Data;
use Billiepayment\BilliePaymentMethod\Model\Payment\Payafterdelivery;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;

class PaymentMethodAvailable implements ObserverInterface
{

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        $methodInstance = $event->getData('method_instance');
        if (!$methodInstance instanceof Payafterdelivery) {
            return;
        }

        $consumerKey = $this->scopeConfig->getValue(Data::XML_PATH_CONFIG_CONSUMER_KEY, ScopeInterface::SCOPE_STORE);
        $secretKey = $this->scopeConfig->getValue(Data::XML_PATH_CONFIG_CONSUMER_SECRET_KEY, ScopeInterface::SCOPE_STORE);

        if (empty($consumerKey) || empty($secretKey)) {
            /** @var \Magento\Framework\DataObject $result */
            $result = $event->getData('result');
            $result->setData('is_available', false);
        }
    }
}
