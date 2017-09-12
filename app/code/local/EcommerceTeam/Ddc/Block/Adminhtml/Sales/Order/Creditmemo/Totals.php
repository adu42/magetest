<?php
class EcommerceTeam_Ddc_Block_Adminhtml_Sales_Order_Creditmemo_Totals extends Mage_Adminhtml_Block_Sales_Order_Creditmemo_Totals
{  
    /**  adminhtml_sales_order_create_totals_fee
     * Initialize order totals array
     *
     * @return Mage_Sales_Block_Order_Totals
     */
    protected function _initTotals()
    {
        
        parent::_initTotals();
        $amount = $this->getSource()->getDeliveryFeeAmount();
        $baseAmount = $this->getSource()->getBaseDeliveryFeeAmount();
        $label = $this->getSource()->getDeliveryFeeLabel();
      //  $amounta = $this->getSource()->getAddress()->getDeliveryFeeAmount();
        
        if ($amount) {
            $this->addTotal(new Varien_Object(array(
                'code'      => 'deliveryfee',
                'value'     => $amount,
                'base_value'=> $baseAmount,
                'label'     => $this->helper('ecommerceteam_ddc')->__($label),//'Delivery Fee'
            ), array('shipping')));
        }
        return $this;
    }
}
?>