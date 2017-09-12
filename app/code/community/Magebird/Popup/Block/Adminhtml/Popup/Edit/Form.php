<?php class Magebird_Popup_Block_Adminhtml_Popup_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /*
    protected function _prepareLayout()
    {
        $config = Mage::getModel('core/config_data');
        $extension_key = $config->load('magebird_popup/general/extension_key', 'path')->getData('value');
        if (empty($extension_key)) {
            $config = Mage::getModel('core/config_data');
            $trial_start = $config->load('magebird_popup/general/trial_start', 'path')->getData('value');
            if ($trial_start > strtotime('-7 days')) {
                $days = ceil((($trial_start + 60 * 60 * 24 * 7) - time()) / 60 / 60 / 24);
                $this->getMessagesBlock()->addError(Mage::helper('magebird_popup')->__("You are currently using free trial mode which will expire in %s days. If you purcached the extension go to System->Configuration->MAGEBIRD EXTENSIONS->Popup to activate your licence (if you get 404 error, logout from admin and login again). After the trial period is over your popups won't be displayed any more until you submit your licence.", $days));
            } else {
                $this->getMessagesBlock()->addError(Mage::helper('magebird_popup')->__("You haven't submited your extension licence yet. Your popups won't be displayed anymore. Go to System->Configuration->MAGEBIRD EXTENSIONS->Popup to activate your licence."));
            }
        }
        return parent::_prepareLayout();
    }
    */

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))), 'method' => 'post', 'enctype' => 'multipart/form-data'));
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
} ?>