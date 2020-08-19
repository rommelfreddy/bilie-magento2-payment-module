<?php
/**
 * Created by HIGHDIGITAL
 * @package     billie-magento-2
 * @copyright   Copyright (c) 2019 HIGHDIGITAL UG (https://www.highdigital.de)
 * User: ngongoll
 */

namespace Billiepayment\BilliePaymentMethod\Model\Payment;
class Payafterdelivery extends \Magento\Payment\Model\Method\AbstractMethod
{
    const PAYMENT_METHOD_PAYAFTERDELIVERY_CODE = 'payafterdelivery';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_PAYAFTERDELIVERY_CODE;

    /**
     * @var string
     */
    protected $_formBlockType = \Billiepayment\BilliePaymentMethod\Block\Form\Payafterdelivery::class;

    /**
     * @var string
     */
    protected $_infoBlockType = \Billiepayment\BilliePaymentMethod\Block\Info\Payafterdelivery::class;

//    /**
//     * Availability option
//     *
//     * @var bool
//     */
//    protected $_isOffline = true;
//
//    /**
//     * @return string
//     */
//    public function getPayableTo()
//    {
//        return $this->getConfigData('payable_to');
//    }
//
//    /**
//     * @return string
//     */
//    public function getMailingAddress()
//    {
//        return $this->getConfigData('mailing_address');
//    }
}