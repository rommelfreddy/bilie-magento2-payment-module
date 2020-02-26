define([
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/model/quote',
    'Magento_Ui/js/model/messageList',
    'mage/validation'
], function ($, Component, quote, globalMessageList) {
    'use strict';
    var billie_config_data = {};
    var billie_order_data = {};
    return Component.extend({

        defaults: {
            template: 'Magento_BilliePaymentMethod/payment/payafterdelivery',
            company: '',
            gender: '',
            lastnaem: '',
            firstname: ''
        },
        /* Validation Form*/
        validateForm: function (form) {
            return $(form).validation() && $(form).validation('isValid');
        },
        initObservable: function () {
            this._super()
                .observe([
                    'company',
                    'gender',
                    'lastname',
                    'firstname'
                ]);
            return this;
        },

        getData: function () {
            return {
                'method': this.item.method,
                'additional_data': {
                    'company': $('#payafterdelivery_company').val(),
                    'token': $('#payafterdelivery_token').val(),
                }
            };
        },

        getCompany: function () {

            var billingAddress = quote.billingAddress();
            return billingAddress.company;
        },


        getLastname: function () {

            var billingAddress = quote.billingAddress();
            return billingAddress.lastname;
        },

        getFirstname: function () {

            var billingAddress = quote.billingAddress();
            return billingAddress.firstname;
        },

        requestGender: function () {
            return true;
        },

        setBillieConfigData: function () {

            var billingAddress = quote.billingAddress();
            var shippingAddress = quote.shippingAddress();
            var totals = quote.totals();
            var items = quote.getItems();
            var line_items = [];

            for (var id in items) {

                var item = items[id];
                var line_item = {
                    "external_id": item.product_id,
                    "title": item.sku,
                    "quantity": item.qty,
                    "amount": {
                        "net": Number(item.base_price_incl_tax - item.base_discount_amount - item.tax_amount).toFixed(2),
                        "gross": Number(item.base_price_incl_tax - item.base_discount_amount).toFixed(2),
                        "tax": Number(item.tax_amount).toFixed(2)
                    },
                }
                line_items.push(line_item);

            }
            return {
                "amount": {
                    "net": totals.grand_total.toFixed(2),
                    "gross": totals.base_grand_total.toFixed(2),
                    "tax": totals.base_tax_amount.toFixed(2)
                },
                "duration": window.checkoutConfig.billie_payment.config.duration,
                "delivery_address": {
                    "house_number": shippingAddress.street[1],
                    "street": shippingAddress.street[0],
                    "city": shippingAddress.city,
                    "postal_code": shippingAddress.postcode,
                    "country": shippingAddress.countryId
                },
                "debtor_company": {
                    "name": document.getElementById('payafterdelivery_company').value ? document.getElementById('payafterdelivery_company').value : billingAddress.company,
                    "address_house_number": shippingAddress.street[1],
                    "address_street": billingAddress.street[0],
                    "address_city": billingAddress.city,
                    "address_postal_code": billingAddress.postcode,
                    "address_country": billingAddress.countryId
                },
                "debtor_person": {
                    "salutation": document.getElementById('payafterdelivery_gender').value,
                    "firstname": document.getElementById('payafterdelivery_firstname').value ? document.getElementById('payafterdelivery_firstname').value : billingAddress.firstname,
                    "lastname": document.getElementById('payafterdelivery_lastname').value ? document.getElementById('payafterdelivery_lastname').value : billingAddress.lastname,
                    "email": quote.guestEmail
                },
                "line_items": line_items
            };

        },
        payWithBillie: function () {
            if (!this.validateForm('#payafterdelivery_billiepayment_form')) {
                return;
            }
            var self = this;
            var billingAddress = quote.billingAddress();
            var billie_order_data = this.setBillieConfigData();

            $.ajax({
                url: '/billiepayment/token',
                method: 'POST',
                data: { merchant_name:  billingAddress.company},
                showLoader: true,
                success: function (data, messageContainer) {
                    billie_config_data = {
                        'session_id': data.session_id,
                        'merchant_name': billingAddress.company
                    };
                    $('#payafterdelivery_token').val(data.session_id);
                    messageContainer = messageContainer || globalMessageList;

                    BillieCheckoutWidget.mount({
                        billie_config_data: billie_config_data,
                        billie_order_data: billie_order_data
                    })
                        .then(function success(ao) {

                            self.placeOrder();
                        })
                        .catch(function failure(err) {

                            // messageContainer.addErrorMessage({'message': $t('An Error accured please check input and try again')});
                            // code to execute when there is an error or when order is rejected
                            console.log('Error occurred', err);
                        });
                }
            });

        }
    });
});