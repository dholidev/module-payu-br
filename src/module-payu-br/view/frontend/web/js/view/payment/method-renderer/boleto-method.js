define(["Dholi_Payment/js/cash"],function(a){return a.extend({defaults:{template:"Dholi_PayUBr/payment/boleto-form",code:"dholi_payments_payu_boleto"},initialize:function(){this._super()},getLogoUrl:function(){return window.checkoutConfig.payment.dholi_payments_payu.url.logo}})});