/*
 * Magento EsayCheckout Extension
 *
 * @copyright:	EcommerceTeam (http://www.ecommerce-team.com)
 * @version:	1.2
 *
 */
Event.observe(window, 'load',
	function(){
		Validation.defaultOptions.immediate = true; 
		console.log('window loaded');
      	if($('shipping-address-form')){ // if enabled diferent shipping address
			Event.observe($('shipping:country_id'), 'change', shippingAddressChanged);
			Event.observe($('shipping:city'), 'change', shippingAddressChanged);
			Event.observe($('shipping:region'), 'change', shippingAddressChanged);
			Event.observe($('shipping:region_id'), 'change', shippingAddressChanged);
			Event.observe($('shipping:postcode'), 'change', shippingAddressChanged);
			if(e = $('billing_use_for_shipping_yes')){
				Event.observe(e, 'click', changeShippingAddressMode);
			}
		}
		
		if($('easycheckout-addressbilling')){ // if enabled diferent shipping address
			Event.observe($('billing:country_id'), 'change', billingAddressChanged);
			Event.observe($('billing:city'), 'change', billingAddressChanged);
			Event.observe($('billing:region'), 'change', billingAddressChanged);
			Event.observe($('billing:region_id'), 'change', billingAddressChanged);
			Event.observe($('billing:postcode'), 'change', billingAddressChanged);
		}
		
		if(e =$('submit-save-address-btn')){ 
			Event.observe(e, 'click', saveAddress);
		}

		if(e =$('submit-save-shipping-address-btn')){
			Event.observe(e, 'click', saveShippingAddress);
		}

		if(e =$('submit-save-billing-address-btn')){
			Event.observe(e, 'click', saveBillingAddress);
		}

		if(e =$('submit-save-shipping-btn')){
			Event.observe(e, 'click', saveShippingMethod);
		}
		
		if(e =$('easycheckout-return-address')){
			Event.observe(e, 'click', showAddress);
		}

		if(e =$('easycheckout-return-shipping-address')){
			Event.observe(e, 'click', showShippingAddress);
		}

		if(e =$('easycheckout-return-billing-address')){
			Event.observe(e, 'click', showBillingAddressInPayment);
		}

		if(e =$('easycheckout-return-shipping')){
			Event.observe(e, 'click', hideAddress);
		}
		
		if(e =$('edit-coupon-field')){
			Event.observe(e, 'click', showCouponField);
		}

		if(e =$('shipping_use_for_billing_yes')){
			Event.observe(e, 'click', showHideBillingAddress);
		}



		if($('easycheckout-shippingmethod')){
			Event.observe($('easycheckout-shippingmethod'), 'click', function(e){
				if(e.target.nodeName == 'INPUT'){
					sendMethods('');
				}
			});
      	}
	}
);

function startLoadingData(only_review_block){
	
	if(only_review_block){
		checkoutoverlay.createOverlay('totals', $('easycheckout-totals'));
		//checkoutoverlay.createOverlay('review', $('easycheckout-review'));
		
	}else{
		
		//checkoutoverlay.createOverlay('review', $('easycheckout-review'));
		checkoutoverlay.createOverlay('totals', $('easycheckout-totals'));
		checkoutoverlay.createOverlay('methods', $('easycheckout-shipping-payment-step'));
	
	}
	
	
}

function stopLoadingData(){
	checkoutoverlay.hideOverlay();
}

function showHideBillingAddress(){
	if($('shipping_use_for_billing_yes').checked){
		hideBillingAddressInShipping();
	}else{
		showBillingAddress();
	}
}


function shippingAddressChanged(){
	if($('billing_use_for_shipping_yes')){
		if(!$('billing_use_for_shipping_yes').checked){
			sendShippingAddress();
		}
	}else{
		sendShippingAddress();
	}
}

function billingAddressChanged(){
	if($('shipping_use_for_billing_yes')){
		if(!$('shipping_use_for_billing_yes').checked){
			sendBillingAddress();
		}
	}else{
		sendBillingAddress();
	}
}

