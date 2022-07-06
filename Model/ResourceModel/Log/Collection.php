<?php

namespace Billiepayment\BilliePaymentMethod\Model\ResourceModel\Log;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \Billiepayment\BilliePaymentMethod\Model\Log::class,
            \Billiepayment\BilliePaymentMethod\Model\ResourceModel\Log::class
        );
    }
}
