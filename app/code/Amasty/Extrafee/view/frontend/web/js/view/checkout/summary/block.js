define([
        'jquery',
        'ko',
        'Magento_Ui/js/form/form',
        'Amasty_Extrafee/js/model/fees',
    ], function(
        $,
        ko,
        Component,
        feesService
    ) {
        'use strict';
        return Component.extend({
            isLoading: feesService.isLoading,
            defaults: {
                template: 'Amasty_Extrafee/fee/block',
                modules: {
                    fieldset: '${ $.name }.amasty-extrafee-fieldsets'
                }
            },
            getTemplate: function () {
                return this.template;
            },
            /**
             * @returns {*}
             */
            visible: function(){
                var elems = this.fieldset().elems.filter(function(el){
                    return el.visible() === true;
                });

                return window.checkoutConfig.amasty.extrafee.enabledOnCheckout == 1 &&
                    elems.length > 0;
            }
        });
    }
);
