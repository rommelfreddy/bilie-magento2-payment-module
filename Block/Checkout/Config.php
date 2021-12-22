<?php

namespace Billiepayment\BilliePaymentMethod\Block\Checkout;

use Billiepayment\BilliePaymentMethod\Helper\Data;

class Config extends \Magento\Framework\View\Element\Template
{

    const SANDBOX_BASE_URL = 'https://static-paella-sandbox.billie.io/checkout/billie-checkout.js';
    const PRODUCTION_BASE_URL = 'https://static.billie.io/checkout/billie-checkout.js';

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Billiepayment\BilliePaymentMethod\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->helper = $helper;
    }

    public function getBillieSrc()
    {
        return ($this->helper->getMode())?self::SANDBOX_BASE_URL:self::PRODUCTION_BASE_URL;
    }
}
