<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Follow Up Email
 * @version   1.1.23
 * @build     800
 * @copyright Copyright (C) 2017 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_Email_Model_Rule_Condition_Order extends Mirasvit_Email_Model_Rule_Condition_Abstract
{
    public function loadAttributeOptions()
    {
        $attributes = array(
            'summary_qty' => Mage::helper('email')->__('Order: Total quantity of products'),
            'summary_count' => Mage::helper('email')->__('Order: Total count of products'),
            'grand_total' => Mage::helper('email')->__('Order: Grand Total'),
            'shipping_method' => Mage::helper('email')->__('Order: Shipping Method'),
            'updated_at' => Mage::helper('email')->__('Order: Updated At'),
            'payment_method' => Mage::helper('email')->__('Order: Payment Method'),
            'order_status' => Mage::helper('email')->__('Order: Status'),
            'is_order_shipped' => Mage::helper('email')->__('Order: Shipment created'),
            'tracking_code_exists' => Mage::helper('email')->__('Order: Shipment has tracking information'),
            'is_order_invoiced' => Mage::helper('email')->__('Order: Invoice created'),
            'is_admin_order' => Mage::helper('email')->__('Order: Is order placed from admin'),
        );

        asort($attributes);
        $this->setAttributeOption($attributes);

        return $this;
    }

    public function getValueSelectOptions()
    {
        $this->_prepareValueOptions();

        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'shipping_method':
                    $options = Mage::getModel('adminhtml/system_config_source_shipping_allmethods')
                        ->toOptionArray();
                    break;
                case 'payment_method':
                    $options = Mage::getModel('adminhtml/system_config_source_payment_allowedmethods')
                        ->toOptionArray();
                    break;
                case 'order_status':
                    $options = Mage::getResourceModel('sales/order_status_collection')->toOptionArray();
                    break;
                case 'is_order_shipped':
                case 'tracking_code_exists':
                case 'is_order_invoiced':
                case 'is_admin_order':
                    $options = Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray();
                    break;
                default:
                    $options = parent::getValueSelectOptions();
            }
            $this->setData('value_select_options', $options);
        }

        return $this->getData('value_select_options');
    }

    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'shipping_method':
            case 'payment_method':
            case 'order_status':
            case 'is_order_shipped':
            case 'tracking_code_exists':
            case 'is_order_invoiced':
            case 'is_admin_order':
                return 'select';
        }

        return parent::getInputType();
    }

    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'shipping_method':
            case 'payment_method':
            case 'order_status':
            case 'is_order_shipped':
            case 'tracking_code_exists':
            case 'is_order_invoiced':
            case 'is_admin_order':
                return 'select';
        }

        return parent::getValueElementType();
    }

    public function validate(Varien_Object $object)
    {
        return $this->validateAttribute($this->getObjectValue($object));
    }

    /**
     * Retrieve object's value depending on validated attribute.
     *
     * @param Varien_Object $object
     *
     * @return mixed $value - object value for validated attribute
     */
    private function getObjectValue($object)
    {
        $value = null;
        if ($object->getData('order_id')) {
            $attrCode = $this->getAttribute();
            $order = Mage::getModel('sales/order')->load($object->getData('order_id'));

            switch ($attrCode) {
                case 'summary_qty':
                    $value = 0;
                    foreach ($order->getAllItems() as $item) {
                        $value += $item->getQtyOrdered();
                    }
                    break;

                case 'summary_count':
                    $value = 0;
                    foreach ($order->getAllItems() as $item) {
                        $value += 1;
                    }
                    break;

                case 'payment_method':
                    $value = $order->getPayment()->getMethod();
                    break;

                case 'order_status':
                    $value = $order->getStatus();
                    break;

                case 'is_order_shipped':
                case 'tracking_code_exists':
                    $value = 0;
                    if (($shipments = $order->getShipmentsCollection())) {
                        if ($attrCode === 'is_order_shipped') {
                            $value = $shipments->getSize() ? 1 : 0;
                        } else {
                            foreach ($shipments as $shipment) {
                                if ($shipment->getAllTracks()) {
                                    $value = 1;
                                    break;
                                }
                            }
                        }
                    }
                    break;

                case 'is_order_invoiced':
                    $value = $order->getInvoiceCollection()->getSize() ? 1 : 0;
                    break;

                case 'is_admin_order':
                    $value = $order->getRemoteIp() ? 0 : 1;
                    break;

                default:
                    $value = $order->getData($attrCode);
            }
        }

        return $value;
    }
}
