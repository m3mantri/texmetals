define([
        'Amasty_Extrafee/js/fee/item',
        'Amasty_Extrafee/js/action/select-fee',
    ], function(
        Item,
        selectFeeAction
    ){
        'use strict';

        return Item.extend({
            /**
             * load fee option
             * @param optionId
             */
            loadOption: function(optionId){
                var optionsIds = [];
                if (optionId !== undefined) {
                    optionsIds.push(optionId);
                }
                selectFeeAction(this.feeId, optionsIds);
            }
        });
    }
)