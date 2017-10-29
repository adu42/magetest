<?php

class Ado_Api_Block_Adminhtml_Slide_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('adoslide_form', array('legend'=>Mage::helper('mapi')->__('Slide information')));
     
      $fieldset->addField('contact_us', 'label', array(
      		'name'      => 'contact_us',
      		'disabled'  => true,
      ));
      
      $fieldset->addField('identifier', 'text', array(
          'label'     => Mage::helper('mapi')->__('Identifier'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'identifier',
      ));

	  $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('mapi')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

	  $fieldset->addField('show_title', 'select', array(
          'label'     => Mage::helper('mapi')->__('Show Title'),
          'name'      => 'show_title',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('mapi')->__('Yes'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('mapi')->__('No'),
              ),
          ),
      ));
		
      $fieldset->addField('width', 'text', array(
          'label'     => Mage::helper('mapi')->__('Width (px)'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'width',
      ));

      $fieldset->addField('height', 'text', array(
          'label'     => Mage::helper('mapi')->__('Height (px)'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'height',
      ));
	
      $fieldset->addField('delay', 'text', array(
          'label'     => Mage::helper('mapi')->__('Delay (ms)'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'delay',
      ));

	$outputFormat = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);	
      $fieldset->addField('active_from', 'date', array(
          'label'     => Mage::helper('mapi')->__('Active From'),
          'required'  => false,
          'name'      => 'active_from',
		'image'  => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).'/adminhtml/default/default/images/grid-cal.gif',
		'format' => $outputFormat,
		'time' => true, 
      ));
	 $fieldset->addField('active_to', 'date', array(
          'label'     => Mage::helper('mapi')->__('Active To'),
          'required'  => false,
          'name'      => 'active_to',
		'image'  => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).'/adminhtml/default/default/images/grid-cal.gif',
		'format' => $outputFormat,
		'time' => true, 
      ));

	 $fieldset->addField('auto_play', 'select', array(
          'label'     => Mage::helper('mapi')->__('Auto Play(Only for picachoose temp)'),
          'name'      => 'auto_play',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('mapi')->__('Yes'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('mapi')->__('No'),
              ),
          ),
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
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('mapi')->__('Content'),
          'title'     => Mage::helper('mapi')->__('Content'),
          'style'     => 'width:600px; height:300px;',
          'wysiwyg'   => false,
          'required'  => false,
          'after_element_html' => '<br>if list about product,fill in "products:3,102,105,..."',
      ));
      
      if ( Mage::getSingleton('adminhtml/session')->getMapiData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getMapiData());
          Mage::getSingleton('adminhtml/session')->setMapiData(null);
      } elseif ( Mage::registry('mapi_data') ) {
          $form->setValues(Mage::registry('mapi_data')->getData());
      }
      return parent::_prepareForm();
  }
}