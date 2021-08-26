define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {
    'use strict';

    rendererList.push({
        type: 'payafterdelivery',
        component: 'Billiepayment_BilliePaymentMethod/js/view/payment/method-renderer/payafterdelivery'
    });

    return Component.extend({});
});
