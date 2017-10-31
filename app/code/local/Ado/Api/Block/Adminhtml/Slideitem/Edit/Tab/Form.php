<?php

class Ado_Api_Block_Adminhtml_Slideitem_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('adoslideitem_form', array('legend'=>Mage::helper('mapi')->__('Slide Item information')));
     
	  $slides = array(''=>'-- Select Slide --');
	  $collection = Mage::getModel('mapi/slide')->getCollection();
	  foreach ($collection as $slide) {
		 $slides[$slide->getId()] = $slide->getTitle();
	  }
	  $fieldset->addField('contact_us', 'label', array(
	  		'name'      => 'contact_us',
	  		'disabled'  => true,
	  ));
	  
	  $fieldset->addField('slide_id', 'select', array(
          'label'     => Mage::helper('mapi')->__('Slide'),
          'name'      => 'slide_id',
          'required'  => true,
          'values'    => $slides,
      ));

      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('mapi')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));
      
	  $fieldset->addField('slide_order', 'text', array(
          'label'     => Mage::helper('mapi')->__('Slide Order'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'slide_order',
      ));
      
      $fieldset->addField('image', 'image', array(
          'label'     => Mage::helper('mapi')->__('Slide Image'),
          'required'  => false,
          'name'      => 'image',
	  ));

      $fieldset->addField('image_url', 'text', array(
          'label'     => Mage::helper('mapi')->__('Image Url'),
          'required'  => false,
          'name'      => 'image_url',
          'after_element_html' => '<br>Use Store URL Ex: category:3 | product:405 | url',
	  ));

      $fieldset->addField('thumb_image', 'image', array(
          'label'     => Mage::helper('mapi')->__('Thumnail Image'),
          'required'  => false,
          'name'      => 'thumb_image',
	  ));

      $fieldset->addField('thumb_image_url', 'text', array(
          'label'     => Mage::helper('mapi')->__('Thumnail Url'),
          'required'  => false,
          'name'      => 'thumb_image_url',
          'after_element_html' => '<br>Use Store URL Ex: category:3 | product:405 | url',
	  ));
		
      $fieldset->addField('link_url', 'text', array(
          'label'     => Mage::helper('mapi')->__('Link Url'),
          'required'  => false,
          'name'      => 'link_url',
          'after_element_html' => '<br>Use Product Image Ex: product:405',
	  ));
		
	  $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('mapi')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('mapi')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('mapi')->__('Disabled'),
              ),
          ),
      ));
     $outputFormat = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
      $fieldset->addField('item_active_from', 'date', array(
          'label'     => Mage::helper('mapi')->__('Active From'),
          'required'  => false,
          'name'      => 'item_active_from',
		'image'  => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).'/adminhtml/default/default/images/grid-cal.gif',
		'format' => $outputFormat,
		'time' => true,
          'value'=>date('Y-m-d H:i:s',strtotime('-1 day')),
      ));

	 $fieldset->addField('item_active_to', 'date', array(
          'label'     => Mage::helper('mapi')->__('Active To'),
          'required'  => false,
          'name'      => 'item_active_to',
		'image'  => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).'/adminhtml/default/default/images/grid-cal.gif',
		'format' => $outputFormat,
		'time' => true,
         'value'=>date('Y-m-d H:i:s',strtotime('+100 days')),
      ));

      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('mapi')->__('Content'),
          'title'     => Mage::helper('mapi')->__('Content'),
          'style'     => 'width:600px; height:300px;',
          'wysiwyg'   => false,
          'required'  => false,
      ));
          
     
      if ( Mage::getSingleton('adminhtml/session')->getFormData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getFormData());
          Mage::getSingleton('adminhtml/session')->setFormData(null);
      } elseif ( Mage::registry('slideitem_data') ) {
          $form->setValues(Mage::registry('slideitem_data')->getData());
      }
      return parent::_prepareForm();
  }
}