function changeShippingAddressMode(){
	
	$flag = this.checked;
		
	if($flag){
		$('shipping-address-form').style.display = 'none';
		sendBillingAddress();
	}else{
		$('shipping-address-form').style.display = 'block';
		sendShippingAddress();
	};
	
}
function changeBillingAddressMode(){
	$flag = this.checked;
	if($flag){
		$('easycheckout-addressbilling').style.display = 'none';
		sendShippingAddress(); 
	}else{
		$('easycheckout-addressbilling').style.display = 'block';
		sendBillingAddress();
	};
}

function cpAdddress(form,formParent){
	if(form=='shipping'){
		var to = 'billing';
	}else{
		var to = 'shipping';
	}
	var items = $$('#'+formParent+' input, #'+formParent+' select, #'+formParent+' textarea');
	items.each(function(item){
		var n = item.getAttribute('id');
		var v = item.getValue();
		if(n){
			var ele = $(n.replace(form,to));
			if(ele){
				ele.setValue(v);
			}
		}
	});
}

function saveAddress(){
	var result = false;
	if($('shipping_use_for_billing_yes') && $('shipping_use_for_billing_yes').checked){
		var valid = new Validation('shipping-address-form', {onSubmit:false});
		  result = valid.validate();
		cpAdddress('shipping','shipping-address-form');
		var q = buildQueryString($$('#shipping-address-form input, #shipping-address-form select, #shipping-address-form textarea,#shipping_use_for_billing_yes'));
	}else if($('billing_use_for_shipping_yes') && $('billing_use_for_shipping_yes').checked){
		var valid = new Validation('billing-address-form', {onSubmit:false});
		result = valid.validate();
		cpAdddress('billing','billing-address-form');
		var q = buildQueryString($$('#easycheckout-addressbilling input, #easycheckout-addressbilling select, #easycheckout-addressbilling textarea, #billing_use_for_shipping_yes'));
	}else{
		var valid1 = new Validation('billing-address-form', {onSubmit:false});
		var result1 = valid1.validate();
		var valid2 = new Validation('shipping-address-form', {onSubmit:false});
		var result2 = valid2.validate();
		result = (result1 && result2);
		var q = buildQueryString($$('#easycheckout-addressbilling input, #easycheckout-addressbilling select, #easycheckout-addressbilling textarea, #billing_use_for_shipping_yes,#shipping-address-form input, #shipping-address-form select, #shipping-address-form textarea,#shipping_use_for_billing_yes'));
	}
	if(result){
		startLoadingData();
		updateFormData(checkoutSaveAddressUrl, q,'hideAddress');
	}
}

function saveShippingAddress(){
	var valid = new Validation('shipping-address-form', {onSubmit:false});
	var result = valid.validate();
	if(result){
		startLoadingData();
		var q = buildQueryString($$('#shipping-address-form input, #shipping-address-form select, #shipping-address-form textarea,#shipping_use_for_billing_yes'));
		updateFormData(checkoutSaveShippingAddressUrl, q,'hideShippingAddress');
	}
}

function saveBillingAddress(){
	var valid = new Validation('billing-address-form', {onSubmit:false});
	var result = valid.validate();
	if(result) {
		startLoadingData();
		var q = buildQueryString($$('#easycheckout-addressbilling input, #easycheckout-addressbilling select, #easycheckout-addressbilling textarea, #billing_use_for_shipping_yes'));
		updateFormData(checkoutSaveBillingAddressUrl, q, 'hideBillingAddressInPayment');
	}
}

function saveShippingMethod(){
	var valid = new Validation('easycheckout-shippingmethod', {onSubmit:false});
	var result = valid.validate();
	if(result) {
		sendMethods('topay');
	}
}

function buildQueryString(elements){
	
	var q = '';
	
	for(var i = 0;i < elements.length;i++){
		if((elements[i].type == 'checkbox' || elements[i].type == 'radio') && !elements[i].checked){
			continue;
		}
		q += elements[i].name + '=' + encodeURIComponent(elements[i].value);
		
		if(i+1 < elements.length){
			q += '&';
		}
		
	}
	return q;
}

function update_coupon(remove){
	startLoadingData();
	if (remove){
		
		
        $('remove-coupone').value = "1";
		var q = buildQueryString($$('#coupon_code, #remove-coupone'));
	
		return updateFormData(checkoutCouponUrl, q,'');
	}
	else{
		
        $('remove-coupone').value = "0";
		var q = buildQueryString($$('#coupon_code, #remove-coupone'));
	
		return updateFormData(checkoutCouponUrl, q,'');
	}
}

