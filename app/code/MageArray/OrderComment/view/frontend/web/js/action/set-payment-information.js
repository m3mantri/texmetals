/**
 * Copyright � 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/action/get-totals',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function (quote, urlBuilder, storage, errorProcessor, customer, getTotalsAction, fullScreenLoader) {
        'use strict';

        return function (messageContainer, paymentData) {
            var serviceUrl,
                payload;
   
            var commentForm = jQuery('.payment-method._active form[data-role=order-comments]'),
            commentData = commentForm.serializeArray(),
            comment = '';

             commentData.forEach(function(item) {
                comment = item.value;
           });

            if("extension_attributes" in paymentData) {
                paymentData.extension_attributes["order_comment"] = comment;
            } else {
                paymentData.extension_attributes = {order_comment: comment};
            }
	
            /**
             * Checkout for guest and registered customer.
             */
            if (!customer.isLoggedIn()) {
                serviceUrl = urlBuilder.createUrl('/guest-carts/:cartId/set-payment-information', {
                    cartId: quote.getQuoteId()
                });
                payload = {
                    cartId: quote.getQuoteId(),
                    email: quote.guestEmail,
                    paymentMethod: paymentData,
                    billingAddress: quote.billingAddress()
                };
            } else {
                serviceUrl = urlBuilder.createUrl('/carts/mine/set-payment-information', {});
                payload = {
                    cartId: quote.getQuoteId(),
                    paymentMethod: paymentData,
                    billingAddress: quote.billingAddress()
                };
            }

            fullScreenLoader.startLoader();

            return storage.post(
                serviceUrl, JSON.stringify(payload)
            ).fail(
                function (response) {
                    errorProcessor.process(response, messageContainer);
                }
            ).always(
                function () {
                    fullScreenLoader.stopLoader();
                }
            );
        };
    }
);