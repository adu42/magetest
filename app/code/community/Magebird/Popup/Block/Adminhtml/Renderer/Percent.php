<?php class Magebird_Popup_Block_Adminhtml_Renderer_Percent extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        //$ZUezj2iHkaq = $row->getData($this->getColumn()->getIndex());
        $row = $row->getData();
        $columnIndex = $this->getColumn()->getIndex();
        if (($columnIndex == "window_closed" || $columnIndex == "page_reloaded") && ($row['background_color'] == "3" || $row['background_color'] == "4")) {
            return "<span class='popupTooltip' title='" . Mage::helper('magebird_popup')->__('Not available for popups with Background Overlay set to None.') . "'>?</span>";
        } elseif (($columnIndex == "popup_closed" || $columnIndex == "click_inside") && ($row['background_color'] == "3" || $row['background_color'] == "4")) {
            return "<span style='min-width:20px; display:inline-block;'>" . $row[$this->getColumn()->getIndex()] . "</span>";
        }
        if ($row['views'] == 0) {
            $viewRate = '0 %';
        } else {
            $viewRate = round(($row[$this->getColumn()->getIndex()] / $row['views'] * 100), 1) . '%';
        }
        return "<span style='min-width:20px; display:inline-block;'>" . $row[$this->getColumn()->getIndex()] . "</span> (" . $viewRate . ")";
    }
} ?>