<?php
namespace Billiepayment\BilliePaymentMethod\Model;

class Log extends \Magento\Framework\Model\AbstractModel
{
    public function _construct()
    {
        $this->_init(ResourceModel\Log::class);
    }
}
