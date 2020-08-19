/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* @api */
define([
        'uiComponent',
        'Billiepayment_Checkout/js/model/payment/renderer-list'
    ],
    function (Component, rendererList) {
        'use strict';
            rendererList.push(
                {
                    type: 'payafterdelivery',
                    component: 'Billiepayment_BilliePaymentMethod/js/view/payment/method-renderer/payafterdelivery'
                }
            );
        /** Add view logic here if needed */
        return Component.extend({});
    });