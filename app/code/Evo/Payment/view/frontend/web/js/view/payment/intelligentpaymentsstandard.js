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
                type: 'evo',
                component: 'Evo_Payment/js/view/payment/method-renderer/evostandard'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
