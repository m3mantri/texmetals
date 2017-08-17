/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/url-builder',
        'mageUtils'
    ],
    function(customer, urlBuilder, utils) {
        "use strict";
        return {
            getUrlForTotalsEstimationForFee: function(quote) {
                var params = (this.getCheckoutMethod() == 'guest') ? {cartId: quote.getQuoteId()} : {};
                var urls = {
                    'guest': '/amasty_extrafee/guest-carts/:cartId/totals-information',
                    'customer': '/amasty_extrafee/carts/mine/totals-information'
                };
                return this.getUrl(urls, params);
            },
            getUrlForFetchFees: function(quote){
                var params = (this.getCheckoutMethod() == 'guest') ? {cartId: quote.getQuoteId()} : {};
                var urls = {
                    'guest': '/amasty_extrafee/guest-carts/:cartId/fees-information',
                    'customer': '/amasty_extrafee/carts/mine/fees-information'
                };
                return this.getUrl(urls, params);
            },
            /** Get url for service */
            getUrl: function(urls, urlParams) {
                var url;

                if (utils.isEmpty(urls)) {
                    return 'Provided service call does not exist.';
                }

                if (!utils.isEmpty(urls['default'])) {
                    url = urls['default'];
                } else {
                    url = urls[this.getCheckoutMethod()];
                }
                return urlBuilder.createUrl(url, urlParams);
            },

            getCheckoutMethod: function() {
                return customer.isLoggedIn() ? 'customer' : 'guest';
            }
        };
    }
);