function elogin(e, p, url){
	
	$('elogin-loading').style.display = 'block';
	$('elogin-buttons').style.display = 'none';
	
	var request = new Ajax.Request(url,
	{
	    method:'post',
	    parameters:'username='+e+'&password='+p,
	    onSuccess: function(transport){ var response = eval('('+(transport.responseText || false)+')');
	      if(response.error){
	      	  $('elogin-message').innerHTML = response.message;
	      	  $('elogin-loading').style.display = 'none';
			  $('elogin-buttons').style.display = 'block';
	      }else{
	      	  location.reload();
	      }
	    },
	    onFailure: function(){ alert('Something went wrong...');stopLoadingData(); }
	  });
}

function updateFormData(url, q,callback){
	var isScusess=false;
	var request = new Ajax.Request(url,
	  {
	    method:'post',
	    parameters:q,
	    onSuccess: function(transport){ var response = eval('('+(transport.responseText || false)+')');
	      
	      if(response.error){
			  if(response.review){
	      	  	$('easycheckout-review-info').update(response.review);
	      	  }
			  stopLoadingData();
			  alert(response.message);
	      	  //coming soon...
	      }else{
	      	  if(response.shipping_rates){
	      	  	$('easycheckout-shippingmethod-available').update(response.shipping_rates);
	      	  }
	      	  if(response.payments){
	      	  	$('easycheckout-paymentmethod-available').update(response.payments);
	      	  }
	      	  if(response.review){
	      	  	$('easycheckout-review-info').update(response.review);
	      	  }
			  if(response.coupon){
	      	  	$('easycheckout-coupon').update(response.coupon);
	      	  }
			  if(response.totals){
	      	  	$('easycheckout-totals').update(response.totals);
	      	  }
			  if(response.totals && $('easycheckout-totals-payment')){
				  $('easycheckout-totals-payment').update(response.totals);
			  }

			  if(response.shippingAddressDetail && $('shipping-address-detail-body')){
				  $('shipping-address-detail-body').update(response.shippingAddressDetail);
			  }

			  if(response.billingAddressDetail && $('billing-address-detail-body')){
				  $('billing-address-detail-body').update(response.billingAddressDetail);
			  }

			stopLoadingData();
			if(callback && callback.length>0){
				eval(callback+'()');
			}
	      }
	      
	    },
	    onFailure: function(){ alert('Something went wrong...');stopLoadingData(); }
	  });
	return isScusess;
}

function showAddress(){
	_setObjShowHide('easycheckout-address-step','show');
	_setObjShowHide('easycheckout-address-shipping-step','show');
	if($('shipping_use_for_billing_yes') && $('shipping_use_for_billing_yes').checked){
		_setObjShowHide('easycheckout-address-billing-step','hide');
	}else{
		_setObjShowHide('easycheckout-address-billing-step','show');
	}
	_setObjShowHide('easycheckout-address-submit-btn','show');
	_setObjShowHide('easycheckout-shipping-payment-step','hide');
	_setObjShowHide('easycheckout-payment-step','hide');
	_setClass('easycheckout-guide-1','active',true);
	_setClass('easycheckout-guide-2','active',false);
	//jQuery('html, body').animate({scrollTop:0}, 'fast');
}

function hideAddress(){
	 if(beforeToPay()){
	_setObjShowHide('easycheckout-address-step','hide');
	_setObjShowHide('easycheckout-address-shipping-step','hide');
	_setObjShowHide('easycheckout-address-submit-btn','hide');
	_setObjShowHide('submit-save-shipping-address-btn','hide');
	_setObjShowHide('easycheckout-address-billing-step','hide');
	_setObjShowHide('easycheckout-shipping-payment-step','show');
	_setObjShowHide('easycheckout-payment-step','hide');
	_setClass('easycheckout-guide-1','active',true);
	_setClass('easycheckout-guide-2','active',false);
	//jQuery('html, body').animate({scrollTop:0}, 'fast');
	 }
}

