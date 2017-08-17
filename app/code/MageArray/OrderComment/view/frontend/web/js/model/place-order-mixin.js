/**
 * Copyright � 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';
  
    return function (placeOrderAction) {
        /** Override default place order action and add agreement_ids to request */
        return wrapper.wrap(placeOrderAction, function(originalAction, paymentData, redirectOnSuccess, messageContainer) {
          
            var commentForm = $('.payment-method._active form[data-role=order-comments]'),
                commentData = commentForm.serializeArray(),
				 comment = '';

               commentData.forEach(function(item) {
                comment= item.value;
				});
            if("extension_attributes" in paymentData) {
				paymentData.extension_attributes["order_comment"] = comment;
			} else {
				paymentData.extension_attributes = {order_comment: comment};
			}
            return originalAction(paymentData, redirectOnSuccess, messageContainer);
        });
    };
});
