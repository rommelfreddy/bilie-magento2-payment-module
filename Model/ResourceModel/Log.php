<?php

namespace Billiepayment\BilliePaymentMethod\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;


class Log extends AbstractDb
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('billie_transaction_log', 'entity_id');
    }
}