function showShippingAddress(){
	_setObjShowHide('easycheckout-address-shipping-step','show');
	_setObjShowHide('submit-save-shipping-address-btn','show');
	_setObjShowHide('easycheckout-address-billing-step','hide');
	_setObjShowHide('shipping_use_for_billing_yes_li','hide');
	/*
	if($('shipping_use_for_billing_yes') && $('shipping_use_for_billing_yes').checked){
		_setObjShowHide('easycheckout-address-billing-step','hide');
	}else{
		_setObjShowHide('easycheckout-address-billing-step','show');
	}
	*/
	_setObjShowHide('easycheckout-address-submit-btn','hide');
	_setObjShowHide('easycheckout-shipping-payment-step','hide');
	_setObjShowHide('easycheckout-payment-step','hide');
	_setClass('easycheckout-guide-1','active',true);
	_setClass('easycheckout-guide-2','active',false);
	//jQuery('html, body').animate({scrollTop:0}, 'fast');
}

function showBillingAddress(){
	_setObjShowHide('easycheckout-address-shipping-step','show');
	_setObjShowHide('easycheckout-address-billing-step','show');
//	_setObjShowHide('easycheckout-addressbilling','show');
	_setObjShowHide('submit-save-billing-address-btn','hide');
	if($('shipping_use_for_billing_yes') && $('shipping_use_for_billing_yes').checked){
		$('shipping_use_for_billing_yes').checked =  false;
	}
	_setObjShowHide('easycheckout-address-submit-btn','show');
	_setObjShowHide('easycheckout-shipping-payment-step','hide');
	_setObjShowHide('easycheckout-payment-step','hide');
	_setClass('easycheckout-guide-1','active',true);
	_setClass('easycheckout-guide-2','active',false);
	//jQuery('html, body').animate({scrollTop:0}, 'fast');
}

function showBillingAddressInPayment(){
	_setObjShowHide('easycheckout-address-shipping-step','hide');
	_setObjShowHide('easycheckout-address-billing-step','show');
//	_setObjShowHide('easycheckout-addressbilling','show');
	_setObjShowHide('submit-save-billing-address-btn','show');
	if($('shipping_use_for_billing_yes') && $('shipping_use_for_billing_yes').checked){
		$('shipping_use_for_billing_yes').checked =  false;
	}
	_setObjShowHide('easycheckout-address-submit-btn','hide');
	_setObjShowHide('easycheckout-shipping-payment-step','hide');
	_setObjShowHide('easycheckout-payment-step','hide');
	_setClass('easycheckout-guide-1','active',true);
	_setClass('easycheckout-guide-2','active',false);
	//jQuery('html, body').animate({scrollTop:0}, 'fast');
}

function hideShippingAddress(){
	_setObjShowHide('easycheckout-address-shipping-step','hide');
	_setObjShowHide('submit-save-shipping-address-btn','hide');
	_setObjShowHide('easycheckout-address-billing-step','hide');
	_setObjShowHide('easycheckout-address-submit-btn','hide');
	_setObjShowHide('easycheckout-shipping-payment-step','show');
	_setObjShowHide('easycheckout-payment-step','hide');
	_setClass('easycheckout-guide-1','active',true);
	_setClass('easycheckout-guide-2','active',false);
	//jQuery('html, body').animate({scrollTop:0}, 'fast');
}

function hideBillingAddressInShipping(){
	_setObjShowHide('easycheckout-address-shipping-step','show');
	_setObjShowHide('easycheckout-address-billing-step','hide');
	_setObjShowHide('easycheckout-address-submit-btn','show');
	_setObjShowHide('submit-save-billing-address-btn','hide');
	_setObjShowHide('easycheckout-shipping-payment-step','hide');
	_setObjShowHide('easycheckout-payment-step','hide');
	_setClass('easycheckout-guide-1','active',true);
	_setClass('easycheckout-guide-2','active',false);
	//jQuery('html, body').animate({scrollTop:0}, 'fast');
}

function hideBillingAddressInPayment(){
	_setObjShowHide('easycheckout-address-shipping-step','hide');
	_setObjShowHide('easycheckout-address-billing-step','hide');
	_setObjShowHide('submit-save-billing-address-btn','hide');
	_setObjShowHide('easycheckout-shipping-payment-step','hide');
	_setObjShowHide('easycheckout-payment-step','show');
	_setClass('easycheckout-guide-1','active',false);
	_setClass('easycheckout-guide-2','active',true);
	//jQuery('html, body').animate({scrollTop:0}, 'fast');
}

