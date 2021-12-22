<?php declare(strict_types=1);


namespace Billiepayment\BilliePaymentMethod\Helper;

use Billie\Sdk\Util\BillieClientFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class BillieClientHelper extends AbstractHelper
{

    /**
     * @return \Billie\Sdk\HttpClient\BillieClient
     * @throws \Billie\Sdk\Exception\BillieException
     */
    public function getBillieClientInstance()
    {
        return BillieClientFactory::getBillieClientInstance(
            $this->scopeConfig->getValue('payment/payafterdelivery/consumer_key', ScopeInterface::SCOPE_WEBSITE),
            $this->scopeConfig->getValue('payment/payafterdelivery/consumer_secret_key', ScopeInterface::SCOPE_WEBSITE),
            (bool)(int)$this->scopeConfig->getValue('payment/payafterdelivery/sandbox', ScopeInterface::SCOPE_WEBSITE)
        );
    }
}
