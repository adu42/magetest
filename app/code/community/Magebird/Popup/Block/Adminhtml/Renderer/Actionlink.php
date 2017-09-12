<?php class Magebird_Popup_Block_Adminhtml_Renderer_Actionlink extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $websites = Mage::app()->getWebsites(false);
        $store = reset($websites)->getDefaultStore();
        if (!$store) {
            $store = next($websites)->getDefaultStore();
        }
        $storeId = $store->getId();
        $url = $this->getUrl('*/*/edit', array('id' => $row->getId(), 'storeId' => $row->getData('_first_store_id')));
        $storeId = (int)$this->getRequest()->getParam('store');
        if ($storeId == 0) {
            $stores = Mage::app()->getStores(false, true);
            $storeId = current($stores)->getId();
        }
        $storeCode = Mage::app()->getStore($storeId)->getCode();
        $coreUrl = Mage::getModel('core/url')->setStore($storeId);
        $previewUrl = $coreUrl->getUrl('magebird_popup/index/preview/previewId/' . $row->getId(), array('_current' => false, '_query' => '___store=' . $storeCode));
        $duplicateUrl = $this->getUrl('*/*/duplicate', array('copyid' => $row->getId(), 'storeId' => $row->getData('_first_store_id')));
        $mousetrackingUrl = $this->getUrl('*/*/mousetracking', array('id' => $row->getId(), 'storeId' => $row->getData('_first_store_id')));
        if ($row->getData('background_color') != 3) {
            if (Mage::getStoreConfig('magebird_popup/statistics/mousetracking')) {
                return sprintf("<style>.popupAction:hover{color:black;}</style><a class='popupAction' href='%s'>%s</a><br /><a class='popupAction' target='_blank' href='%s'>%s</a><br /><a class='popupAction' href='%s'>%s</a><br /><a class='popupAction' href='%s'>%s</a>", $url, Mage::helper('catalog')->__('Edit'), $previewUrl, Mage::helper('magebird_popup')->__('Preview'), $duplicateUrl, Mage::helper('magebird_popup')->__('Duplicate'), $mousetrackingUrl, Mage::helper('magebird_popup')->__('Mousetracking'));
            } else {
                return sprintf("<style>.popupAction:hover{color:black;}</style><a class='popupAction' href='%s'>%s</a><br /><a class='popupAction' target='_blank' href='%s'>%s</a><br /><a class='popupAction' href='%s'>%s</a>", $url, Mage::helper('catalog')->__('Edit'), $previewUrl, Mage::helper('magebird_popup')->__('Preview'), $duplicateUrl, Mage::helper('magebird_popup')->__('Duplicate'));
            }
        } else {
            if (Mage::getStoreConfig('magebird_popup/statistics/mousetracking')) {
                return sprintf("<style>.popupAction:hover{color:black;}</style><a class='popupAction' href='%s'>%s</a><br /><a class='popupAction' target='_blank' href='%s'>%s</a><br /><a class='popupAction' href='%s'>%s</a> " . "Mousetracking <span class='popupTooltip' title='" . Mage::helper('magebird_popup')->__("Mousetracking is not available to fixed positioned popups. You can change position in popup editor inside Appearance & css->Overlay Background settings.") . "'>(?)</span>", $url, Mage::helper('magebird_popup')->__('Edit'), $previewUrl, Mage::helper('magebird_popup')->__('Preview'), $duplicateUrl, Mage::helper('magebird_popup')->__('Duplicate'));
            } else {
                return sprintf("<style>.popupAction:hover{color:black;}</style><a class='popupAction' href='%s'>%s</a><br /><a class='popupAction' target='_blank' href='%s'>%s</a><br /><a class='popupAction' href='%s'>%s</a>", $url, Mage::helper('magebird_popup')->__('Edit'), $previewUrl, Mage::helper('magebird_popup')->__('Preview'), $duplicateUrl, Mage::helper('magebird_popup')->__('Duplicate'));
            }
        }
    }
} ?>