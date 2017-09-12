<?php class Magebird_Popup_Model_Widget_Selectoptions
{
    public function toOptionArray()
    {
        $options[] = array('value' => 0, 'label' => Mage::helper('magebird_popup')->__('/'));
        $options[] = array('value' => 1, 'label' => Mage::helper('magebird_popup')->__('Text field'));
        $options[] = array('value' => 2, 'label' => Mage::helper('magebird_popup')->__('Required text field'));
        $options[] = array('value' => 3, 'label' => Mage::helper('magebird_popup')->__('Textarea'));
        $options[] = array('value' => 4, 'label' => Mage::helper('magebird_popup')->__('Required textarea'));
        $options[] = array('value' => 5, 'label' => Mage::helper('magebird_popup')->__('Select options'));
        $options[] = array('value' => 6, 'label' => Mage::helper('magebird_popup')->__('Required select options'));
        return $options;
    }
} ?>