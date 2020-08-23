define(
    [
        'jquery',
        'ko',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/view/shipping'
    ],
    function (
        $,
        ko,
        customer,
        Component
    ) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/shipping',
                shippingFormTemplate: 'Magento_Checkout/shipping-address/form',
                shippingMethodListTemplate: 'Magento_Checkout/shipping-address/shipping-method-list',
                shippingMethodItemTemplate: 'Jotadevs_RedCustoms/shipping-method-item'
            },

            initialize: function () {
                var self = this;
                this._super();

                if (!customer.isLoggedIn()) {
                    self.visible(false);
                }
            },

            getMediaUrl: function (methodCode) {
                var imageurl = window.checkoutConfig.mediaUrl + methodCode + '.png';
                return imageurl;
            }
        });
    }
);
