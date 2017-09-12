<?php

class EcommerceTeam_Ddc_Block_Sales_Order_Totals extends Mage_Sales_Block_Order_Totals
{
    
    /**
     * Initialize order totals array
     *
     * @return Mage_Sales_Block_Order_Totals
     */
    protected function _initTotals()
    {
        parent::_initTotals();
        $baseAmount = $this->getOrder()->getBaseDeliveryFeeAmount();
        $amount = $this->getOrder()->getDeliveryFeeAmount();
        $label = $this->getOrder()->getDeliveryFeeLabel();
        if ($amount!=0){
            $this->addTotalBefore(new Varien_Object(array(
                'code'      => 'deliveryfee',
                'value'     => $amount,
                'base_value'=> $baseAmount,
                'strong' => false,
                'label'     => $this->helper('ecommerceteam_ddc')->__($label),
            ), array('grand_total')));
        }
        
        return $this;
    }
 
}
?>