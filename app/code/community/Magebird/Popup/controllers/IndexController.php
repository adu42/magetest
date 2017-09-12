<?php class Magebird_Popup_IndexController extends Mage_Core_Controller_Front_Action
{
    public function IndexAction()
    {
    }

    public function showAction()
    {
        header('Cache-Control: private, no-cache, no-store, max-age=0, must-revalidate, proxy-revalidate');
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        if ($this->getRequest()->getParam('switchRequestType') && Mage::getStoreConfig('magebird_popup/settings/requestswitched') != 1) {
            Mage::getModel('core/config')->saveConfig('magebird_popup/settings/requesttype', 3);
            Mage::getModel('core/config')->saveConfig('magebird_popup/settings/requestswitched', 1);
            Mage::app()->getCacheInstance()->cleanType('config');
        }
        Mage::getModel('magebird_popup/popup')->addNewView();
        $block = $this->getLayout()->createBlock('magebird_popup/popup')->setTemplate('magebird/popup/popup.phtml');
        $this->getResponse()->setBody($block->toHtml());
    }

    public function previewAction()
    {
        header('Cache-Control: private, no-cache, no-store, max-age=0, must-revalidate, proxy-revalidate');
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        $this->loadLayout();
        $this->getLayout()->getBlock("head")->setTitle($this->__("Popup Preview"));
        $this->renderLayout();
    }

    public function templateAction()
    {
        header('Cache-Control: private, no-cache, no-store, max-age=0, must-revalidate, proxy-revalidate');
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        $this->loadLayout();
        $this->getLayout()->getBlock("head")->setTitle($this->__("Template Preview"));
        $this->renderLayout();
    }

    public function statsAction()
    {
        if (Mage::helper('magebird_popup')->getIsCrawler()) return;
        if ($this->getRequest()->getParam('mousetracking')) {
            Mage::helper('magebird_popup/mousetracking')->handleMousetracking();
        }
        $popupIds = array();
        if ($popupId = $this->getRequest()->getParam('popupId')) {
            $popupIds[$popupId] = $this->getRequest()->getParam('time');
        }
        if ($popupIds = $this->getRequest()->getParam('popupIds')) {
            $popupIds = json_decode($popupIds);
            foreach ($popupIds as $popupId => $second) {
                $popupIds[$popupId] = $second;
            }
        }
        foreach ($popupIds as $popupId => $second) {
            $popup = Mage::getModel('magebird_popup/popup')->load($popupId);
            if ($popup->getData('popup_id')) {
                $views = $popup->getData('views');
                if (($popup->getData('background_color') != 3 && $popup->getData('background_color') != 4) || (($popup->getData('background_color') == 3 || $popup->getData('background_color') != 4) && $popup->getData('show_when') != 1)) {
                    $popup->setPopupData($popupId, 'views', $views + 1);
                    Mage::getModel('magebird_popup/popup')->uniqueViewStats($popup->getData('popup_id'));
                }
                $total_time = $popup->getData('total_time');
                $show_second = $second;
                if ($show_second > ($popup->getData('max_count_time') * 1000)) {
                    $show_second = $popup->getData('max_count_time') * 1000;
                }
                $popup->setPopupData($popupId, 'total_time', $total_time + $show_second);
                if ($this->getRequest()->getParam('closed') == 1) {
                    $popup->setPopupData($popupId, 'popup_closed', $popup->getData('popup_closed') + 1);
                } elseif ($this->getRequest()->getParam('windowClosed') == 1) {
                    if ($popup->getData('background_color') != 3 && $popup->getData('background_color') != 4) {
                        $popup->setPopupData($popupId, 'window_closed', $popup->getData('window_closed') + 1);
                        $popup->setPopupData($popupId, 'last_rand_id', $this->getRequest()->getParam('lastPageviewId'));
                    }
                } elseif ($this->getRequest()->getParam('clickInside') == 1) {
                    $popup->setPopupData($popupId, 'click_inside', $popup->getData('click_inside') + 1);
                }
            }
        }
    }

    public function popupCartsCountAction()
    {
        $popupId = $this->getRequest()->getParam('popupId');
        Mage::getModel('magebird_popup/popup')->uniqueViewStats($popupId);
    }
} ?>