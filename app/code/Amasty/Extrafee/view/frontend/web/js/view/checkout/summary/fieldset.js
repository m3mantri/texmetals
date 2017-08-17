define([
        'underscore',
        'jquery',
        'mageUtils',
        'uiLayout',
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'Amasty_Extrafee/js/model/fees',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/model/cart/totals-processor/default',
        'Amasty_Extrafee/js/action/load-fees',
    ], function(
        _,
        $,
        utils,
        layout,
        Component,
        quote,
        feesService,
        shippingService,
        totalsDefaultProvider,
        loadFees
    ) {
        'use strict';
        return Component.extend({
            defaults: {
                listens: {
                    elems: 'updateTotals'
                },
                components:
                {
                    dropdown: 'Amasty_Extrafee/js/fee/item/dropdown',
                    checkbox: 'Amasty_Extrafee/js/fee/item/checkbox',
                    radio: 'Amasty_Extrafee/js/fee/item'
                }
            },
            /**
             * check that currently loaded fees has selection option
             * @returns {boolean}
             */
            hasFees: function(){
                var fees = _.filter(this.elems(), function(item){
                    var vals = [];
                    if (item.currentValue() !== false && item.currentValue() !== null){
                        vals = item.currentValue();
                    }
                    return vals.length > 0;
                });
                return fees.length > 0;
            },
            /**
             * esitamate totals of fees selected
             * @param elems
             */
            updateTotals: function(elems){
                if (this.initChildCount === elems.length && this.hasFees()){
                    totalsDefaultProvider.estimateTotals(quote.shippingAddress());
                }
            },
            /**
             * @returns {exports.initialize}
             */
            initialize: function(){
                this._super();
                if (this.blockEnabled()) {
                    quote.paymentMethod.subscribe(loadFees);
                    quote.shippingMethod.subscribe(loadFees);
                    feesService.fees.subscribe(this.updateFees.bind(this));
                }
                return this;
            },
            /**
             * update fees after collect totals
             * @param fees
             */
            updateFees: function(fees){
                var names = {};
                _.each(fees, function (fee) {
                    var name = this.name + '.fee.' + fee.id;
                    var elem = this.findChildByName(name);
                    if (!elem) {
                        var config = utils.extend({}, {
                            parent: this.name,
                            name: 'fee.' + fee.id,
                            component: this.components[fee.frontend_type],
                            options: fee.base_options,
                            label: fee.name,
                            description: fee.description,
                            frontendType: fee.frontend_type,
                            feeId: fee.id,
                            currentValue: fee.current_value
                        });

                        layout([config]);
                    } else {
                        elem.options(fee.base_options);
                        elem.visible(true)
                    }
                    names[name] = 1;
                }.bind(this));

                this.removeUnmatchedFees(names);
            },
            /**
             * remove fees elems
             * @param names
             */
            removeUnmatchedFees: function(names){
                this.elems.each(function(elem){
                    if (!names[elem.name]){
                        elem.visible(false)
                    }
                }.bind(this));
            },
            /**
             * find element by name
             * @param name
             */
            findChildByName: function (name) {
                return _.findWhere(this.elems(), {
                    name: name
                });
            },
            /**
             * @returns {*}
             */
            blockEnabled: function(){
                return window.checkoutConfig.amasty.extrafee.enabledOnCheckout == 1;
            }
        });
    }
);
