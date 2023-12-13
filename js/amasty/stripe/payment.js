var AmStripePayment = Class.create({
    moduleCode: false,
    stripe: false,
    card: false,
    controller: false,
    type: false,
    paymentIntend: null,
    secret: null,
    isAuthorized: false,
    isLoading: false,
    isLoadingIntend: false,
    paymentRequestData: false,
    updateUrl: false,
    style: {
        base: {
            color: '#32325d',
            lineHeight: '18px',
            fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
            fontSmoothing: 'antialiased',
            fontSize: '16px',
            '::placeholder': {
                color: '#aab7c4'
            }
        },
        invalid: {
            color: '#fa755a',
            iconColor: '#fa755a'
        }
    },

    initialize: function (publicKey, moduleCode, controller, paymentIntendSecret, paymentRequestData, updateUrl) {
        this.moduleCode = moduleCode;
        this.controller = controller;
        this.paymentRequestData = paymentRequestData;
        if (this.secret !== paymentIntendSecret) {
            this.isAuthorized = false;
        }
        this.secret = paymentIntendSecret;
        this.updateUrl = updateUrl;
        if (this.stripe === false) {
            this.stripe = Stripe(publicKey);
            this.card = this.stripe.elements().create('card', {style: this.style, hidePostalCode: true});
            this.submitOrder = this.saveFunction.bindAsEventListener(this);

            this.card.addEventListener('change', function (event) {
                var displayError = document.getElementById('stripe-card-errors');

                if (event.error) {
                    displayError.textContent = event.error.message;
                } else {
                    this.setupAction();
                    displayError.textContent = '';
                    this.isAuthorized = false;
                }
            }.bind(this));
        }
        this.card.mount('#stripe-card-element');
        this.loadPaymentIntend();

        if (this.getPaymentMethod() === this.moduleCode) {
            this.setupAction();
        }
    },
    loadPaymentIntend: function() {
        if (this.isLoadingIntend) {
            return;
        }
        this.isLoadingIntend = true;
        this.stripe.retrievePaymentIntent(this.secret).then(function(result) {
            this.isLoadingIntend = false;
            if (result.error) {
                var errorElement = document.getElementById('stripe-card-errors');
                errorElement.textContent = result.error.message;
                return;
            }
            this.paymentIntend = result.paymentIntent;
            if (this.paymentIntend.status === 'requires_capture') {
                this.isAuthorized = true;
                this.card.unmount();
            } else {
                this.isAuthorized = false;
            }
        }.bind(this));
    },
    updateAmount: function() {
        return new Promise(function(resolve, reject) {
            new Ajax.Request(this.updateUrl, {
                method: 'get',
                onComplete: function (response) {
                    if (200 === response.status) {
                        this.secret = response.responseJSON;
                    }
                    resolve(true);
                }.bind(this)
            });
        }.bind(this));
    },

    saveOriginalFunction: function (callback) {
        if (!this.hasOwnProperty('amastyStripeOriginalSave')) {
            this.amastyStripeOriginalSave = callback;
        }
    },

    getPaymentMethod: function() {
        var method = $$("input[name='payment[method]']:checked");

        if (method.length) {
            return method.first().getValue();
        } else {
            method = $$("[name='payment[method]']");
            if (method.length) {
                return method.first().getValue();
            }
        }
    },

    setupAction: function() {
        switch (this.controller) {
            case 'onepage':
                try {
                    if (window.hasOwnProperty('completeCheckout')) { // Amasty One Step Checkout
                        this.saveOriginalFunction(completeCheckout);
                        completeCheckout = this.submitOrder;
                        this.type = 'amasty_osc';
                    } else { // Default Checkout
                        this.saveOriginalFunction(payment.save);
                        payment.__proto__.save = this.saveFunction.bind(this);
                        this.type = 'default';
                    }
                } catch (e) {
                    console.log(e);
                }
                break;
            case 'index':
                if (!this.hasOwnProperty('amastyStripeOriginalSave')) {
                    try {
                        if (window.hasOwnProperty('OnecolumnCheckout')) { // OnecolumnCheckout
                            this.saveOriginalFunction(OnecolumnCheckout.saveShippingAndPaymentMethods);
                            OnecolumnCheckout.saveShippingAndPaymentMethods = this.submitOrder;
                            this.type = 'tm_fire_onecolumn';
                        } else if (window.hasOwnProperty('FireCheckout')) { // FireCheckout
                            this.saveOriginalFunction(checkout.__proto__.save);
                            FireCheckout.prototype.save = this.submitOrder;
                            this.type = 'tm_fire';
                        } else if (window.hasOwnProperty('OnePage')) { //IWD Checkout
                            this.saveOriginalFunction(OnePage.prototype.tryPlaceOrder);
                            OnePage.prototype.tryPlaceOrder = this.submitOrder;
                            this.type = 'iwd_osc';
                        } else {
                            this.saveOriginalFunction(payment.save);
                            checkout.__proto__.save = this.saveFunction.bind(this);
                            this.type = 'iwd_osc_v2';
                        }
                    } catch (e) {
                        console.log(e.message);
                    }
                }

                break;
            case 'multishipping':
                var button = document.getElementById('payment-continue');

                if (button.length !== 0) {
                    button.stopObserving();
                    button.observe('click', this.submitOrder);
                }
                break;
            case 'sales_order_create':
            case 'sales_order_edit':
                this.saveOriginalFunction(order.submit);
                order.submit = this.submitOrder;
                break;
        }
    },

    completeAction: function() {
        switch (this.controller) {
            case 'onepage':
                try {
                    this.amastyStripeOriginalSave.apply(payment);
                } catch (e) {
                    this.amastyStripeOriginalSave();
                }
                break;
            case 'index':
                try {
                    if (this.type === 'tm_fire_onecolumn') {
                        OnecolumnCheckout.saveShippingAndPaymentMethods = this.amastyStripeOriginalSave;
                        $$('.step-shipping-payment-method .button.next').first().click()
                    } else if (this.type === 'tm_fire') {
                        FireCheckout.prototype.save = this.amastyStripeOriginalSave;
                        checkout.save();
                    } else if (this.type === 'iwd_osc') {
                        OnePage.prototype.tryPlaceOrder = this.amastyStripeOriginalSave;
                        Singleton.get(OnePage).tryPlaceOrder();
                    } else if (this.type === 'iwd_osc_v2') {
                        this.amastyStripeOriginalSave();
                    }
                } catch (e) {
                    console.log(e.message);
                }
                break;
            case 'multishipping':
                var form = document.getElementById('multishipping-billing-form');

                if (form.length !== 0) {
                    form.submit();
                }
                break;
            case 'sales_order_create':
            case 'sales_order_edit':
                this.amastyStripeOriginalSave();
                break;
        }
    },

    /**
     * Assign stripe payment method and authorize
     */
    authorizeAction: function() {
        this.stripe.handleCardPayment(this.secret, this.card, this.paymentRequestData).then(function (result) {
            this.isLoading = false;
            if (result.error) {
                var errorElement = document.getElementById('stripe-card-errors');
                errorElement.textContent = result.error.message;
            } else {
                this.isAuthorized = true;
                this.completeAction();
            }
        }.bind(this));
    },

    saveFunction: function () {
        if (this.isLoading || (this.type === 'amasty_osc' && !amscheckoutForm.validator.validate())) {
            return;
        }
        if (this.getPaymentMethod() === this.moduleCode
            && !this.isAuthorized
            && this.paymentIntend
            && this.paymentIntend.status !== 'requires_capture'
        ) {
            this.isLoading = true;
            this.updateAmount().then(this.authorizeAction.bind(this));
        } else {
            this.completeAction();
        }
    }
});
