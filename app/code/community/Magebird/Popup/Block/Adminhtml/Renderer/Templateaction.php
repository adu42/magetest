<?php class Magebird_Popup_Block_Adminhtml_Renderer_Templateaction extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $websites = Mage::app()->getWebsites(false);
        $storeId = 1;
        foreach ($websites as $website) {
            $store = $website->getDefaultStore();
            if (!$store) continue;
            $storeId = $store->getId();
            if (Mage::app()->getStore($storeId)->getData('is_active') != 0) break;
        }
        $templateUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK) . "magebird_popup/index/template/templateId/" . $row->getId();
        $templateCopyUrl = $this->getUrl('*/*/copy', array('copyid' => $row->getId(), 'storeId' => $row->getStoreId()));
        return sprintf("<a class='popupAction' target='_blank' href='%s'>%s</a> <a class='popupAction' href='%s'>%s</a>", $templateUrl, Mage::helper('magebird_popup')->__('Preview'), $templateCopyUrl, Mage::helper('magebird_popup')->__('Copy & Edit'));
    }
} ?>