<?php

/*
 * Magento EsayCheckout Extension
 *
 * @copyright:	EcommerceTeam (http://www.ecommerce-team.com)
 * @version:	1.2
 *
 */

class EcommerceTeam_EasyCheckout_Block_Onepage_Shipping extends EcommerceTeam_EasyCheckout_Block_Onepage_Abstract{
	
	protected $prefix = 'shipping';
	
    public function getAddress(){
    	
    	if($this->someAsBilling()){
			
			$session = Mage::getSingleton('customer/session');
			
			if($session->isLoggedIn()){
				if($address = $session->getCustomer()->getDefaultShippingAddress()){
					return $address;
				}

				if($address = $this->getQuote()->getShippingAddress()){
					if(!$address->getFirstname())$address->setFirstname($session->getCustomer()->getFirstname());
					if(!$address->getLastname())$address->setLastname($session->getCustomer()->getLastname());
					return $address;
				}
			}
		}
		return $this->getQuote()->getShippingAddress();
    }
    
    
    
    	
    
}
