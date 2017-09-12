<?php

/*
* Magento Delivery Date & Customer Comment Extension
*
* @copyright:	EcommerceTeam (http://www.ecommerce-team.com)
* @version:	2.0
*
*/

class EcommerceTeam_Ddc_Model_Config
{
    const CONFIG_BASE_PATH = 'checkout/deliveryfee/';
    
    protected $_deliveryFeeOptions=null;
    
    public  function isDeliveryFeeEnable(){
        return $this->_getConfig('enabled_deliveryfee');
    }
    
    public  function getDeliveryFeeOptions(){
        if($this->_deliveryFeeOptions===null){
            $_optionsStr = $this->_getConfig('deliveryfee_select');
            $_options=array();
            if(!empty($_optionsStr)){
                $_lines=explode("\n",$_optionsStr);
				$_defaultChecked=0;
                foreach($_lines as $_line){
                    $_line=trim($_line);
                    if(!empty($_line)){
                        $_r=explode('|',$_line);
                        if(count($_r)>=4){
                            $_options[$_r[0]]['id']=$_r[0];
                            list($_label,$_time)=$this->_getLabel($_r[1]);
                            $_options[$_r[0]]['label']= $_label;
                            $_options[$_r[0]]['time']= $_time;
                            if(!empty($_r[2]) && stripos($_r[2],'del')!==false){
                                $_r[2]=str_replace('del','',$_r[2]);
                                $_delPrice=(float)$_r[2];
                                $_r[2]=Mage::App()->getStore()->convertPrice($_delPrice,true);
                                $_r[2]='<del>'.trim($_r[2]).'</del>';
                            }
                            $_options[$_r[0]]['description']=$_r[2];
                            $_options[$_r[0]]['value']=$_r[3];
                            if($_r[3]!=0 && is_numeric($_r[3])){
                                $_options[$_r[0]]['price']='+'.Mage::App()->getStore()->convertPrice($_r[3],true);
                            }else{
                                $_options[$_r[0]]['price']=Mage::helper('ecommerceteam_ddc')->__($_r[3]);
                            }
							if($_defaultChecked==0)$_defaultChecked=$_r[0];
							if(isset($_r[4])&& $_r[4]=='checked'){
								$_options[$_r[0]]['checked']='checked';
								$_defaultChecked=-1;
							}
                        }
                    }
                }
				if($_defaultChecked>0)$_options[$_defaultChecked]['checked']='checked';
            }
           $this->_deliveryFeeOptions=$_options; 
        }
        return $this->_deliveryFeeOptions;
    }

	
	public function _getLabel($label){
        $label=trim($label);
		$_label = Mage::helper('ecommerceteam_ddc')->__($label);
		$regex='/\[(.*)\]/i';
		if(preg_match($regex, $_label, $matches)){
			$days = isset($matches[1])?intval($matches[1]):'20';
			if($days<=0)$days=20;
			$days='+'.$days;
			$_days = date('Y-m-d H:i:s',strtotime("$days days"));
			$days = $this->dateFormat($_days);
			$_label=str_replace($matches[0],$days,$_label);
		}
		return array($_label,$_days);
	}

	public function dateFormat($date)
    {
        return Mage::helper('core')->formatDate($date, Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM, false);
    }

    
    public  function getDeliveryFeeById($id){
        if(!empty($id)){
            $_options=$this->getDeliveryFeeOptions();
            if(isset($_options[$id]))return $_options[$id];
        }
        return false;
    }
    
    public function _getConfig($key, $method = null)
    {
       return Mage::getStoreConfig(self::CONFIG_BASE_PATH . $key);
    }

}
