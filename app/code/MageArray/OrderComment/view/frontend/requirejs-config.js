/**
 * Copyright ? 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
var config = {
     map: {
        '*': {
	'Magento_Checkout/js/action/set-payment-information':'MageArray_OrderComment/js/action/set-payment-information',
    'Magento_Paypal/js/action/set-payment-method':'MageArray_OrderComment/js/action/set-payment-method',
        }
    },
	
	config: {
        mixins: {
            'Magento_Checkout/js/action/place-order': {
                'MageArray_OrderComment/js/model/place-order-mixin': true
            }
        }
    }
};
