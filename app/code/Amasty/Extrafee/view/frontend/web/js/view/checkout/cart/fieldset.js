define(
    [
        'Amasty_Extrafee/js/view/checkout/summary/fieldset'
    ],
    function (Component, quote, totals) {
        'use strict';

        return Component.extend({
            /**
             * @returns {*}
             */
            blockEnabled: function(){
                return window.checkoutConfig.amasty.extrafee.enabledOnCart == 1;
            }
        });
    }
);