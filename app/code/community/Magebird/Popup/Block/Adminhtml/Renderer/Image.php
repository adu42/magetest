<?php class Magebird_Popup_Block_Adminhtml_Renderer_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        return "<img class='templatePreview' style='margin:10px' src='http://www.magebird.com/media/" . $row['preview_image'] . "' />";
    }
} ?>