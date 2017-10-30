<?php

class Ado_Api_Block_Adminhtml_Slideitem_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('adoslideitem_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('mapi')->__('Slide Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('mapi')->__('Item Information'),
          'title'     => Mage::helper('mapi')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('mapi/adminhtml_slideitem_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}