<?php

/*
* Magento Delivery Date & Customer Comment Extension
*
* @copyright:	EcommerceTeam (http://www.ecommerce-team.com)
* @version:	2.0
*
*/

class EcommerceTeam_Ddc_Model_Observer
{

    /*
     public function coreBlockAbstractPrepareLayoutBefore($event)
           switch ($event->getBlock()->getType()):
               case ('adminhtml/sales_order_grid'):
                   if (Mage::getVersion() < '1.4.0') {
                       $event->getBlock()->addColumn('delivery_date', array(
                           'header' => Mage::helper('sales')->__('Delivery Date'),
                           'type' => 'date',
                           'index' => 'delivery_date',
                           'width' => '160px',
                           ));
                   } else {
                       $event->getBlock()->addColumnAfter('delivery_date', array(
                           'header' => Mage::helper('sales')->__('Delivery Date'),
                           'type' => 'date',
                           'index' => 'delivery_date',
                           'width' => '160px',
                           ), 'created_at');

                   }
                   break;
           endswitch;
       }
   */
    public function loadOrderData($event)
    {
        $order = $event->getOrder();
        $data = Mage::getModel('ecommerceteam_ddc/order')->load($order->getEntityId(),
            'order_id')->getData();
        if (isset($data['order_id'])) {
            unset($data['entity_id'], $data['order_id']);
            $order->addData($data);
        }

    }

    public function loadQuoteData($event)
    {
        $quote = $event->getQuote();
        $data = Mage::getModel('ecommerceteam_ddc/quote')->load($quote->getEntityId(),
            'quote_id')->getData();
        if (isset($data['quote_id'])) {
            unset($data['entity_id'], $data['quote_id']);
            $quote->addData($data);
        }

    }

    public function saveOrderData($event)
    {
        $order = $event->getOrder();
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        try {
            $model = Mage::getModel('ecommerceteam_ddc/order')->load($order->getEntityId(),'order_id');
            if (!$model->getEntityId()) {
                $model->setOrderId($order->getEntityId());
            }
            if($quote->getDeliveryFeeLabel())$model->setDeliveryFeeLabel($quote->getDeliveryFeeLabel());
            if($quote->getDeliveryFeeAmount())$model->setDeliveryFeeAmount($quote->getDeliveryFeeAmount());
            if($quote->getBaseDeliveryFeeAmount())$model->setBaseDeliveryFeeAmount($quote->getBaseDeliveryFeeAmount());
            if($quote->getDeliveryDate())$model->setDeliveryDate($quote->getDeliveryDate());
            if($quote->getCustomerComment())$model->setCustomerComment($quote->getCustomerComment());
            $model->save();
        }
        catch (exception $e) {
            //continue
        }
    }
    
    

    public function saveQuoteData($event)
    {
        $helper = Mage::helper('ecommerceteam_ddc');
        $quote = $event->getQuote();
        $request = Mage::app()->getRequest();
        $model = Mage::getModel('ecommerceteam_ddc/quote')->load($quote->getEntityId(),'quote_id');
        if (!$model->getEntityId()) {
            $model->setQuoteId($quote->getEntityId());
        }
        $data = array();

        if ($customer_comment = $request->getParam('customer_comment')) {
            $data['customer_comment'] = $customer_comment;
        }
        if ($deliveryfeeid = $request->getParam('deliveryfee')) {
            $deliveryfee = Mage::getModel('ecommerceteam_ddc/config')->getDeliveryFeeById($deliveryfeeid);
            if ($deliveryfee) {
                $fee=$deliveryfee['value'];
                $amountPrice = 0.00;
                if($fee!=0){
                     $amountPrice = $quote->getStore()->convertPrice($fee, false);
                }
                $data['delivery_fee_label'] = $deliveryfee['label'];
                $data['delivery_fee_amount'] = $amountPrice;
                $data['base_delivery_fee_amount'] = $fee;
                if(isset($deliveryfee['time']))
                $data['delivery_date'] = $deliveryfee['time'];
            }
        }
        if (!empty($data)) { //&& count($data)>2
            try {
                $quote->addData($data);
                $model->addData($data)->save();
            }
            catch (exception $e) {
                //continue
            }

        }

    }

    /**
     * Set fee amount invoiced to the order
     *
     * @param Varien_Event_Observer $observer
     * @return Magentix_Fee_Model_Observer
     */
    public function invoiceSaveAfter(Varien_Event_Observer $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();

        if ($invoice->getBaseDeliveryFeeAmount()) {
            $order = $invoice->getOrder();
            $order->setDeliveryFeeAmountInvoiced($order->getDeliveryFeeAmountInvoiced() + $invoice->getDeliveryFeeAmount());
            $order->setBaseDeliveryFeeAmountInvoiced($order->getBaseDeliveryFeeAmountInvoiced() + $invoice->getBaseDeliveryFeeAmount());
        }

        return $this;
    }

    /**
     * Set fee amount refunded to the order
     *
     * @param Varien_Event_Observer $observer
     * @return Magentix_Fee_Model_Observer
     */
    public function creditmemoSaveAfter(Varien_Event_Observer $observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();

        if ($creditmemo->getDeliveryFeeAmount()) {
            $order = $creditmemo->getOrder();
            $order->setDeliveryFeeAmountRefunded($order->getDeliveryFeeAmountRefunded() + $creditmemo->getDeliveryFeeAmount());
            $order->setBaseDeliveryFeeAmountRefunded($order->getBaseDeliveryFeeAmountRefunded() + $creditmemo->getBaseDeliveryFeeAmount());
        }

        return $this;
    }

    /**
     * Update PayPal Total
     *
     * @param Varien_Event_Observer $observer
     * @return Magentix_Fee_Model_Observer
     */
    public function updatePaypalTotal(Varien_Event_Observer $observer)
    {
		if(Mage::getModel('ecommerceteam_ddc/config')->isDeliveryFeeEnable()){
        $used =false;
        $cart = $observer->getEvent()->getPaypalCart();
        $label = $cart->getSalesEntity()->getDeliveryFeeLabel();
        $amount = $cart->getSalesEntity()->getDeliveryFeeAmount();
        $amountBase = $cart->getSalesEntity()->getBaseDeliveryFeeAmount();
        
        
        if(!$amount){
            $session = Mage::getSingleton('checkout/session');
            $deliveryfeeid = $session->getDeliveryFeeId();
            if($deliveryfeeid){
                $deliveryfee = Mage::getModel('ecommerceteam_ddc/config')->getDeliveryFeeById($deliveryfeeid);
                $fee=$deliveryfee['value'];
                $amountPrice=0;
		if($fee!=0){
                     $amountPrice = $cart->getSalesEntity()->getStore()->convertPrice($fee, false);
                }
                $label = $deliveryfee['label'];
                $amount = $amountPrice;
                $amountBase = $fee;
            }
        }
       
       if($amount!=0){
           if($cart->getSalesEntity() instanceof Mage_Sales_Model_Order){
                $currencyCode = $cart->getSalesEntity()->getOrderCurrency()->getCurrencyCode();
           }else{
                $currencyCode = $cart->getSalesEntity()->getQuoteCurrencyCode();
           }
           if($currencyCode){
                $used = Mage::getModel('paypal/config')->useStoreCurrency($currencyCode);
           }    
           if($used){
                $cart->addItem($label,1,$amount,'DELIVERYFEE');
           }else{
                $cart->addItem($label,1,$amountBase,'DELIVERYFEE');
           }
        }}
        return $this;
    }
}