function beforeToPay(){
	var valid = new Validation('shipping-address-form', {onSubmit:false});
	var result = valid.validate();
	if(!result){
		showShippingAddress();
		return false;
	}
	return true;
}

function topay(){
	if(beforeToPay()){
	_setObjShowHide('easycheckout-address-step','hide');
	_setObjShowHide('easycheckout-address-shipping-step','hide');
	_setObjShowHide('submit-save-shipping-address-btn','hide');
	_setObjShowHide('easycheckout-address-submit-btn','hide');
	_setObjShowHide('easycheckout-address-billing-step','hide');
	_setObjShowHide('easycheckout-shipping-payment-step','hide');
	_setObjShowHide('easycheckout-payment-step','show');
	_setClass('easycheckout-guide-1','active',false);
	_setClass('easycheckout-guide-2','active',true);
	//jQuery('html, body').animate({scrollTop:0}, 'fast');
	}
}

function showShippingTip(){
	//var v= this.options[this.selectedIndex].value;
	var v= jQuery('#shipping_method').val();
    if(!v){ v='default'; }
	if(shippingTips[v] && $('shippingTips')){ $('shippingTips').update(shippingTips[v]);}
}

function showCouponField(){
	var id='discount-form';
	if(id){
		if($(id)){
			if($(id).style.display == 'block'){
				$(id).style.display = 'none';
			}else{
				$(id).style.display = 'block';
			}
		}
	}
}

function _setClass(id,className,isAdd){
	if(id){if($(id)){
		if(isAdd){
		$(id).addClassName(className);
		}else{
		$(id).removeClassName(className);
		}
		}}
}

function _setObjShowHide(id,act){
	if(id){
		if($(id)){
			if(act=='show'){
				$(id).style.display = 'block';
			}else{
				$(id).style.display = 'none';
			}
		}
	}
}


function sendBillingAddress(){
	startLoadingData();
	var q = buildQueryString($$('#easycheckout-addressbilling input, #easycheckout-addressbilling select, #easycheckout-addressbilling textarea, #billing_use_for_shipping_yes'));
	if($('billing_use_for_shipping_yes') && $('billing_use_for_shipping_yes').checked){
		return updateFormData(checkoutDefaultUrl, q,'');
	}
	return updateFormData(checkoutBillingUrl, q,'');
}

function sendShippingAddress(){
	startLoadingData();
	var q = buildQueryString($$('#shipping-address-form input, #shipping-address-form select, #shipping-address-form textarea'));
	if($('shipping_use_for_billing_yes') && $('shipping_use_for_billing_yes').checked){
		return updateFormData(checkoutDefaultUrl, q,'');
	}
	return updateFormData(checkoutShippingUrl, q,'');
}

function sendMethods(callback){
	
	startLoadingData(true);
	
	var q = '';
	
	q += buildQueryString($$('#easycheckout-shippingmethod input, #easycheckout-shippingmethod select, #easycheckout-shippingmethod textarea'));
	q += '&';
	q += buildQueryString($$('#easycheckout-paymentmethod input, #easycheckout-paymentmethod select, #easycheckout-paymentmethod textarea'));
	
	return updateFormData(checkoutTotalsUrl, q,callback);
	
}

var checkoutoverlay = {
	overlay:{},
	hideOverlay:function(){
		for(i in this.overlay){
			this.overlay[i].style.display = 'none';
		}
	},
	createOverlay:function(id, container){
		
		if(this.overlay['sln-overlay-'+id]){
		
			var overlay = this.overlay['sln-overlay-'+id];
		
		}else{
		
			var overlay = document.createElement('div');
			overlay.id = 'sln-overlay-'+id;
			
			document.body.appendChild(overlay);
			
			this.overlay['sln-overlay-'+id] = overlay;
		}
		
		if(typeof SLN_IS_IE == 'boolean'){
			container.style.position = 'relative';
		}else{
			SLN_IS_IE = false;
		}
		
		overlay.style.top			= container.offsetTop + 'px';
		overlay.style.left			= container.offsetLeft - (SLN_IS_IE ? 1 : 0) + 'px';
		overlay.style.width			= container.offsetWidth + (SLN_IS_IE ? 1 : 0) + 'px';	
		overlay.style.height		= container.offsetHeight + 'px';
		overlay.style.display 		= 'block';
		overlay.style.background	= '#ffffff';
		overlay.style.position		= 'absolute';
		overlay.style.opacity		= '0.7';
		overlay.style.filter		= 'alpha(opacity: 70)';
		
	}
}

