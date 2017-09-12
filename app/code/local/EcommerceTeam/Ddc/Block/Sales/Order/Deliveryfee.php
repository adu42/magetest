<?php
/**
 * Created by Magentix
 * Based on Module from "Excellence Technologies" (excellencetechnologies.in)
 *
 * @category   Magentix
 * @package    Magentix_Fee
 * @author     Matthieu Vion (http://www.magentix.fr)
 * @license    This work is free software, you can redistribute it and/or modify it
 */

class EcommerceTeam_Ddc_Block_Sales_Order_Deliveryfee extends Mage_Core_Block_Template
{

    /**
     * Get order store object
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    /**
     * Get totals source object
     *
     * @return Mage_Sales_Model_Order
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * Initialize fee totals
     *
     * @return Magentix_Fee_Block_Sales_Order_Fee
     */
    public function initTotals()
    {
        if ((float) $this->getOrder()->getBaseDeliveryFeeAmount()) {
            $source = $this->getSource();
            $value  = $source->getDeliveryFeeAmount();
            $label = $source->getDeliveryFeeLabel();

            $this->getParentBlock()->addTotal(new Varien_Object(array(
                'code'   => 'deliveryfee',
                'strong' => false,
                'label'  => Mage::helper('ecommerceteam_ddc')->__($label),//'Delivery Fee'
                'value'  => $value
            )));
        }
        
        return $this;
    }
}