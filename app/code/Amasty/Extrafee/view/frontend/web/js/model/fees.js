define(
    [
        'ko',
        'Magento_Checkout/js/model/quote'
    ],
    function (ko, quote) {
        return {
            fees: ko.observable([]),
            isLoading: ko.observable(false),
            rejectFeesLoading: ko.observable(false)
        }
    }
)