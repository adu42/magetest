<?php class Magebird_Popup_MousetrackingController extends Mage_Core_Controller_Front_Action
{
    protected function _initAction()
    {
        $this->loadLayout();
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction();
        $block = $this->getLayout()->createBlock('magebird_popup/mousetracking')->setTemplate('magebird/popup/mousetracking.phtml');
        $this->getResponse()->setBody($block->toHtml());
    }
} ?>