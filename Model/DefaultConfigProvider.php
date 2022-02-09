<?php

namespace Billiepayment\BilliePaymentMethod\Model;

use Magento\Catalog\Model\ResourceModel\AbstractResource;
use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Store\Model\StoreManagerInterface;

class DefaultConfigProvider
{
    /**
     * @var Category
     */
    private $categoryResourceModel;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Product
     */
    private $productResourceModel;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    public function __construct(
        Category              $categoryResourceModel,
        Product               $productResourceModel,
        StoreManagerInterface $storeManager,
        CheckoutSession       $checkoutSession
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->categoryResourceModel = $categoryResourceModel;
        $this->productResourceModel = $productResourceModel;
        $this->storeManager = $storeManager;
    }

    public function afterGetConfig(
        \Magento\Checkout\Model\DefaultConfigProvider $subject,
        array                                         $result
    )
    {
        $items = $result['totalsData']['items'];
        foreach ($items as $index => $item) {
            $quoteItem = $this->checkoutSession->getQuote()->getItemById($item['item_id']);
            $product = $quoteItem ? $quoteItem->getProduct() : null;
            if ($product) {

                $categoryIds = $product->getCategoryIds();
                $categoryName = isset($categoryIds[0]) ? $this->getAttributeValue($this->categoryResourceModel, $categoryIds[0], 'name') : null;

                $result['quoteItemData'][$index]['billie_additional_data'] = [
                    'manufacturer' => $this->getAttributeValue($this->productResourceModel, $product->getId(), 'manufacturer'),
                    'description' => $this->getAttributeValue($this->productResourceModel, $product->getId(), 'description'),
                    'category' => $categoryName,
                ];
            }
        }

        return $result;
    }

    private function getAttributeValue(AbstractResource $sourceModel, $entityId, $code)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $value = $sourceModel->getAttributeRawValue($entityId, $code, $storeId);
        $value = $value !== false ? $value : null;
        if (is_array($value)) {
            $value = count($value) === 0 ? null : array_shift($value);
        }

        if ($value !== null) {
            $attributeModel = $sourceModel->getAttribute($code);
            if ($attributeModel && $attributeModel->usesSource()) {
                $value = $attributeModel->getSource()->getOptionText($value);
            }
        }

        return is_string($value) ? strip_tags($value) : null;
    }
}
