<?php class Magebird_Popup_Block_Adminhtml_Renderer_Device extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        switch ($row->getData('device')) {
            case 1:
                return Mage::helper('magebird_popup')->__('Desktop');
                break;
            case 2:
                return Mage::helper('magebird_popup')->__('Mobile');
                break;
            default:
                return Mage::helper('magebird_popup')->__('Unknown');
        }
    }
} ?>