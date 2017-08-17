define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals',
        'Magento_Customer/js/customer-data'
    ],
    function (Component, quote, priceUtils, totals, storage) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Amasty_Extrafee/checkout/summary/fee',
            },
            totals: quote.getTotals(),
            /**
             * Get formatted price
             * @returns {*|String}
             */
            getValue: function() {
                var price = 0;
                if (this.totals()) {
                    price = totals.getSegment('amasty_extrafee').value;
                }
                return this.getFormattedPrice(price);
            },
            /**
             * @returns {string}
             */
            getMethods: function(){
                var title = '';
                if (this.totals() &&  totals.getSegment('amasty_extrafee').value > 0) {
                    title = totals.getSegment('amasty_extrafee').title;
                }
                return title;
            },
            /**
             * @override
             */
            isDisplayed: function () {
                if (this.totals() &&  totals.getSegment('amasty_extrafee').value > 0) {
                    return true;
                }
                return false;
            }
        });
    }
);