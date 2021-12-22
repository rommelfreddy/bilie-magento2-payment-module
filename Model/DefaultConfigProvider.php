<?php
namespace Billiepayment\BilliePaymentMethod\Model;

use Magento\Checkout\Model\Session as CheckoutSession;

class DefaultConfigProvider
{
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    protected $_categoryFactory;
    /**
     * Constructor
     *
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        CheckoutSession $checkoutSession
    ) {
        $this->_categoryFactory = $categoryFactory;
        $this->checkoutSession = $checkoutSession;
    }

    public function afterGetConfig(
        \Magento\Checkout\Model\DefaultConfigProvider $subject,
        array $result
    ) {
        $items = $result['totalsData']['items'];
        foreach ($items as $index => $item) {
            $quoteItem = $this->checkoutSession->getQuote()->getItemById($item['item_id']);
            $result['quoteItemData'][$index]['manufacturer'] = $quoteItem->getProduct()->getAttributeText('manufacturer');
            $result['quoteItemData'][$index]['description'] = $quoteItem->getProduct()->getDescription();
            $categoryIds = $quoteItem->getProduct()->getCategoryIds();
            $category = $this->_categoryFactory->create()->load($categoryIds[0]);
            $result['quoteItemData'][$index]['category'] = $category->getName();
        }
        return $result;
    }
}
