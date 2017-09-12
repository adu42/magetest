<?php class Magebird_Popup_Model_Widget_Coupontype
{
    public function toOptionArray()
    {
        $options[] = array('value' => 0, 'label' => Mage::helper('magebird_popup')->__('No coupon'));
        $options[] = array('value' => 1, 'label' => Mage::helper('magebird_popup')->__('Specific static coupon code'));
        if (version_compare(Mage::getVersion(), '1.7', '>=')) {
            $options[] = array('value' => 2, 'label' => Mage::helper('magebird_popup')->__('Auto generate dynamic coupon code for each customer'));
        }
        return $options;
    }
} ?>