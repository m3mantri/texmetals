define(
    [
        'Amasty_Extrafee/js/view/checkout/summary/block'
    ],
    function (Component, quote, totals) {
        'use strict';

        return Component.extend({
            /**
             * @returns {*}
             */
            visible: function(){
                var elems = this.fieldset().elems.filter(function(el){
                    return el.visible() === true;
                });

                return window.checkoutConfig.amasty.extrafee.enabledOnCart == 1 &&
                    elems.length > 0;
            }
        });
    }
);