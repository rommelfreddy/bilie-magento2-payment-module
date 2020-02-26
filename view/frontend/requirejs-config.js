var config = {
    map: {

        '*': {

            shoppingCart:           'Magento_Checkout/js/shopping-cart',
            sidebar:                'Magento_Checkout/js/sidebar',
            checkoutLoader:         'Magento_Checkout/js/checkout-loader',
            checkoutData:           'Magento_Checkout/js/checkout-data',
            proceedToCheckout:      'Magento_Checkout/js/proceed-to-checkout',
            billiePayment:          'Magento_BilliePaymentMethod/js/billie-widget'

        }
    },
    urlArgs: "bust=" + (new Date()).getTime() // Disable require js cache
};