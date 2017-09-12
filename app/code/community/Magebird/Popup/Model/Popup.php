<?php class Magebird_Popup_Model_Popup extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('magebird_popup/popup');
    }

    public function setPopupData($id, $field, $value)
    {
        $id = intval($id);
        $table = Mage::getSingleton("core/resource")->getTableName('magebird_popup');
        $core_write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = "UPDATE `{$table}` SET `$field`=:value WHERE popup_id=$id";
        $bind = array('value' => $value);
        $core_write->query($sql, $bind);
    }

    public function getPictureResize($width, $heigth = null, $image_file = null)
    {
        if ($image_file) {
            $image_file = str_replace("popup/", "", $image_file);
        } else {
            $image_file = str_replace("popup/", "", $this->getData("image"));
        }
        $original_file = Mage::getBaseDir('media') . DS . 'popup' . DS . $image_file;
        $resize_file = Mage::getBaseDir('media') . DS . 'popup' . DS . "resized_" . $width . $image_file;
        if (!file_exists($resize_file) && file_exists($original_file)) {
            $image = new Varien_Image($original_file);
            $image->constrainOnly(TRUE);
            $image->keepAspectRatio(TRUE);
            $image->keepFrame(FALSE);
            $image->resize($width, $heigth);
            $image->save($resize_file);
        }
        return (Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . "media/popup/" . "resized_" . $width . $image_file);
    }

    public function checkIfPageRefreshed($lastPageviewId)
    {
        $lastPageviewId = substr($lastPageviewId, 0, 10);
        $table = Mage::getSingleton("core/resource")->getTableName('magebird_popup');
        $core_write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = "UPDATE `{$table}` SET `page_reloaded`=`page_reloaded`+1,`window_closed`=`window_closed`-1 WHERE last_rand_id=:lastPageviewId";
        $bind = array('lastPageviewId' => $lastPageviewId);
        $core_write->query($sql, $bind);
    }

    public function parsePopupContent($popupId = null)
    {
        if (version_compare(Mage::getVersion(), '1.5', '<')) return;
        $core_write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $storeCollection = Mage::getModel('core/store')->getCollection();
        $popupCollection = Mage::getModel('magebird_popup/popup')->getCollection();
        if ($popupId) {
            $popupCollection->addFieldToFilter('popup_id', $popupId);
        }
        foreach ($storeCollection as $_store) {
            $store_id = $_store->getData('store_id');
            $emulation = Mage::getSingleton('core/app_emulation');
            $emulating = $emulation->startEnvironmentEmulation($store_id);
            foreach ($popupCollection as $_popup) {
                $popup_content = $_popup->getData('popup_content');
                $popup_content_value = Mage::helper('cms')->getBlockTemplateProcessor()->filter($popup_content);
                $sql = "INSERT INTO " . Mage::getSingleton('core/resource')->getTableName('magebird_popup_content') . " (popup_id,store_id,content,is_template)   VALUES (" . $_popup->getData('popup_id') . ",$store_id,:value,0) ON DUPLICATE KEY UPDATE content = VALUES(content)";
                $bind = array('value' => $popup_content_value);
                $core_write->query($sql, $bind);
            }
            $emulation->stopEnvironmentEmulation($emulating);
        }
    }

    public function parsePopupTemplateContent()
    {
        if (version_compare(Mage::getVersion(), '1.5', '<')) return;
        $core_write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $storeCollection = Mage::getModel('core/store')->getCollection();
        $templateCollection = Mage::getModel('magebird_popup/template')->getCollection();
        foreach ($storeCollection as $_store) {
            $store_id = $_store->getData('store_id');
            $emulation = Mage::getSingleton('core/app_emulation');
            $emulating = $emulation->startEnvironmentEmulation($store_id);
            foreach ($templateCollection as $_popup) {
                $popup_content = $_popup->getData('popup_content');
                $popup_content_value = Mage::helper('cms')->getBlockTemplateProcessor()->filter($popup_content);
                $sql = "INSERT INTO " . Mage::getSingleton('core/resource')->getTableName('magebird_popup_content') . " (popup_id,store_id,content,is_template)   VALUES (" . $_popup->getData('template_id') . ",$store_id,:value,1) ON DUPLICATE KEY UPDATE content = VALUES(content)";
                $bind = array('value' => $popup_content_value);
                $core_write->query($sql, $bind);
            }
            $emulation->stopEnvironmentEmulation($emulating);
        }
    }

    public function addNewView()
    {
        if (!Mage::helper('magebird_popup')->getPopupCookie('newVisit')) {
            Mage::helper('magebird_popup')->setPopupCookie('newVisit', 1, time() + (3600 * 48));
            $core_write = Mage::getSingleton('core/resource')->getConnection('core_write');
            $sql = "UPDATE " . Mage::getSingleton('core/resource')->getTableName('magebird_popup_stats') . " SET visitors=visitors+1";
            $core_write->query($sql);
        }
    }

    public function uniqueViewStats($popupId)
    {
        $popupId = intval($popupId);
        $lastPopups = Mage::helper('magebird_popup')->getPopupCookie('lastPopups');
        $show = false;
        $popups = explode(",", $lastPopups);
        foreach ($popups as $_popupId) {
            if ($_popupId == $popupId) $show = true;
        }
        if (!$show && Mage::helper('magebird_popup')->getPopupCookie('magentoSessionId')) {
            Mage::helper('magebird_popup')->setPopupCookie('lastPopups', $lastPopups . "," . $popupId, time() + (3600 * 48));
            $core_write = Mage::getSingleton('core/resource')->getConnection('core_write');
            if (Mage::helper('magebird_popup')->getPopupCookie('cartProductIds')) {
                $sql = "UPDATE " . Mage::getSingleton('core/resource')->getTableName('magebird_popup_stats') . " SET popup_visitors=popup_visitors+1,popup_carts=popup_carts+1 WHERE popup_id=" . $popupId;
            } else {
                $sql = "UPDATE " . Mage::getSingleton('core/resource')->getTableName('magebird_popup_stats') . " SET popup_visitors=popup_visitors+1 WHERE popup_id=" . $popupId;
            }
            $core_write->query($sql);
        }
    }
} ?>