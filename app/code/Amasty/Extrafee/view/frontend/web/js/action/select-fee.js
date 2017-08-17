define(
    [
        'underscore',
        'Amasty_Extrafee/js/model/resource-url-manager',
        'Magento_Checkout/js/model/quote',
        'mage/storage',
        'Magento_Checkout/js/model/totals',
        'Amasty_Extrafee/js/model/fees',
        'Magento_Checkout/js/model/error-processor'
    ],
    function (_, resourceUrlManager, quote, storage, totalsService, feesService, errorProcessor) {
        "use strict";
        return function (feeId, optionsIds) {
            var serviceUrl, payload;
            var requiredFields = ['countryId', 'region', 'regionId', 'postcode'];

            totalsService.isLoading(true);
            serviceUrl = resourceUrlManager.getUrlForTotalsEstimationForFee(quote),
                payload = {
                    information: {
                        fee_id: feeId,
                        options_ids: optionsIds
                    },
                    addressInformation: {
                        address: _.pick(quote.shippingAddress(), requiredFields)
                    }
                };

            if (quote.shippingMethod() && quote.shippingMethod()['method_code']) {
                payload.addressInformation['shipping_method_code'] = quote.shippingMethod()['method_code'];
                payload.addressInformation['shipping_carrier_code'] = quote.shippingMethod()['carrier_code'];
            }

            feesService.rejectFeesLoading(true);

            storage.post(
                serviceUrl, JSON.stringify(payload), false
            ).done(
                function (result) {
                    quote.setTotals(result);
                }
            ).fail(
                function (response) {
                    errorProcessor.process(response);
                }
            ).always(
                function () {
                    totalsService.isLoading(false);
                    feesService.rejectFeesLoading(false);
                }
            );
        }
    }
);
