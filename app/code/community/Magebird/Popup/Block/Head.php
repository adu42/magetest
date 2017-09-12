<?php class Magebird_Popup_Block_Head extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        setcookie('cookiesEnabled', '1', time() + 30);
    }

    public function getTargetPageId()
    {
        return Mage::helper('magebird_popup')->getTargetPageId();
    }

    public function getFilterId()
    {
        return Mage::helper('magebird_popup')->getFilterId();
    }

    public function isAjax()
    {
        return Mage::getStoreConfig('magebird_popup/settings/useajax');
    }

    public function requestType()
    {
        return Mage::getStoreConfig('magebird_popup/settings/requesttype') == 1 ? 'GET' : 'POST';
    }

    public function getPreviewId()
    {
        $request = $this->getRequest();
        $moduleName = $request->getModuleName();
        $actionName = $request->getActionName();
        if ($actionName != "preview" || $moduleName != "magebird_popup") return '';
        $previewId = $this->getRequest()->getParam('previewId');
        return $previewId;
    }

    /**
     * check extension_key && expire
     * @return string
     */
    public function getPage()
    {
       /* $extension_key = trim(Mage::getStoreConfig('magebird_popup/general/extension_key'));
        $config = Mage::getModel('core/config_data');
        $trial_start = $config->load('magebird_popup/general/trial_start', 'path')->getData('value');
        if ((empty($extension_key) || strlen($extension_key) != 10) && ($trial_start < strtotime('-7 days') || $trial_start > strtotime('+35 days'))) {
            return '0';
        }
       */
        return '1';
    }

    public function getTemplateId()
    {
        $request = $this->getRequest();
        $moduleName = $request->getModuleName();
        $actionName = $request->getActionName();
        if ($actionName != "template" || $moduleName != "magebird_popup") return '';
        $previewId = $this->getRequest()->getParam('templateId');
        return $previewId;
    }
} ?>