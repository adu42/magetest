<?php class Magebird_Popup_Helper_Mousetracking extends Mage_Core_Helper_Abstract
{
    public function handleMousetracking()
    {
        $mousetracking = Mage::app()->getRequest()->getParam('mousetracking');
        $mousetracking = json_decode($mousetracking);
        $device = $mousetracking->isMobile ? 2 : 1;
        $_mousetracking = Mage::getModel('magebird_popup/mousetracking');
        $_mousetracking->setWindowWidth($mousetracking->width);
        $_mousetracking->setWindowHeight($mousetracking->height);
        $_mousetracking->setMousetracking($mousetracking->cursor);
        $_mousetracking->setDevice($device);
        $zendDate = Zend_Date::now();
        $zendDate->setLocale(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE))->setTimezone(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE));
        $date = date("Y-m-d H:i:s", $zendDate->get());
        $_mousetracking->setDateCreated($date);
        $_mousetracking->setUserIp($_SERVER['REMOTE_ADDR']);
        $_mousetracking->save();
        $mousetrackingId = $_mousetracking->getId();
        $this->deleteOldMousetracking();
        $mousetrackingPopups = Mage::app()->getRequest()->getParam('mousetrackingPopups');
        $mousetrackingPopups = json_decode($mousetrackingPopups);
        foreach ($mousetrackingPopups as $popupId => $mousetrackingPopup) {
            $hPZ0AZXYNUa = Mage::getModel('magebird_popup/mousetrackingpopup');
            $hPZ0AZXYNUa->setMousetrackingId($mousetrackingId);
            $hPZ0AZXYNUa->setPopupId($popupId);
            $hPZ0AZXYNUa->setPopupWidth($mousetrackingPopup->width);
            $hPZ0AZXYNUa->setPopupLeftPosition($mousetrackingPopup->left);
            $hPZ0AZXYNUa->setPopupTopPosition($mousetrackingPopup->top);
            $hPZ0AZXYNUa->setStartSeconds($mousetrackingPopup->startDelayMs);
            $hPZ0AZXYNUa->setTotalMs($mousetrackingPopup->totalMiliSeconds);
            $hPZ0AZXYNUa->setBehaviour($mousetrackingPopup->ca);
            $hPZ0AZXYNUa->save();
        }
    }

    public function delete($time)
    {
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $table = Mage::getSingleton("core/resource")->getTableName('magebird_mousetracking');
        $table_magebird_mousetracking_popup = Mage::getSingleton("core/resource")->getTableName('magebird_mousetracking_popup');
        $sql = "DELETE $table,$table_magebird_mousetracking_popup FROM $table
          INNER JOIN $table_magebird_mousetracking_popup ON $table.mousetracking_id=$table_magebird_mousetracking_popup.mousetracking_id
          WHERE date_created < '" . @date('Y-m-d H:i:s', $time) . "'";
        $connWrite->query($sql);
    }

    public function deleteOldMousetracking()
    {
        $delete_mousetracking = Mage::getStoreConfig('magebird_popup/statistics/delete_mousetracking');
        switch ($delete_mousetracking) {
            case 1:
                $this->delete(strtotime("-1 month"));
                break;
            case 2:
                $this->delete(strtotime("-6 month"));
                break;
            case 3:
                $this->delete(strtotime("-7 day"));
                break;
            case 4:
                break;
            default:
                $this->delete(strtotime("-6 month"));
        }
    }
} ?>