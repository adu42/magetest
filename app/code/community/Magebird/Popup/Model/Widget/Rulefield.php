<?php class Magebird_Popup_Model_Widget_Rulefield
{
    public function toOptionArray()
    {
        $options = array();
        if (version_compare(Mage::getVersion(), '1.7', '<')) {
            $options[] = array('value' => '', 'label' => Mage::helper('magebird_popup')->__("No matching rules found"));
            return $options;
        }
        $collection = Mage::getModel('salesrule/rule')->getCollection();
        $collection->addFieldToFilter('coupon_type', 2);
        $collection->addFieldToFilter('use_auto_generation', 1);
        foreach ($collection as $rule) {
            $options[] = array('value' => $rule['rule_id'], 'label' => $rule['name'] . " (id " . $rule['rule_id'] . ")");
        }
        if (count($options) == 0) {
            $options[] = array('value' => '', 'label' => Mage::helper('magebird_popup')->__("No matching rules found"));
        }
        return $options;
    }
} ?>