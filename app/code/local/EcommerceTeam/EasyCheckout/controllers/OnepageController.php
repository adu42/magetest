<?php

/*
 * Magento EsayCheckout Extension
 *
 * @copyright:	EcommerceTeam (http://www.ecommerce-team.com)
 * @version:	1.2
 *
 */

class EcommerceTeam_EasyCheckout_OnepageController extends Mage_Checkout_Controller_Action{
	
	protected $_helper;
	protected $_session;
	protected $_checkout;
	
	public function dispatch($action){
		
		return parent::dispatch($action);
		
	}
	
	public function getHelper(){
		
		if (is_null($this->_helper)) {
            $this->_helper = Mage::helper('ecommerceteam_echeckout');
        }
		return $this->_helper;
		
	}
	
	public function getOnepage(){
		
		return $this->getHelper()->getOnepage();
		
	}
	public function getCustomerSession(){
		if (is_null($this->_session)) {
            $this->_session = Mage::getSingleton('customer/session');
        }
		return $this->_session;
	}
	public function getCheckout()
    {
        if (empty($this->_checkout)) {
            $this->_checkout = Mage::getSingleton('checkout/session');
        }
        return $this->_checkout;
    }
    
    public function indexAction(){
    	
    	//die();
    	
        $quote = $this->getOnepage()->getQuote();
        
        if (!$quote->hasItems() || $quote->getHasError()){
        	
            $this->_redirect('checkout/cart');
            
            return;
        }
        
        if (!$quote->validateMinimumAmount())
        {
            $error = Mage::getStoreConfig('sales/minimum_order/error_message');
            Mage::getSingleton('checkout/session')->addError($error);
            $this->_redirect('checkout/cart');
            return;
        }
        
        Mage::getSingleton('checkout/session')->setCartWasUpdated(false);
        Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_secure'=>true)));
        
        
        $guest = $this->getRequest()->getParam('guest');
        if($guest){
            Mage::getSingleton('customer/session')->setGuestCheckout(true);
        }
        $sessionGuest = Mage::getSingleton('customer/session')->getGuestCheckout();
        
        if(!$sessionGuest){
            if(!Mage::helper('customer')->isLoggedIn() && $this->getHelper()->mustLogin()){
               // Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::helper('core/url')->getCurrentUrl());
                $this->_redirect('customer/account/login');
                return;
            }
        }
        
        
        $this->getOnepage()->initCheckout();
        
        $this->loadLayout();
        
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('checkout/session');
        
        
        $this->getLayout()->getBlock('head')->setTitle($this->getHelper()->getConfigData('options/title'));
        
        $this->renderLayout();
    }
    
    public function successAction()
    {
        if (!$this->getOnepage()->getCheckout()->getLastSuccessQuoteId()) {
            $this->_redirect('*/cart');
            return;
        }

        $lastQuoteId = $this->getOnepage()->getCheckout()->getLastQuoteId();
        $lastOrderId = $this->getOnepage()->getCheckout()->getLastOrderId();

        if (!$lastQuoteId || !$lastOrderId) {
            $this->_redirect('*/cart');
            return;
        }
        Mage::getSingleton('checkout/session')->clear();
        $this->loadLayout();
        $this->_initLayoutMessages('checkout/session');
        Mage::dispatchEvent('checkout_onepage_controller_success_action', array('order_ids' => array($lastOrderId)));
        $this->renderLayout();
    }

    public function failureAction()
    {
        $lastQuoteId = $this->getOnepage()->getCheckout()->getLastQuoteId();
        $lastOrderId = $this->getOnepage()->getCheckout()->getLastOrderId();

        if (!$lastQuoteId || !$lastOrderId) {
            $this->_redirect('*/cart');
            return;
        }

        $this->loadLayout();
        $this->renderLayout();
    }

