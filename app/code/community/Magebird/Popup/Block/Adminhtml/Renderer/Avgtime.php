<?php class Magebird_Popup_Block_Adminhtml_Renderer_Avgtime extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
       // $columnData = $row->getData($this->getColumn()->getIndex());
        $row = $row->getData();
        if ($row['background_color'] == "3" || $row['background_color'] == "4") {
            return "<span class='popupTooltip' title='" . Mage::helper('magebird_popup')->__('Not available for popups with Background Overlay set to None.') . "'>?</span>";
        }
        if ($row['views'] == 0) {
            $avgtime = '0 s';
        } else {
            $avgtime = round(($row['total_time'] / 1000 / $row['views']), 1) . ' s';
        }
        return $avgtime;
    }
} ?>