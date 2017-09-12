<?php

/*
 * Magento EsayCheckout Extension
 *
 * @copyright:	EcommerceTeam (http://www.ecommerce-team.com)
 * @version:	1.2
 *
 */

	class EcommerceTeam_EasyCheckout_Block_Onepage_Abstract extends Mage_Checkout_Block_Onepage_Abstract{
		
		public $helper;
		
		public function __construct(){
			$this->helper = Mage::helper('ecommerceteam_echeckout');
		}
		
		public function getConfigData($node){
			return $this->helper->getConfigData($node);
		}
		
		public function getCountryHtmlSelect($type){
	        $countryId = $this->getAddress()->getCountryId();
	        if (is_null($countryId)) {
	            $countryId = Mage::getStoreConfig('general/country/default');
	        }
            $countryOptions=$this->getSortOptions($this->getCountryOptions(),$countryId);
	        $select = $this->getLayout()->createBlock('core/html_select')
	            ->setName($type.'[country_id]')
	            ->setId($type.':country_id')
	            ->setTitle($this->__('Country'))
	            ->setClass('validate-select')
	            ->setValue($countryId)
	            ->setOptions($countryOptions);

	        return $select->getHtml();
	    }
	    public function isThreeColsMode(){
	    	return Mage::helper('ecommerceteam_echeckout')->getConfigFlag('options/checkoutmode');
	    }
	    public function getAddressesHtmlSelect($type)
	    {
	        if ($this->isCustomerLoggedIn()) {
	            $options = array();
	            foreach ($this->getCustomer()->getAddresses() as $address) {
	                $options[] = array(
	                    'value'=>$address->getId(),
	                    'label'=>$address->format('oneline')
	                );
	            }

	            $addressId = $this->getAddress()->getCustomerAddressId();
	            
	            if (empty($addressId)) {
	                if ($type=='billing') {
	                    $address = $this->getCustomer()->getPrimaryBillingAddress();
	                } else {
	                    $address = $this->getCustomer()->getPrimaryShippingAddress();
	                }
	                if ($address) {
	                    $addressId = $address->getId();
	                }
	            }

	            $select = $this->getLayout()->createBlock('core/html_select')
	                ->setName($type.'_address_id')
	                ->setId($type.'-address-select')
	                ->setClass('address-select')
	                ->setExtraParams('onchange="'.$type.'.newAddress(!this.value)"')
	                ->setValue($addressId)
	                ->setOptions($options);

	            $select->addOption('', Mage::helper('checkout')->__('New Address'));

	            return $select->getHtml();
	        }
	        return '';
	    }
	    
	    public function customerHasAddresses(){
	    	
	    	if($this->helper->getConfigFlag('options/address_book_enabled')){
	    	
	    		return parent::customerHasAddresses();
	    	
	    	}
	    	
	    	return false;
	    	
	    }
        
     public function someAsBilling(){
    	return $this->helper->shippingSameAsBilling();
     }
     
     public function someAsShipping(){
    	return $this->helper->shippingSameAsBilling();
     }
     
     public function isShow(){
    	
        return !$this->getQuote()->isVirtual();
        
    }
    
    
    
    public function canShow(){
        if(!$this->getQuote()->isVirtual() && $this->helper->differentShippingEnabled()){
            if($this->helper->shippingFirstEnabled()){
              //  @file_put_contents(dirname(__FILE__).'/aa.txt',print_r($this->prefix,true)."\n",FILE_APPEND);
                if($this->prefix=='billing'){
            	   return false;
            	}else if($this->prefix=='shipping'){
            	   return true;
            	}
            }
        	return true;
        }
        return false;
    }
    
    
    
     /**
     * @ÅÅÐò
     */    
    public function getSortOptions($options,$countryId){
       // @file_put_contents(dirname(__FILE__).'/aa.txt',$countryId.print_r($options,true)."\n",FILE_APPEND);
          $countryDefault=array(
    'SE' => array(
        'SE',
        'AU',
        'CA',
        'FR',
        'DE',
        'GB',
        'US',
        ),
    'DA' => array(
        'DA',
        'AU',
        'CA',
        'FR',
        'DE',
        'GB',
        'US',
        ),
    'DE' => array(
		'DE',
        'AT',
		'CH',
        'CZ',
        'DK',
        'NL',
        'NO',
        'PL',
        'SI',
        'SE',
        ),
    'AT' => array(
        'AT',
        'CZ',
        'DK',
        'DE',
        'NL',
        'NO',
        'PL',
        'SI',
        'SE',
        'CH',
        ),
    'US' => array(
        'US',
        'AU',
        'BR',
        'CA',
        'FR',
        'DE',
        'IT',
        'ES',
        'CH',
        'GB',
        ),
    'AU' => array(
        'AU',
        'BR',
        'CA',
        'FR',
        'DE',
        'IT',
        'ES',
        'CH',
        'GB',
        'US',
        ),
    'UK' => array(
        'GB',
        'AU',
        'BR',
        'CA',
        'FR',
        'DE',
        'IT',
        'ES',
        'CH',
        'US',
        ),
    'CA' => array(
        'CA',
        'AU',
        'BR',
        'FR',
        'DE',
        'IT',
        'ES',
        'CH',
        'GB',
        'US',
        ),
    'ES' => array(
        'ES',
        'AR',
        'CL',
        'CO',
        'EC',
        'MX',
        'PE',
        'PT',
        'PR',
        'VE',
        ),
    'FI' => array(
        'FI',
        'AU',
        'CA',
        'FR',
        'DE',
        'GB',
        'US',
        ),
    'FR' => array(
        'FR',
        'BE',
        'CA',
        'NC',
        'GF',
        'PF',
        'CH',
        ),
    'IT' => array(
        'IT',
        'CH',
        ),
    'NL' => array(
        'NL',
        'AN',
        'AU',
        'CA',
        'FR',
        'DE',
        'GB',
        'US',
        ),
    'NO' => array(
        'NO',
        'AU',
        'CA',
        'FR',
        'DE',
        'GB',
        'US',
        ),
    'PT' => array(
        'PT',
        'BR',
        ),
    );
          
          $find=false;
          $_options=array();
          if(isset($countryDefault[$countryId])){
              $countryDefault=$countryDefault[$countryId];
          }else{
              $countryDefault=array();
          }
          if(!empty($countryDefault)){
              foreach($countryDefault as $k=>$val){
                 foreach($options as $key=>$option){
                    if($option['value']==$val){
                        $_options[$k]['value']=$val;
                        $_options[$k]['label']=$option['label'];
                        unset($options[$key]);
                        break;
                    }
                 }
              }
              
              if(!empty($_options)){
                  foreach($options as &$option){
                     if($option['value']==''){
                        $option['label']='------------';
                        break;
                     }
                  }
                  $options=  array_merge($_options,$options);
              }
          }
          return $options;
    }
		
	}