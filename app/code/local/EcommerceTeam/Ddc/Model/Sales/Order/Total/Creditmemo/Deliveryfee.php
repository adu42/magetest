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

class EcommerceTeam_Ddc_Model_Sales_Order_Total_Creditmemo_Deliveryfee extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{

    /**
     * Collect credit memo total
     *
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @return Magentix_Fee_Model_Sales_Order_Total_Creditmemo_Fee
     */
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();

        if($order->getDeliveryFeeAmountInvoiced() > 0) {

            $feeAmountLeft = $order->getDeliveryFeeAmountInvoiced() - $order->getDeliveryFeeAmountRefunded();
            $basefeeAmountLeft = $order->getBaseDeliveryFeeAmountInvoiced() - $order->getBaseDeliveryFeeAmountRefunded();

            if ($basefeeAmountLeft > 0) {
                $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $feeAmountLeft);
                $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $basefeeAmountLeft);
                $creditmemo->setDeliveryFeeLabel($order->setDeliveryFeeLabel());
                $creditmemo->setDeliveryFeeAmount($feeAmountLeft);
                $creditmemo->setBaseDeliveryFeeAmount($basefeeAmountLeft);
            }

        } else {

            $feeAmount = $order->getDeliveryFeeAmountInvoiced();
            $basefeeAmount = $order->getBaseDeliveryFeeAmountInvoiced();

            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $feeAmount);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $basefeeAmount);
            $creditmemo->setDeliveryFeeLabel($order->setDeliveryFeeLabel());
            $creditmemo->setDeliveryFeeAmount($feeAmount);
            $creditmemo->setBaseDeliveryFeeAmount($basefeeAmount);

        }

        return $this;
    }

}
