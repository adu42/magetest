<?php
/**
 * @paypal支付的review页面整理
 */
class EcommerceTeam_EasyCheckout_Model_Paypal_Express_Checkout extends Mage_Paypal_Model_Express_Checkout{
    
    public function updateOrder($data)
    {
        /** @var $checkout Mage_Checkout_Model_Type_Onepage */
        $checkout = Mage::getModel('checkout/type_onepage');
       // @file_put_contents(dirname(__FILE__).'/aa.txt',"==11===\n",FILE_APPEND);
        $this->_quote->setTotalsCollectedFlag(true);
        $checkout->setQuote($this->_quote);
        if (isset($data['billing'])) {
            if (isset($data['customer-email'])) {
                $data['billing']['email'] = $data['customer-email'];
            }
            $checkout->saveBilling($data['billing'], 0);
        }
        if (!$this->_quote->getIsVirtual() && isset($data['shipping'])) {
            $checkout->saveShipping($data['shipping'], 0);
            if(isset($data['deliveryfee'])){ //add
                $this->_quote->setDeliveryFeeId($data['deliveryfee']);   
            }
        }
        try{
            if(Mage::helper('ecommerceteam_echeckout')){
                $this->_quote->setIsMobile(Mage::helper('ecommerceteam_echeckout')->isMobile());
            }  
        }catch(exception $e){
            
        }
        

        if (isset($data['shipping_method'])) {
            $this->updateShippingMethod($data['shipping_method']);
        }
        $this->_quote->setTotalsCollectedFlag(false);
        $this->_quote->collectTotals();
        $this->_quote->setDataChanges(true);
        $this->_quote->save();
    }
}
?>