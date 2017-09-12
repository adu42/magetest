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

class EcommerceTeam_Ddc_Model_Sales_Order_Total_Invoice_Deliveryfee extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{

    /**
     * Collect invoice total
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @return Magentix_Fee_Model_Sales_Order_Total_Invoice_Fee
     */
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        
        $order = $invoice->getOrder();
        
        
        $feeAmountLeft = $order->getDeliveryFeeAmount() - $order->getDeliveryFeeAmountInvoiced();
        $baseFeeAmountLeft = $order->getBaseDeliveryFeeAmount() - $order->getBaseDeliveryFeeAmountInvoiced();
        
        if ($feeAmountLeft) {
            $invoice->setGrandTotal($invoice->getGrandTotal() + $feeAmountLeft);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseFeeAmountLeft);
        }
        $invoice->setDeliveryFeeLabel($order->getDeliveryFeeLabel());
        $invoice->setDeliveryFeeAmount($feeAmountLeft);
        $invoice->setBaseDeliveryFeeAmount($baseFeeAmountLeft);
        return $this;
    }
}
