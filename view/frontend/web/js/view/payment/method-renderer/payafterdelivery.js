define([
    'jquery',
    'knockout',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/customer',
    'Magento_Ui/js/model/messageList',
    'mage/url',
    'mage/validation',
], function ($, ko, Component, quote, customer, globalMessageList, urlBuilder) {
    'use strict';
    var billie_config_data = {};

    return Component.extend({
        defaults: {
            template: 'Billiepayment_BilliePaymentMethod/payment/payafterdelivery',
        },

        inputs: {
            gender: ko.observable(),
            firstname: ko.observable(),
            lastname: ko.observable(),
            company: ko.observable(),
            token: ko.observable(),
            widget_res: ko.observable(),
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

            quote.billingAddress.subscribe((address) => {
                if (address) {
                    this.inputs.firstname(address.firstname);
                    this.inputs.lastname(address.lastname);
                    if (address.company) {
                        this.inputs.company(address.company);
                    }
                }
            });

            return this;
        },

        getData: function () {
            return {
                'method': this.item.method,
                'additional_data': {
                    'company': this.inputs.company(),
                    'token': this.inputs.token(),
                    'widget_res': this.inputs.widget_res(),
                }
            };
        },

        requestGender: function () {
            return true;
        },

        splitStreet: function (address) {
            if (!address.street[1]) {
                var street = address.street[0].split(/(\d+)/g);

                address.street = [street[0], street[1] + street[2]];
            }
            return address
        },

        setBillieConfigData: function () {
            var billingAddress = this.splitStreet(quote.billingAddress());
            var shippingAddress = this.splitStreet(quote.shippingAddress());
            var totals = quote.totals();
            var items = quote.getItems();
            var line_items = [];

            var customerEmail = customer.isLoggedIn() ? customer.customerData.email : quote.guestEmail;

            for (var id in items) {

                var item = items[id];
                var line_item = {
                    "external_id": item.product_id,
                    "title": item.name,
                    "quantity": item.qty,
                    "description": item.description,
                    "brand": item.manufacturer,
                    "category": item.category,
                    "amount": {
                        "net": (Number(item.base_price_incl_tax) - (Number(item.base_discount_amount) / item.qty) - Number(item.tax_amount)).toFixed(2),
                        "gross": (Number(item.base_price_incl_tax) - (Number(item.base_discount_amount) / item.qty)).toFixed(2),
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
                    "name": this.inputs.company() ? this.inputs.company() : billingAddress.company,
                    "address_house_number": billingAddress.street[1],
                    "address_street": billingAddress.street[0],
                    "address_city": billingAddress.city,
                    "address_postal_code": billingAddress.postcode,
                    "address_country": billingAddress.countryId
                },
                "debtor_person": {
                    "salutation": this.inputs.gender(),
                    "firstname": this.inputs.firstname() ? this.inputs.firstname() : billingAddress.firstname,
                    "lastname": this.inputs.lastname() ? this.inputs.lastname() : billingAddress.lastname,
                    "email": customerEmail
                },
                "line_items": line_items
            };

        },

        payWithBillie: function () {
            if (!this.validateForm('#payafterdelivery_billiepayment_form')) {
                return;
            }
            var customerEmail = customer.isLoggedIn() ? customer.customerData.email : quote.guestEmail;
            var self = this;
            var billingAddress = quote.billingAddress();
            var billie_order_data = this.setBillieConfigData();

            $.ajax({
                url: urlBuilder.build('billiepayment/token'),
                method: 'POST',
                data: {
                    merchant_customer_id: customerEmail
                },
                showLoader: true,
                success: (data) => {
                    billie_config_data = {
                        'session_id': data.session_id,
                        'merchant_name': billingAddress.company
                    };
                    this.inputs.token(data.session_id);

                    BillieCheckoutWidget.mount({
                        billie_config_data: billie_config_data,
                        billie_order_data: billie_order_data
                    }).then((ao) => {
                        this.inputs.widget_res(JSON.stringify(ao.debtor_company));
                        self.placeOrder();
                    }).catch(function failure(err) {
                        console.log('Error occurred', err);
                    });
                }
            });

        }
    });
});
