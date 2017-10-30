<?php

class Ado_Api_Block_Adminhtml_Slide_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('mapi_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('mapi')->__('Slide Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('mapi')->__('Slide Information'),
          'title'     => Mage::helper('mapi')->__('Slide Information'),
          'content'   => $this->getLayout()->createBlock('mapi/adminhtml_slide_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}