	/**
	 * 判断是否要保存地址信息
	 * import address data
	 * @param string $type
	 * @return boolean
	 */
	protected function addAddressData($type='shipping',$save=false){
		$result = array('error' => 0, 'message' => array(),'error_type'=>'');
		//获得post过来的地址数据
		$address_data = $this->getRequest()->getPost($type,false);
		$type_address_id=$type.'_address_id';
		$customerAddressId = $this->getRequest()->getPost($type_address_id, false);

		//第一次写入后，把这个标识设为1，不再判断是不是使用相同地址
		if($this->getHelper()->isMobile()){
		$sameAddress = $this->getCheckout()->getOnlySaveSelf();
		if($sameAddress){
			//if(isset($address_data['use_for_shipping']))$address_data['use_for_shipping']=0;
			if(isset($address_data['use_for_billing']))$address_data['use_for_billing']=0;
		}
		}
		/*
		if($sameAs){
			$address_data['use_for_billing'] = 1;
			//$address_data['use_for_shipping'] = 1;
		}
		*/
       // Mage::log('------1.1------',null,'address_log.txt');
      //  Mage::log($type.$save,null,'address_log.txt');
		//post过来有数据，在ajax的接收变更，所以不判断，先写数据，直到点击save按钮，才判断地址合法性
		$result1 = $this->importAddressData($address_data,$customerAddressId,$type,$save);
		$result = $this->processResult($result,$type,$result1);
		if ((isset($address_data['use_for_billing']) && $address_data['use_for_billing'])||(isset($address_data['use_for_shipping']) && $address_data['use_for_shipping'])){
			$type = ($type=='shipping')?'billing':'shipping';
			$result2 = $this->importAddressData($address_data,$customerAddressId,$type,$save);
         //   Mage::log('------1.2------',null,'address_log.txt');
        //    Mage::log($type.$save,null,'address_log.txt');
			$result = $this->processResult($result,$type,$result2);
		}
		//存储会话
		 $this->getOnepage()->getQuote()->save();

		return $result;

	}

	/**
	 * 存档模式会验证地址是否正确，当有错误的时候怎么定位前端错误，展示对应的表单？
	 * @param $address_data
	 * @param $customerAddressId
	 * @param string $type
	 * @param bool|false $save
	 * @return array|bool
	 */
	protected function importAddressData($address_data,$customerAddressId,$type='shipping',$save=false){
		$result = array('error' => 0, 'message' => array(),'error_type'=>0);
		//是否需要重新计算运费？ shipping地址存档的时候需要
		$calc=false;
		if ($address_data||$customerAddressId) {
			if($type=='shipping'){
				if($save){ //存档验证
					$result = $this->getOnepage()->saveShippingAddressNo($address_data,$customerAddressId);
				}else{
					$address = $this->getOnepage()->getQuote()->getShippingAddress();
					$calc = true;// $save; //要重新计算运费
				}
			}else{
				if($save){ //存档验证
					$result = $this->getOnepage()->saveBillingAddressNo($address_data,$customerAddressId);
				}else{
					$address = $this->getOnepage()->getQuote()->getBillingAddress();
				}
			}
			
			if(!$save){ // 非存档验证
				if($customerAddressId){
					$customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
					if ($customerAddress->getId()) {
						if ($customerAddress->getCustomerId() == $this->getOnepage()->getQuote()->getCustomerId()) {
							$address->importCustomerAddress($customerAddress);
						}
					}
				}else{
					$address->setCustomerAddressId(null);
					$address->setSaveInAddressBook(empty($address_data['save_in_address_book']) ? 0 : 1);
					$address->addData($address_data);
					// set email for newly created user
					if (!$address->getEmail() && $this->getOnepage()->getQuote()->getCustomerEmail()) {
						$address->setEmail($this->getOnepage()->getQuote()->getCustomerEmail());
					}
				}
				$address->implodeStreetAddress();


				if($calc){ //需要重新计算运费，设置快递方式
                 //   Mage::log($address->getCountryId(),null,'address_log.txt');
                 //   Mage::log($address->getAddressType(),null,'address_log.txt');
                 //   Mage::log('------1------',null,'address_log.txt');
                    $address->setCollectShippingRates(true);
                     $this->getOnepage()->getQuote()->setTotalsCollectedFlag(true)->collectTotals();
					 $address = $this->getHelper()->initSingleShippingMethod($address);
					 $this->getOnepage()->getQuote()->setShippingAddress($address);
					//$address->setCollectShippingRates(true);
					//$address->collectShippingRates();
					//
					//$address->setCollectShippingRates(true);
					//$this->getOnepage()->getQuote()->setShippingAddress($address);
				}
			}

			$this->addSessionValue($address_data,$type);
			return $result;
		}
		return false;
	}

	/**
	 * 保存地址信息后的结果处理
	 * @param $result
	 * @param $type
	 * @param array $result2
	 * @return mixed
	 */
	protected function processResult($result,$type,$result2){
		//先合并
		if(isset($result2['error']) && $result2['error'] != false){
			$result['error_type'] = $type;
			$messages = array();
			foreach((array) $result['message'] as $message){
				$messages[] = $this->__(ucfirst($type).' address error').': '.$message;
			}
			$result2['message']=$messages;
			if(isset($result['message']) && !empty($result['message'])){
				$result['message']=array_merge($result2['message'],$result['message']);
			}else{
				$result['message']=$result2['message'];
				$result['error']=$result2['error'];
			}
		}
		return $result;
	}


