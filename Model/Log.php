<?php
namespace Billiepayment\BilliePaymentMethod\Model;
class Log extends \Magento\Framework\Model\AbstractModel {
    public function _construct(){
        $this->_init("Billiepayment\BilliePaymentMethod\Model\ResourceModel\Log");
    }
}