<?php

namespace Billiepayment\BilliePaymentMethod\Model;

use Billie\Sdk\Util\WidgetHelper;
use Billiepayment\BilliePaymentMethod\Helper\Data;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class AdditionalConfigProvider implements ConfigProviderInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Data
     */
    protected $helper;

    public function __construct(
        Data                 $helper,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
    }

    public function getConfig()
    {
        if (!$this->scopeConfig->isSetFlag('payment/payafterdelivery/active', ScopeInterface::SCOPE_STORE)) {
            return [];
        }

        return [
            'billie_payment' => [
                'config' => [
                    'widget_url' => WidgetHelper::getWidgetUrl($this->scopeConfig->isSetFlag(Data::XML_PATH_CONFIG_SANDBOX_MODE, ScopeInterface::SCOPE_STORE)),
                    'duration' => $this->scopeConfig->getValue(Data::XML_PATH_CONFIG_DURATION, ScopeInterface::SCOPE_STORE),
                    'description' => $this->scopeConfig->getValue('payment/payafterdelivery/description', ScopeInterface::SCOPE_STORE),
                ]
            ]
        ];
    }
}