	/**
	 *  有些值要保存在会话中，刷新的时候展示出来
	 * @param $data
	 * @param string $type
	 */
	protected function addSessionValue($data,$type='shipping'){
		if($type=='shipping'){
			if(isset($data['telephone'])) $this->getCheckout()->setShippingTelephone($data['telephone']);
		}else{
			if(isset($data['telephone'])) $this->getCheckout()->setBillingTelephone($data['telephone']);
		}
	}


   
    
	public function ajaxAction(){
		$action = $this->getRequest()->getParam('action');
		
		$result = new stdClass();
		$result->error = false;
		
       // @file_put_contents(dirname(__FILE__).'/aa.txt',print_r($action,true)."\n",FILE_APPEND);
        
		switch($action):
			default:
              //  Mage::log('------0------',null,'address_log.txt');
               $rs =  $this->_billingOrShippingAddress();
               if($rs['shipping']){
              //     Mage::log('------10------',null,'address_log.txt');
                    $result->shipping_rates = $this->_getShippingMethodsHtml();
				    $result->totals = $this->_getTotalsHtml();

               }
               if($rs['billing']){
                    $result->payments = $this->_getPaymentMethodsHtml();
               }
               //$result->review = $this->_getReviewHtml();
               //$result->totals = $this->_getTotalsHtml();
			break;
			case('payment'):
				$address = $this->getOnepage()->getQuote()->getBillingAddress();
 				$billData = $this->getRequest()->getPost('billing');
				if($billData){
					$address->addData($billData);
					$address->implodeStreetAddress();
				}
				
				$this->getOnepage()->getQuote()->collectTotals();
				
				$result->payments	= $this->_getPaymentMethodsHtml();
				//$result->review 	= $this->_getReviewHtml();
				$result->totals = $this->_getTotalsHtml();
				$this->getOnepage()->getQuote()->save();
			break;
			case('shipping'):
				$haveValue  =	$this->addAddressData($action);
				if($haveValue){ //如果有值更新，就重新输出结果
					//$result->review = $this->_getReviewHtml();
					$result->shipping_rates = $this->_getShippingMethodsHtml();
					$this->getOnepage()->getQuote()->collectTotals();
					$result->totals = $this->_getTotalsHtml();
					$result->shippingAddressDetail = $this->_getAddressHtml($action);
				}
				break;
            case('billing'):
				$haveValue  =	$this->addAddressData($action);
				if($haveValue){ //如果有值更新，就重新输出结果
					//$result->review = $this->_getReviewHtml();
					$result->shipping_rates = $this->_getShippingMethodsHtml();
					$this->getOnepage()->getQuote()->collectTotals();
					$result->totals = $this->_getTotalsHtml();
					$result->billingAddressDetail = $this->_getAddressHtml($action);
				}
			break;
			case('review'):
				if($shippingMethod = $this->getRequest()->getPost('shipping_method', false)){
        			$this->getOnepage()->getQuote()->getShippingAddress()->setShippingMethod($shippingMethod);
        		}
                if($shippingAddition = $this->getRequest()->getPost('deliveryfee', false)){
        			$this->getOnepage()->getQuote()->setDeliveryFeeId($shippingAddition);
                    $this->getCheckout()->setDeliveryFeeId($shippingAddition);
        		}
        		if (($payment = $this->getRequest()->getPost('payment', false)) && is_array($payment) && isset($payment['method']) && $payment['method']) {
        			try{
                      //  Mage::log('ajax-review:',null,'ado_test.txt');
                     //   Mage::log($payment,null,'ado_test.txt');
                		$this->getOnepage()->getQuote()->getPayment()->importData($payment);
                	}catch(Exception $e){
                		//continue
                	}
            	}
				$this->getOnepage()->getQuote()->setTotalsCollectedFlag(true)->collectTotals();
				//$result->review = $this->_getReviewHtml();
				$this->getOnepage()->getQuote()->save();
				$result->totals = $this->_getTotalsHtml();
			break;
			case('login'):
				$username = $this->getRequest()->getPost('username');
				$password = $this->getRequest()->getPost('password');
				if($username && $password){
					try{
		                Mage::getSingleton('customer/session')->login($username, $password);
		            }catch(Mage_Core_Exception $e) {
		                switch ($e->getCode()) {
		                    case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
		                        $message = $this->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', Mage::helper('customer')->getEmailConfirmationUrl($username));
		                    break;
		                    default:
		                        $message = $e->getMessage();
		                    break;
		                }
		                $result->error	= true;
		                $result->message	= $message;
		            }catch(Exception $e){
		                $result->error	= true;
		                $result->message	= $e->getMessage();
		            }
				}else{
		        	$result->error = true;
		            $result->message = $this->__('Login and password are required');
		        }
			break;
        case('save'):
			//save address
                $msg=array('message'=>array());
                $this->saveCheckoutMethod();
                
                if($this->getHelper()->isShippingFirst()){ //if shipping first save shipping address  == //
					$_result = $this->addAddressData('shipping',true);
                    if(isset($_result['error']) && $_result['error'] != false){
    					$result->error = true;
    					$msg['message'] = array_merge($msg['message'], $_result['message']);
    				}

					$_result = $this->addAddressData('billing',true);
					if(isset($_result['error']) && $_result['error']!=0){
						$result->error = true;
						$msg['message'] = array_merge($msg['message'], $_result['message']);
					}

                    //@file_put_contents(dirname(__FILE__).'/aa.txt',print_r($_result,true)."\n -4- \n",FILE_APPEND);
                   
                }else{   // not shipping first ============ //

					$_result = $this->addAddressData('billing',true);

					if(isset($_result['error']) && $_result['error'] != false){
						$result->error = true;
						$msg['message'] = array_merge($msg['message'], $_result['message']);
					}
                
				//save Shipping address and method
				if (!$this->getOnepage()->getQuote()->isVirtual()) {
						$_result = $this->addAddressData('shipping',true);
						if(isset($_result['error']) && $_result['error']!=0){
							$result->error = true;
							$msg['message'] = array_merge($msg['message'], $_result['message']);
						}
					//$this->getOnepage()->saveShippingMethod($this->getRequest()->getPost('shipping_method'));
				}
             } //if shipping first save shipping address end
				
			$this->saveSubscribe();
			$this->saveDeliveryfee();
                
            if($result->error){
                $result->message = implode("\n", $msg['message']);
			}
            $this->getOnepage()->getQuote()->save();
            $address =  $this->getOnepage()->getQuote()->getShippingAddress();
            if($address){
               // $collectors = $address->getTotalCollector()->getCollectors();
			   // $collectors['shipping']->collect($address);
                $address->setCollectShippingRates(true);
    			$address->collectShippingRates();
    			$this->getHelper()->initSingleShippingMethod($address);
    			$result->shipping_rates = $this->_getShippingMethodsHtml();
    			$address->setCollectShippingRates(true);  
             }
            $this->getOnepage()->getQuote()->setTotalsCollectedFlag(false)->collectTotals();      
            
  	        $result->shipping_rates = $this->_getShippingMethodsHtml();
           // $result->payments = $this->_getPaymentMethodsHtml();
            //$result->review = $this->_getReviewHtml(); 

            $this->getOnepage()->getQuote()->save();
			$result->totals = $this->_getTotalsHtml();
			$result->shippingAddressDetail = $this->_getAddressHtml('shipping');
			$result->billingAddressDetail = $this->_getAddressHtml('billing');
			break;
		case('saveshippingbilling'):
			//save address
			$msg=array('message'=>array());
			$this->saveCheckoutMethod();

			$this->getCheckout()->setOnlySaveSelf(false);
			$_result = $this->addAddressData('shipping',true);
			if(isset($_result['error']) && $_result['error'] != false){
				$result->error = true;
				$msg['message'] = array_merge($msg['message'], $_result['message']);
			}
			$address_data = $this->getRequest()->getPost('shipping',false);

			if(!isset($address_data['use_for_billing']) || !$address_data['use_for_billing']){
				$this->getCheckout()->setOnlySaveSelf(true);
				$_result = $this->addAddressData('billing',true);
				if(isset($_result['error']) && $_result['error'] != false){
					$result->error = true;
					$msg['message'] = array_merge($msg['message'], $_result['message']);
				}
			}

			$this->saveSubscribe();
			$this->saveDeliveryfee();

			if($result->error){
				$result->message = implode("\n", $msg['message']);
				if(empty($result->message))$result->message=$this->__('Please check your fill in.');
			}
			if($this->getHelper()->isMobile()){
				$this->getCheckout()->setStepNotifyShippingMethod(1);
				$this->getCheckout()->setStepNotifyPaymentMethod(0);
			}
			$this->getOnepage()->getQuote()->save();
			$address =  $this->getOnepage()->getQuote()->getShippingAddress();
			if($address){
				//$collectors = $address->getTotalCollector()->getCollectors();
				//$collectors['shipping']->collect($address);
				$address->setCollectShippingRates(true);
				$address->collectShippingRates();
				$this->getHelper()->initSingleShippingMethod($address);
				$result->shipping_rates = $this->_getShippingMethodsHtml();
				$address->setCollectShippingRates(true);
			}
			$this->getOnepage()->getQuote()->setTotalsCollectedFlag(false)->collectTotals();

			$result->shipping_rates = $this->_getShippingMethodsHtml();
			// $result->payments = $this->_getPaymentMethodsHtml();
			//$result->review = $this->_getReviewHtml();
			$result->totals = $this->_getTotalsHtml();
			$this->getOnepage()->getQuote()->save();
			$result->shippingAddressDetail = $this->_getAddressHtml('shipping');
			$result->billingAddressDetail = $this->_getAddressHtml('billing');
			break;
		case('saveshipping'):
			//save address
			$msg=array('message'=>array());
			$this->saveCheckoutMethod();
			$onlySlef = $this->getCheckout()->getOnlySaveSelf();

				$_result = $this->addAddressData('shipping',true);
				if(isset($_result['error']) && $_result['error'] != false){
					$result->error = true;
					$msg['message'] = array_merge($msg['message'], $_result['message']);
				}else{
					$this->getCheckout()->setOnlySaveSelf(1);
				}
				if(!$onlySlef){
					$_result = $this->addAddressData('billing',true);
				}
			//	@file_put_contents(dirname(__FILE__).'/aa.txt',print_r($_result,true)."\n -4- \n",FILE_APPEND);

			$this->saveSubscribe();
			$this->saveDeliveryfee();

			if($result->error){
				$result->message = implode("\n", $msg['message']);
			}
			if($this->getHelper()->isMobile()){
				$this->getCheckout()->setStepNotifyShippingMethod(0);
				$this->getCheckout()->setStepNotifyPaymentMethod(0);
			}
			$address =  $this->getOnepage()->getQuote()->getShippingAddress();
			if($address){
				//$collectors = $address->getTotalCollector()->getCollectors();
				//$collectors['shipping']->collect($address);
				$address->setCollectShippingRates(true);
				$address->collectShippingRates();
				$this->getHelper()->initSingleShippingMethod($address);
				$result->shipping_rates = $this->_getShippingMethodsHtml();
				$address->setCollectShippingRates(true);
			}
			$this->getOnepage()->getQuote()->setTotalsCollectedFlag(false)->collectTotals();
			$result->shipping_rates = $this->_getShippingMethodsHtml();
			// $result->payments = $this->_getPaymentMethodsHtml();
			//$result->review = $this->_getReviewHtml();
			$result->totals = $this->_getTotalsHtml();
			$this->getOnepage()->getQuote()->save();
			$result->shippingAddressDetail = $this->_getAddressHtml('shipping');
			$result->billingAddressDetail = $this->_getAddressHtml('billing');
			break;
		case('savebilling'):
			$msg=array('message'=>array());
			$_result = $this->addAddressData('billing',true);
			if (isset($_result['error']) && $_result['error'] != 0) {
				$result->error = true;
				$msg['message'] = array_merge($msg['message'], $_result['message']);
			}
			if($result->error){
				$result->message = implode("\n", $msg['message']);
			}
			if($this->getHelper()->isMobile()){
				$this->getCheckout()->setStepNotifyShippingMethod(0);
				$this->getCheckout()->setStepNotifyPaymentMethod(1);
			}
			$result->shippingAddressDetail = $this->_getAddressHtml('shipping');
			$result->billingAddressDetail = $this->_getAddressHtml('billing');
			break;
		case('coupon'):
			if (!$this->getOnepage()->getQuote()->getItemsCount()) {
				return;
			}
			$couponCode = (string) $this->getRequest()->getParam('coupon_code');
			if ($this->getRequest()->getParam('remove') == 1) {
					$couponCode = '';
			}
			$oldCouponCode = $this->getOnepage()->getQuote()->getCouponCode();
			if (!strlen($couponCode) && !strlen($oldCouponCode)) {
				return;
			}   
			try {
	            $this->getOnepage()->getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')
	                ->collectTotals()
	                ->save();
                $this->getOnepage()->getQuote()->getShippingAddress()->setCollectShippingRates(true)->collectShippingRates();
				if ($couponCode) {
                if ($couponCode == $this->getOnepage()->getQuote()->getCouponCode()) {
					$result->message = $this->__('Coupon code "%s" was applied successfully.', Mage::helper('core')->htmlEscape($couponCode));
			    }
                else {
					$result->error	= true;
					$result->message = $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode));
			    }
            } else {
				$result->message = $this->__('Coupon code was canceled successfully.');
		    }
        }
        catch (Mage_Core_Exception $e) {
			$result->error	= true;
		    $result->message = $e->getMessage();
        }
        catch (Exception $e) {
			$result->error	= true;
		    $result->message = $e->getMessage();
         }

		$result->shipping_rates = $this->_getShippingMethodsHtml();
		//$result->review = $this->_getReviewHtml(); 
        $result->totals = $this->_getTotalsHtml();
		$result->coupon = $this->_getCouponHtml(); 
							
		break;
			
		endswitch;
		
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		
	}

	/**
	 * 保存地址的同时，也要保存其他会话记录，==== 开始 ====
	 */
	protected function saveCheckoutMethod(){
		if($this->getCustomerSession()->isLoggedIn()){
			$this->getOnepage()->saveCheckoutMethod(Mage_Sales_Model_Quote::CHECKOUT_METHOD_LOGIN_IN);
		}elseif($this->getRequest()->getParam('create_account') || $this->getOnepage()->getQuote()->hasVirtualItems() || (bool)$this->getHelper()->getConfigData('options/guest_checkout') == false){
			$this->getOnepage()->saveCheckoutMethod(Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER);
		}else{
			$this->getOnepage()->saveCheckoutMethod(Mage_Sales_Model_Quote::CHECKOUT_METHOD_GUEST);
		}
	}

	protected function saveSubscribe(){
		if($this->getRequest()->getParam('subscribe', false)){
			if($this->getCustomerSession()->isLoggedIn()){
				$email = $this->getCustomerSession()->getCustomer()->getEmail();
			}else{
				$email = $this->getOnepage()->getQuote()->getBillingAddress()->getEmail();
			}
			Mage::getModel('newsletter/subscriber')->subscribe($email);
		}
	}

	protected function saveDeliveryfee(){
		if($shippingAddition = $this->getRequest()->getPost('deliveryfee', false)){
			$this->getOnepage()->getQuote()->setDeliveryFeeId($shippingAddition);
			$this->getCheckout()->setDeliveryFeeId($shippingAddition);
		}
	}

	/**
	 * 保存地址的同时，也要保存其他会话记录，==== 结束 ====
	 */
	
	public function onepageAction(){
		$this->_redirect('checkout/onepage');
	}
	
	public function saveAction(){
		$this->getCheckout()->setStepNotifyShippingMethod(0);
        $this->getCheckout()->setStepNotifyPaymentMethod(0);
		if ($this->getRequest()->isPost()) {
			//@file_put_contents(dirname(__FILE__).'/aa.txt',print_r($this->getRequest()->getParams(),true)."\n -1- \n",FILE_APPEND);
			$result = array('error'=>0, 'message'=>array());
			
        	try {
        		if ($requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds()) {
	                $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
	                if ($diff = array_diff($requiredAgreements, $postedAgreements)) {
	                    $result['error'] = true;
	                    $result['message'][] = $this->__('Please agree to all Terms and Conditions before placing the order.');
	                }
	            }
        		
        		if($this->getCustomerSession()->isLoggedIn()){
        			
        			$this->getOnepage()->saveCheckoutMethod(Mage_Sales_Model_Quote::CHECKOUT_METHOD_LOGIN_IN);
        				
        		}elseif($this->getRequest()->getParam('create_account') || $this->getOnepage()->getQuote()->hasVirtualItems() || (bool)$this->getHelper()->getConfigData('options/guest_checkout') == false){
        			
        			$this->getOnepage()->saveCheckoutMethod(Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER);
        			
	            }else{
	            	
	            	$this->getOnepage()->saveCheckoutMethod(Mage_Sales_Model_Quote::CHECKOUT_METHOD_GUEST);
	            	
	            }
                
                if($this->getHelper()->isShippingFirst()){ //if shipping first save shipping address  == //
					$_result = $this->addAddressData('shipping',true);
                    if(isset($_result['error']) && $_result['error'] != false){
    					$result['error'] = true;
    					$result['message'] = array_merge($result['message'], $_result['message']);
						if($this->getHelper()->isMobile()){
							$this->getCheckout()->setStepNotifyShippingMethod(1);
						}
    				}
					$address_data = $this->getRequest()->getPost('shipping',false);
				 if(!isset($address_data['use_for_billing']) || $address_data['use_for_billing']!=1){ 
					$_result = $this->addAddressData('billing',true);
					if(isset($_result['error']) && $_result['error'] != false){
						$result['error'] = true;
						$result['message'] = array_merge($result['message'], $_result['message']);
					}
				 }
                    //@file_put_contents(dirname(__FILE__).'/aa.txt',print_r($_result,true)."\n -4- \n",FILE_APPEND);
                    if (!$this->getOnepage()->getQuote()->isVirtual()) {  //need Shipping method
					   $this->getOnepage()->saveShippingMethod($this->getRequest()->getPost('shipping_method'));
                    }
                }else{   // not shipping first ============ //

				$_result = $this->addAddressData('billing',true);
				if(isset($_result['error']) && $_result['error'] != false){
					$result['error'] = true;
					$result['message'] = array_merge($result['message'], $_result['message']);
					if($this->getHelper()->isMobile()){
						$this->getCheckout()->setStepNotifyShippingMethod(0);
						$this->getCheckout()->setStepNotifyPaymentMethod(1);
					}
				}
                
				//save Shipping address and method
				if (!$this->getOnepage()->getQuote()->isVirtual()) {
					if(!$this->getOnepage()->getQuote()->getBillingAddress()->getUseForShipping()){
						$_result = $this->addAddressData('shipping',true);
						if(isset($_result['error']) && intval($_result['error'])){
							$result['error'] = true;
							$result['message'] = array_merge($result['message'], $_result['message']);
							if($this->getHelper()->isMobile()){
								$this->getCheckout()->setStepNotifyShippingMethod(1);
								$this->getCheckout()->setStepNotifyPaymentMethod(0);
							}
						}
					}
					$this->getOnepage()->saveShippingMethod($this->getRequest()->getPost('shipping_method'));
				}
             }
             
             
             //if shipping first save shipping address end
				if($this->getRequest()->getParam('subscribe', false)){
					if($this->getCustomerSession()->isLoggedIn()){
						$email = $this->getCustomerSession()->getCustomer()->getEmail();
					}else{
						$email = $this->getOnepage()->getQuote()->getBillingAddress()->getEmail();
					}
					Mage::getModel('newsletter/subscriber')->subscribe($email);
				}
                
                if($shippingAddition = $this->getRequest()->getPost('deliveryfee', false)){
        			$this->getOnepage()->getQuote()->setDeliveryFeeId($shippingAddition);
                    $this->getCheckout()->setDeliveryFeeId($shippingAddition);
        		}
                
                if($this->getHelper()->isMobile()){
                    $this->getOnepage()->getQuote()->setIsMobile(1);
                    if(!$this->getRequest()->getPost('shipping_method')){
                        $this->getCheckout()->setStepNotifyShippingMethod(1);
                    }
                }
				
				$this->getOnepage()->savePaymentMethod($this->getRequest()->getPost('payment'));
				$this->getOnepage()->getQuote()->collectTotals();
				$this->getOnepage()->getQuote()->save();
				$this->getOnepage()->savePaymentMethod($this->getRequest()->getPost('payment')); //fix for don delete original cc number after save quote




               if($redirectUrl = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl()){

					$this->getOnepage()->getQuote()->save();

					return $this->_redirectUrl($redirectUrl);
				}

                if($this->getHelper()->isMobile()){
                     $this->getCheckout()->setStepNotifyPaymentMethod(1);
                }
        		if(isset($result['error']) && (bool)$result['error'] === true){
					throw new Mage_Core_Exception(implode('<br/>', $result['message']));
				}
				Mage::dispatchEvent('easycheckout_controller_onepage_save_order', array('request'=>$this->getRequest(), 'quote'=>$this->getOnepage()->getQuote()));
				$this->getOnepage()->saveOrder();
				$this->getOnepage()->getQuote()->save();
				$this->getCheckout()->setCustomerAssignedQuote(false);
				$this->getCheckout()->setCustomerAdressLoaded(false);
				$redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();
				if($redirectUrl){
					$this->_redirectUrl($redirectUrl);
				}else{
				    if($this->getHelper()->isMobile()){
				        $this->getCheckout()->setStepNotifyShippingMethod(0);
				        $this->getCheckout()->setStepNotifyPaymentMethod(0);
				    }
					$this->_redirect('*/*/success');
				}
        	}catch(Mage_Core_Exception $e) {
        		Mage::logException($e);
            	$this->getOnepage()->getQuote()->save();
            	$this->getCustomerSession()->addError($e->getMessage());
				if($this->getHelper()->isMobile()){
					$this->getCheckout()->setStepNotifyShippingMethod(0);
					$this->getCheckout()->setStepNotifyPaymentMethod(1);
				}
            	$this->onepageAction();
        	}catch(Exception $e) {
        		Mage::logException($e);
        		$this->getOnepage()->getQuote()->save();
        		$this->getCustomerSession()->addError($this->__('There was an error processing your order. Please contact us or try again later.'));
				if($this->getHelper()->isMobile()){
					$this->getCheckout()->setStepNotifyShippingMethod(0);
					$this->getCheckout()->setStepNotifyPaymentMethod(1);
				}
        		$this->onepageAction();
        	}
		}
	}

	
	protected function _getShippingMethodsHtml()
    {
        $layout = Mage::getModel('core/layout');
        $layout->getUpdate()->load('checkout_onepage_shippingmethod');
        $layout->generateXml();
        $layout->generateBlocks();
        return $layout->getOutput();

        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('checkout_onepage_shippingmethod');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        return $output;
    }
    
    protected function _getPaymentMethodsHtml()
    {
        $layout = Mage::getModel('core/layout');
        $layout->getUpdate()->load('checkout_onepage_paymentmethod');
        $layout->generateXml();
        $layout->generateBlocks();
        return $layout->getOutput();
    }

    protected function _getAdditionalHtml()
    {
        $layout = Mage::getModel('core/layout');
        $layout->getUpdate()->load('checkout_onepage_additional');
        $layout->generateXml();
        $layout->generateBlocks();
        return $layout->getOutput();
    }
    
    protected function _getReviewHtml()
    {
    	
        $layout = Mage::getModel('core/layout');
        $layout->getUpdate()->load('ecommerceteam_echeckout_review');
        $layout->generateXml();
        $layout->generateBlocks();
        return $layout->getOutput();
    }
	
	protected function _getCouponHtml()
    {
    	
        $layout = Mage::getModel('core/layout');
        $layout->getUpdate()->load('ecommerceteam_echeckout_onepage_coupon');
        $layout->generateXml();
        $layout->generateBlocks();
        return $layout->getOutput();
    }
    
    protected function _getTotalsHtml()
    {
        $layout = Mage::getModel('core/layout');
        $layout->getUpdate()->load('ecommerceteam_echeckout_totals');
        $layout->generateXml();
        $layout->generateBlocks();
        return $layout->getOutput();
    }

	protected function _getAddressHtml($type = 'shipping'){
		if($type == 'shipping'){
			return $this->getOnepage()->getQuote()->getShippingAddress()->getFormated(true);
		}else{
			return $this->getOnepage()->getQuote()->getBillingAddress()->getFormated(true);
		}
	}
    
     /**
     * @=======@
     * @       @
     * @=======@
     */
    protected function _billingOrShippingAddress(){
        $result=array(
            'shippingFirst'=>0,
            'billing'=>0,
            'shipping'=>0,
            'billingSameShipping'=>0,
            'shippingSameBilling'=>0,
        );
            //shipping first
          if($this->getHelper()->isShippingFirst()){
            $result['shippingFirst']=1;
            if($shipping_address_data = $this->getRequest()->getPost('shipping')){
				$this->addAddressData('shipping');
				    $result['shipping']=1;
            }else if($billing_address_data = $this->getRequest()->getPost('billing')){
				$this->addAddressData('billing');
                $result['billing']=1;
			}
		  }
         return $result;
    }
    /**
     * @=========@
     * @=========@
     * @=========@
     */

	/**
	 * shippingAddress写入的时候，是否同时写入BillingAddress，不好判断，原因：
	 * 1、后面还可能会变更BillingAddress，展示BillingAddress，如果没有，需要展示shippingAddress，要写
	 * 2、如果返回来修改shippingAddress，BillingAddress已经存在，不能再修改，这个时候，不要写
	 *  写BillingAddress的时候一定不写shippingAddress
	 *  怎么验证地址？当地址错误的时候怎么验证地址或者提示地址有错？
	 *  怎么提示地址修改变更后的详细地址信息？
	 * 手机中：
	 * 一旦提交一次，改变会话中的 setShippingSameAsBilling(0) 做标识。其他时候各保存各自的。
	 *  pc：
	 *  不变规则
	 *
	 *
	 *
	 *
	 */
}
?>