define(
    [
        'underscore',
        'Amasty_Extrafee/js/model/resource-url-manager',
        'Magento_Checkout/js/model/quote',
        'mage/storage',
        'Amasty_Extrafee/js/model/fees',
        'Magento_Checkout/js/model/error-processor'
    ],
    function (_, resourceUrlManager, quote, storage, feesService, errorProcessor) {
        "use strict";
        return function () {
            if (!feesService.rejectFeesLoading()) {
                var serviceUrl, payload;
                var requiredFields = ['countryId', 'region', 'regionId', 'postcode'];
                feesService.isLoading(true);
                serviceUrl = resourceUrlManager.getUrlForFetchFees(quote),
                    payload = {
                        addressInformation: {
                            address: _.pick(quote.shippingAddress(), requiredFields)
                        },
                        paymentMethod: ''
                    };

                if (quote.shippingMethod() && quote.shippingMethod()['method_code']) {
                    payload.addressInformation['shipping_method_code'] = quote.shippingMethod()['method_code'];
                    payload.addressInformation['shipping_carrier_code'] = quote.shippingMethod()['carrier_code'];
                }

                if (quote.paymentMethod() && quote.paymentMethod()['method']) {
                    payload.paymentMethod = quote.paymentMethod()['method'];
                }

                storage.post(
                    serviceUrl, JSON.stringify(payload), false
                ).done(
                    function (result) {
                        if (result['fees']) {
                            feesService.fees(result['fees']);
                        }

                        if (result['totals']) {
                            quote.setTotals(result['totals']);
                        }
                    }
                ).fail(
                    function (response) {
                        errorProcessor.process(response);
                    }
                ).always(
                    function () {
                        feesService.isLoading(false);
                    }
                );
            }
        }
    }
);
