<?php
class Magebird_Popup_Model_Widget_Register_Rulefield
{
    public function toOptionArray()
    {
        $options = array();
        if (version_compare(Mage::getVersion(), "1.7", "<")) {
            $options[] = array("value" => "", "label" => Mage::helper("magebird_popup")->__("No matching rules found"));
            return $options;
        }
        $rules = Mage::getModel("salesrule/rule")->getCollection();
        $rules->addFieldToFilter("coupon_type", 2);
        $rules->addFieldToFilter("use_auto_generation", 1);
        foreach ($rules as $rule) {
            $options[] = array("value" => $rule["rule_id"], "label" => $rule["name"] . " (id " . $rule["rule_id"] . ")");
        }
        if (count($options) == 0) {
            $options[] = array("value" => "", "label" => Mage::helper("magebird_popup")->__("No matching rules found"));
        }
        return $options;
    }
}