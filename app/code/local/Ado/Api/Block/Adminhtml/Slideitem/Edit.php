<?php

class Ado_Api_Block_Adminhtml_Slideitem_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'mapi';
        $this->_controller = 'adminhtml_slideitem';
        
        $this->_updateButton('save', 'label', Mage::helper('mapi')->__('Save Slide Item'));
        $this->_updateButton('delete', 'label', Mage::helper('mapi')->__('Delete Slide Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('adoslide_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'adoslide_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'adoslide_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('slideitem_data') && Mage::registry('slideitem_data')->getId() ) {
            return Mage::helper('mapi')->__("Edit Slide Item '%s'", $this->htmlEscape(Mage::registry('slideitem_data')->getTitle()));
        } else {
            return Mage::helper('mapi')->__('Add Slide Item');
        }
    }
}