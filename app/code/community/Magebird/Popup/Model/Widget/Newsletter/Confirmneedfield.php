<?php class Magebird_Popup_Model_Widget_Newsletter_Confirmneedfield
{
    public function toOptionArray()
    {
        $options = array();
        $confirm = (Mage::getStoreConfig('newsletter/subscription/confirm') == 1) ? true : false;
        $options[] = array('value' => 0, 'label' => Mage::helper('magebird_popup')->__("No"));
        if ($confirm) {
            $options[] = array('value' => 1, 'label' => Mage::helper('magebird_popup')->__("Yes"));
        }
        return $options;
    }
} ?>