var paymentForm = Class.create();
paymentForm.prototype = {
	beforeInitFunc:$H({}),
    afterInitFunc:$H({}),
    beforeValidateFunc:$H({}),
    afterValidateFunc:$H({}),
    initialize: function(formId){
        this.form = $(this.formId = formId);
    },
    init : function () {
        //var elements = Form.getElements(this.form);
        
        var elements = $$('#easycheckout-paymentmethod-available input, #easycheckout-paymentmethod-available select, #easycheckout-paymentmethod-available textarea');
        
        /*if ($(this.form)) {
            $(this.form).observe('submit', function(event){this.save();Event.stop(event);}.bind(this));
        }*/
        var method = null;
        for (var i=0; i<elements.length; i++) {
            if (elements[i].name=='payment[method]') {
                if (elements[i].checked) {
                    method = elements[i].value;
                }
            }
            elements[i].setAttribute('autocomplete','off');
        }
        if (method) this.switchMethod(method);
    },
    
    switchMethod: function(method){
        if (this.currentMethod && $('payment_form_'+this.currentMethod)) {
            var form = $('payment_form_'+this.currentMethod);
            form.style.display = 'none';
            var elements = form.getElementsByTagName('input');
            for (var i=0; i<elements.length; i++) elements[i].disabled = true;
            var elements = form.getElementsByTagName('select');
            for (var i=0; i<elements.length; i++) elements[i].disabled = true;
        }
        if ($('payment_form_'+method)){
            var form = $('payment_form_'+method);
            form.style.display = '';
            var elements = form.getElementsByTagName('input');
            for (var i=0; i<elements.length; i++) elements[i].disabled = false;
            var elements = form.getElementsByTagName('select');
            for (var i=0; i<elements.length; i++) elements[i].disabled = false;
            this.currentMethod = method;
        }
    }
}
var billing = Class.create();
billing = billing.prototype = {
	newAddress: function(isNew){
        if (isNew) {
            $('billing-new-address-form').select('input[type=text], select, textarea').each(function(e){if(!e.getAttribute('disabled') && !e.getAttribute('readonly')){e.value = ''};});
            
            Element.show('billing-new-address-form');
        } else {
            Element.hide('billing-new-address-form');
        }
        billingAddressChanged();
    }
}
var shipping = Class.create();
shipping = billing.prototype = {
	newAddress: function(isNew){
        if (isNew) {
            $('shipping-new-address-form').select('input[type=text], select, textarea').each(function(e){if(!e.getAttribute('disabled') && !e.getAttribute('readonly')){e.value = ''};});          
            Element.show('shipping-new-address-form');
        } else {
            Element.hide('shipping-new-address-form');
        }
        shippingAddressChanged();
        //shipping.setSameAsBilling(false);
    }
}

/*Prototype fix for IE9*/
if (Prototype.Browser.IE) {
  Object.extend(Selector.handlers, {
    // IE improperly serializes _countedByPrototype in (inner|outer)HTML.
    unmark: (function(){
      var PROPERTIES_ATTRIBUTES_MAP = (function(){
        var el = document.createElement('div'),
            isBuggy = false,
            propName = '_countedByPrototype',
            value = 'x'
        el[propName] = value;
        isBuggy = (el.getAttribute(propName) === value);
        el = null;
        return isBuggy;
      })();

      return PROPERTIES_ATTRIBUTES_MAP ?
        function(nodes) {
          for (var i = 0, node; node = nodes[i]; i++)
            node.removeAttribute('_countedByPrototype');
          return nodes;
        } :
        function(nodes) {
          for (var i = 0, node; node = nodes[i]; i++)
            node._countedByPrototype = void 0;
          return nodes;
        }
    })()
  });
}