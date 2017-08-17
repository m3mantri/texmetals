/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'paradoxlabs_firstdata',
                component: 'ParadoxLabs_FirstData/js/view/payment/method-renderer/paradoxlabs_firstdata'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
