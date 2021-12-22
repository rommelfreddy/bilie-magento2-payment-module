<?php

namespace Billiepayment\BilliePaymentMethod\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $quoteTable = 'quote';
        $orderTable = 'sales_order';
        $orderPaymentTable = 'sales_order_payment';
        $quotePaymentTable = 'quote_payment';
        //Quote table
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($quoteTable),
                'billie_legal_form',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'billie_legal_form'
                ]
            );
        //Order table
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($orderTable),
                'billie_legal_form',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'billie_legal_form'
                ]
            );
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($orderTable),
                'billie_reference_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'billie_reference_id'
                ]
            );
        // Quote Payment table
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($quotePaymentTable),
                'billie_tax_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'billie_tax_id'
                ]
            );
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($quotePaymentTable),
                'billie_registration_number',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'billie_registration_number'
                ]
            );
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($quotePaymentTable),
                'billie_company',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'billie_company'
                ]
            );
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($quotePaymentTable),
                'billie_salutation',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'billie_salutation'
                ]
            );
        // Order Payment table
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($orderPaymentTable),
                'billie_viban',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'billie_viban'
                ]
            );
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($orderPaymentTable),
                'billie_vbic',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'billie_vbic'
                ]
            );
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($orderPaymentTable),
                'billie_tax_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'billie_tax_id'
                ]
            );
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($orderPaymentTable),
                'billie_registration_number',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'billie_registration_number'
                ]
            );
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($orderPaymentTable),
                'billie_company',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'billie_company'
                ]
            );
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($orderPaymentTable),
                'billie_salutation',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'billie_salutation'
                ]
            );
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($orderPaymentTable),
                'billie_duration',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => false,
                    'comment' => 'billie_salutation'
                ]
            );

        $tableName = $setup->getTable('billie_transaction_log');
        if (version_compare($context->getVersion(), '0.1.8', '<')) {
            $setup->getConnection()->changeColumn(
                $setup->getTable($tableName),
                'request',
                'request',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 64000
                ]
            );
        }

        $setup->endSetup();
    }
}
