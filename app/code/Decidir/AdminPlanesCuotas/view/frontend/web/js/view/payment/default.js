/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'ko',
        'jquery',
        'uiComponent',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'uiRegistry',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Ui/js/model/messages',
        'uiLayout',
        'Magento_Checkout/js/action/redirect-on-success',
        'Magento_Checkout/js/model/totals',
        'Decidir_AdminPlanesCuotas/js/action/get-payment-information'
    ],
    function (
        ko,
        $,
        Component,
        placeOrderAction,
        selectPaymentMethodAction,
        quote,
        customer,
        paymentService,
        checkoutData,
        checkoutDataResolver,
        registry,
        additionalValidators,
        Messages,
        layout,
        redirectOnSuccessAction,
        totals,
        getPaymentInformationAction
    ) {
        'use strict';

        return Component.extend({
            redirectAfterPlaceOrder: true,
            isPlaceOrderActionAllowed: ko.observable(quote.billingAddress() != null),


            /**
             * After place order callback
             */
            afterPlaceOrder: function () {
                //OBTENER TOKEN DE PAGO Y HACER PAGO
                console.log('FUnción afterPlaceOrder');
               /* console.log('QUOTE ID: '+quote.getQuoteId());
              require(['jquery', 'jquery/ui'], function($){         
                $.ajax('/spsdecidir/payment/authorizeAnswer',
                {
                    method  : 'POST',
                    data    :
                    {
                        bin      : decidir_bin,
                        quote_id   : quote.getQuoteId(),
                        tarjeta_sps: decidir_tarjeta_sps,
                        cuota: decidir_cuota,
                        detalles_pago: decidir_detalles_pago,
                        holderName: decidir_holderName,
                        lastDigits: decidir_lastDigits,
                        expirationMonth: decidir_expirationMonth,
                        expirationYear: decidir_expirationYear

                    },                                    
                    success : function (responsePago)
                    {
                          console.log('Pago. Respuesta: '+responsePago)
                          //var res = JSON.parse(response);
                          //if(res["status"]=="approved"){
                            //console.log('ok pagado'+response+' - Estado: '+res["status"]);
                          //}else{
                            //console.log('Error al pagar'+response+' - Estado: '+res["status"]);
                          //}
                          
                    },
                    error   : function (e, status)
                    {
                          console.log('Error pagado'+responsePago);
                    }
                });
              });     */           
                
            },

            /**
             * Initialize view.
             *
             * @return {exports}
             */
            initialize: function () {
                var billingAddressCode,
                    billingAddressData,
                    defaultAddressData;

                this._super().initChildren();
                quote.billingAddress.subscribe(function (address) {
                    this.isPlaceOrderActionAllowed(address !== null);
                }, this);
                checkoutDataResolver.resolveBillingAddress();

                billingAddressCode = 'billingAddress' + this.getCode();
                registry.async('checkoutProvider')(function (checkoutProvider) {
                    defaultAddressData = checkoutProvider.get(billingAddressCode);

                    if (defaultAddressData === undefined) {
                        // Skip if payment does not have a billing address form
                        return;
                    }
                    billingAddressData = checkoutData.getBillingAddressFromData();

                    if (billingAddressData) {
                        checkoutProvider.set(
                            billingAddressCode,
                            $.extend(true, {}, defaultAddressData, billingAddressData)
                        );
                    }
                    checkoutProvider.on(billingAddressCode, function (providerBillingAddressData) {
                        checkoutData.setBillingAddressFromData(providerBillingAddressData);
                    }, billingAddressCode);
                });

                return this;
            },

            /**
             * Initialize child elements
             *
             * @returns {Component} Chainable.
             */
            initChildren: function () {
                this.messageContainer = new Messages();
                this.createMessagesComponent();

                return this;
            },

            /**
             * Create child message renderer component
             *
             * @returns {Component} Chainable.
             */
            createMessagesComponent: function () {

                var messagesComponent = {
                    parent: this.name,
                    name: this.name + '.messages',
                    displayArea: 'messages',
                    component: 'Magento_Ui/js/view/messages',
                    config: {
                        messageContainer: this.messageContainer
                    }
                };

                layout([messagesComponent]);

                return this;
            },

            /**
             * Place order.
             */
            placeOrder: function (data, event) {
                var self = this;

                if (event) {
                    event.preventDefault();
                }

                if (this.validate() && additionalValidators.validate()) {
                    this.isPlaceOrderActionAllowed(false);

                    this.getPlaceOrderDeferredObject()
                        .fail(
                            function () {
                                self.isPlaceOrderActionAllowed(true);
                            }
                        ).done(
                            function () {
                                self.afterPlaceOrder();

                                if (self.redirectAfterPlaceOrder) {
                                    console.log('Redirect to controller validarPago');
                                    console.log('QUOTE ID: '+quote.getQuoteId());
                                    //console.log('method = '+this.item.method);
                                    //console.log('method = '+this.getData());
                                    //console.log('method = '+this);


                                    //redirectOnSuccessAction.execute();
                                    window.location.href="/spsdecidir/payment/validarPago";
                                }
                            }
                        );

                        return true;
                }

                return false;

            },

            getPlaceOrderDeferredObject: function () {
                return $.when(
                    placeOrderAction(this.getData(), this.messageContainer)
                );
            },

            /**
             * @return {Boolean}
             */
            selectPaymentMethod: function () {
                $( "input[name='payment[method]']" ).prop('disabled', true); //Radiobox de cada medio de pago

                /**
                 * Reseteo las selecciones de plan de pago y cuotas, al mismo tiempo que se limpia el formulario de
                 * tarjetas y elimina la layenda de reintegro de los totales.
                 */

                $('tr.leyenda-reintegro').remove();
                $('[name="tarjeta"]').prop('checked',false);
                $('.box-tarjeta').removeClass('tarjeta-seleccionada');
                $('.leyenda-reintegro').remove();
                $('.tarjetas-disponibles').removeClass('no-display-2');
                $('.selector-cuotas').removeClass('no-display-2');

                $('.plan-seleccionado').addClass('no-display-2');
                $('.sps-datos-tarjeta').hide();

                $('#sps-tarjeta-nombre').val('');
                $('#sps-tarjeta-numero').val('');
                $('#sps-tarjeta-vencimiento').val('');
                $('#sps-tarjeta-codigo-seguridad').val('');
                $('#sps-email').val('');
                $('#sps-tarjeta-codigo-seguridad-helper').val('');


				console.log('1 RESET DESCUENTO');
                $.ajax('/rest/V1/descuento/reset',
                {
                    method  : 'GET',
                    context : this,
                    success : function (response)
                    {
                        var deferred = $.Deferred();

                        totals.isLoading(true);
                        getPaymentInformationAction(deferred);
                        $.when(deferred).done(function () {
                            totals.isLoading(false);
                        });
                        $('tr.descuento_cuota').hide();
                    },
                    error   : function (e, status)
                    {
                        alert("Se produjo un error, por favor intentelo nuevamente");
                        $('.adminplanes-loader').addClass('no-display-2');
                    }
                });



				console.log('Reset costo');
                $.ajax('/rest/V1/costo/reset',
                {
                    method  : 'GET',
                    context : this,
                    success : function (response)
                    {
                        var deferred = $.Deferred();

                        totals.isLoading(true);
                        getPaymentInformationAction(deferred);
                        $.when(deferred).done(function () {
                            totals.isLoading(false);

                            $( "input[name='payment[method]']" ).prop('disabled', false); //Radiobox de cada medio de pago
                        });
                        $('.decidir_costo').hide();
                        $('.leyenda-tea').hide();
                        $('.leyenda-cft').hide();
                    },
                    error   : function (e, status)
                    {
                        alert("Se produjo un error, por favor intentelo nuevamente");
                        $('.adminplanes-loader').addClass('no-display-2');
                    }
                });




                selectPaymentMethodAction(this.getData());
                checkoutData.setSelectedPaymentMethod(this.item.method);

                return true;
            },

            isChecked: ko.computed(function () {
                return quote.paymentMethod() ? quote.paymentMethod().method : null;
            }),

            isRadioButtonVisible: ko.computed(function () {
                
                ////console.log("anda:"+ this.isChecked());    
                
                if (typeof(document.getElementById("decidir_spsdecidir")) != 'undefined' && document.getElementById("decidir_spsdecidir") != null)
                {
                    ////console.log("---decidir_spsdecidir esta definido");
                    ////console.log("---checked:",document.getElementById("decidir_spsdecidir").checked);
                    if(document.getElementById("decidir_spsdecidir").checked){
                        if(!($('.checkout-shipping-method:visible').length == 0))
                        {
                            var deferred = $.Deferred();
                            quote.setPaymentMethod(null);
                            getPaymentInformationAction(deferred);
                            jQuery.when(deferred).done(function () {
                                totals.isLoading(false);
                                fullScreenLoader.stopLoader();
                                $('tr.leyenda-reintegro').remove();
                                $('.leyenda-reintegro').remove();
                                $('.tarjeta-almacenada').hide();
                                $('#selector-planes').show();
                                $('.tarjetas-disponibles').show();
                                $('.cuotas-disponibles').addClass('no-display-2');
                                $('#decidir_spsdecidir').click();
                                $('.bancos-disponibles').addClass('no-display-2');
                            });
                        }
                    }
                    //document.getElementById("decidir_spsdecidir").checked=false;
                    //$(".payment-method-content").hide();
                    //////console.log("termina if");
                }
                ////console.log("isRadioButtonVisible",paymentService.getAvailablePaymentMethods());
                
                return paymentService.getAvailablePaymentMethods().length !== 1;
            }),

            /**
             * Get payment method data
             */
            getData: function () {
                return {
                    'method': this.item.method,
                    'po_number': null,
                    'additional_data': null
                };
            },

            /**
             * Get payment method type.
             */
            getTitle: function () {
                return this.item.title;
            },

            /**
             * Get payment method code.
             */
            getCode: function () {
                return this.item.method;
            },

            /**
             * @return {Boolean}
             */
            validate: function () {
                return true;
            },

            /**
             * @return {String}
             */
            getBillingAddressFormName: function () {
                return 'billing-address-form-' + this.item.method;
            },

            /**
             * Dispose billing address subscriptions
             */
            disposeSubscriptions: function () {
                // dispose all active subscriptions
                var billingAddressCode = 'billingAddress' + this.getCode();

                registry.async('checkoutProvider')(function (checkoutProvider) {
                    checkoutProvider.off(billingAddressCode);
                });
            }
        });
    }
);
