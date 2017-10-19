<?php

class Ado_Api_Block_Adminhtml_Banneritem_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'mapi';
        $this->_controller = 'adminhtml_banneritem';
        
        $this->_updateButton('save', 'label', Mage::helper('mapi')->__('Save Banner Item'));
        $this->_updateButton('delete', 'label', Mage::helper('mapi')->__('Delete Banner Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('adobanner_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'adobanner_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'adobanner_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('adobanneritem_data') && Mage::registry('adobanneritem_data')->getId() ) {
            return Mage::helper('mapi')->__("Edit Banner Item '%s'", $this->htmlEscape(Mage::registry('adobanneritem_data')->getTitle()));
        } else {
            return Mage::helper('mapi')->__('Add Banner Item');
        }
    }
}