<?php

/*
* Magento Delivery Date & Customer Comment Extension
*
* @copyright:	EcommerceTeam (http://www.ecommerce-team.com)
* @version:	2.0
*
*/

class EcommerceTeam_Ddc_Model_Sales_Quote_Address_Total_Deliveryfee extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    protected $_code = 'deliveryfee';

    public function collect(Mage_Sales_Model_Quote_Address $address)
    {

       // @file_put_contents(dirname(__FILE__).'/aa.txt',print_r('===',true),FILE_APPEND);
        parent::collect($address);

        $this->_setAmount(0);
        $this->_setBaseAmount(0);

        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this; //this makes only address type shipping to come through
        }


        $quote = $address->getQuote();
        $config = Mage::getModel('ecommerceteam_ddc/config');

        if($config->isDeliveryFeeEnable()){ //your business logic
           // $exist_amount = $quote->getDeliveryFeeAmount();
            $deliveryfeeid= $quote->getDeliveryFeeId();


            if(!$deliveryfeeid){
				$session = Mage::getSingleton('checkout/session');
                $deliveryfeeid = $session->getDeliveryFeeId();
            }
            $deliveryfee = $config->getDeliveryFeeById($deliveryfeeid);


            //$address->setDeliveryFeeAmount(0);
            //$address->setBaseDeliveryFeeAmount(0);
           // $quote->setDeliveryFeeAmount(0);
           // $quote->setBaseDeliveryFeeAmount(0);

            if($deliveryfee){
                $address->setDeliveryFeeId($deliveryfeeid); //fix calc get Id
                $quote->setDeliveryFeeId($deliveryfeeid);

                $fee =(float)$deliveryfee['value'];
                $label=$deliveryfee['label'];
                $amountPrice = $quote->getStore()->convertPrice($fee, false);
                $amountPrice = round($amountPrice,2); // fix price
                $fee = round($fee,2); // fix price
                $address->setDeliveryFeeLabel($label);
                $address->setDeliveryFeeAmount($amountPrice);
                $address->setBaseDeliveryFeeAmount($fee);

                $quote->setDeliveryFeeLabel($label);
                $quote->setDeliveryFeeAmount($amountPrice);
                $quote->setBaseDeliveryFeeAmount($fee);

                  //  $this->_setAmount($amountPrice);
                  //  $this->_setBaseAmount($fee);



            }
            $address->setGrandTotal($address->getGrandTotal() + $address->getDeliveryFeeAmount());
            $address->setBaseGrandTotal($address->getBaseGrandTotal() + $address->getBaseDeliveryFeeAmount());
        }else{
            $address->setDeliveryFeeLabel('');
            $address->setDeliveryFeeAmount(0);
            $address->setBaseDeliveryFeeAmount(0);

            $quote->setDeliveryFeeLabel('');
            $quote->setDeliveryFeeAmount(0);
            $quote->setBaseDeliveryFeeAmount(0);
        }

        return $this;
    }

	/*
    public function getLabel($label){
        $_label = trim($label); //Mage::helper('ecommerceteam_ddc')->__($label);
		$regex='/\[(.*)\]/i';
		if(preg_match($regex, $_label, $matches)){
			$days = $matches[1];
			if(stripos($days,'+')===false && (int)$days>0)$days='+'.$days;
			$days = date('Y-m-d',strtotime("$days days"));
			$_label=str_replace($matches[0],$days,$_label);
		}
		return $_label;
    }
	*/

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
	   if(Mage::getModel('ecommerceteam_ddc/config')->isDeliveryFeeEnable()){
        $amount = $address->getDeliveryFeeAmount();
        if($address->getAddressType() == 'shipping' && $amount!=0){
             $config = Mage::getModel('ecommerceteam_ddc/config');
             $title = $config->_getConfig('deliveryfee_title');
             $baseAmount = $address->getBaseDeliveryFeeAmount();
            // $label = $address->getDeliveryFeeLabel();
            // if($label)$title = $label;
                $address->addTotal(array(
                    'code'=>$this->getCode(),
                    'title'=>Mage::helper('ecommerceteam_ddc')->__($title),
                    'value'=> $amount,
                    'base_value'=>$baseAmount,
            ));
        }}
        return $this;
    }
}
?>
