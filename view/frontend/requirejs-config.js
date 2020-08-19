var config = {
    map: {

        '*': {

            shoppingCart:           'Billiepayment_Checkout/js/shopping-cart',
            sidebar:                'Billiepayment_Checkout/js/sidebar',
            checkoutLoader:         'Billiepayment_Checkout/js/checkout-loader',
            checkoutData:           'Billiepayment_Checkout/js/checkout-data',
            proceedToCheckout:      'Billiepayment_Checkout/js/proceed-to-checkout',
            billiePayment:          'Billiepayment_BilliePaymentMethod/js/billie-widget'

        }
    },
    urlArgs: "bust=" + (new Date()).getTime() // Disable require js cache
};