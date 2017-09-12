<?php class Magebird_Popup_Block_Adminhtml_Renderer_Sales extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
       // $oQ2OLcywzN5 = $row->getData($this->getColumn()->getIndex());
        $row = $row->getData();
        $tooltip = false;
        if ($row['show_when'] != "1" && ($row['background_color'] == "3" || $row['background_color'] == "4")) {
            $tooltip = "<span class='popupTooltip' title='" . Mage::helper('magebird_popup')->__('Not available for popups with Background Overlay set to None and Show when other than "After page loads".') . "'>?</span>";
        }
        if ($row['popupSalesCount'] > 0 && $row['views'] > 0) {
            $conversion = round($row['popupSalesCount'] / $row['popupVisitors'] * 100, 2) . "%";
        } else {
            $conversion = "/";
        }
        if ($row['popupSalesCount'] > 0 && $row['popupCarts'] > 0) {
            $abondedCart = round(($row['popupCarts'] - $row['popupSalesCount']) / $row['popupCarts'] * 100, 2) . "%";
        } else {
            $abondedCart = "/";
        }
        $cpnSales = $row['popupRevenue'] ? $row['currency'] . $row['popupRevenue'] : "/";
        $html = "<span style='font-size:11px;'>";
        $html .= "<span style='min-width:71px;display:inline-block;'>Cpn Sales:</span> " . $cpnSales . "<br>";
        $html .= "<span style='min-width:71px;display:inline-block;'>Cpn Orders:</span> " . $row['couponSalesCount'] . "<br>";
        if ($tooltip) {
            $html .= "<span style='min-width:71px;display:inline-block;'>Conversion:</span> $tooltip<br>";
            $html .= "<span style='min-width:71px;display:inline-block;'>Abonded cart:</span> $tooltip<br>";
        } else {
            $html .= "<span style='min-width:71px;display:inline-block;'>Conversion:</span> $conversion<br>";
            $html .= "<span style='min-width:71px;display:inline-block;'>Abonded cart:</span> $abondedCart<br>";
        }
        $html .= "</span>";
        return $html;
    }
} ?>