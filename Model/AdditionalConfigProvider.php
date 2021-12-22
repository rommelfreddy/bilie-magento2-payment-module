<?php
/**
 * Created by HIGHDIGITAL
 * @package     billie-magento-2
 * @copyright   Copyright (c) 2020 HIGHDIGITAL UG (https://www.highdigital.de)
 * User: ngongoll
 * Date: 16.02.20
 */

namespace Billiepayment\BilliePaymentMethod\Model;

use Billiepayment\BilliePaymentMethod\Helper\Data;

class AdditionalConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    protected $helper;
    public function __construct(Data $helper, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
    }

    public function getConfig()
    {
        $output['billie_payment']['config']['duration'] = $this->scopeConfig->getValue('payment/payafterdelivery/duration');
        $output['billie_payment']['config']['description'] = $this->scopeConfig->getValue('payment/payafterdelivery/description');
        return $output;
    }
}
