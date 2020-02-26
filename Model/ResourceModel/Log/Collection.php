<?php

namespace Magento\BilliePaymentMethod\Model\ResourceModel\Contact;

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
        $this->_init('Magento\BilliePaymentMethod\Model\Log', 'Magento\BilliePaymentMethod\Model\ResourceModel\Log');
    }
}