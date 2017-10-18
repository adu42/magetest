<?php

class Ado_Api_Block_Adminhtml_Banneritem_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('adobanneritem_form', array('legend'=>Mage::helper('mapi')->__('Banner Item information')));
     
	  $banners = array(''=>'-- Select Banner --');
	  $collection = Mage::getModel('mapi/banner')->getCollection();
	  foreach ($collection as $banner) {
		 $banners[$banner->getId()] = $banner->getTitle();
	  }

	  $fieldset->addField('contact_us', 'label', array(
	  		'name'      => 'contact_us',
	  		'disabled'  => true,
	  ));
	  
	  $fieldset->addField('banner_id', 'select', array(
          'label'     => Mage::helper('mapi')->__('Banner'),
          'name'      => 'banner_id',
          'required'  => true,
          'values'    => $banners,
      ));

      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('mapi')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));
      
	  $fieldset->addField('banner_order', 'text', array(
          'label'     => Mage::helper('mapi')->__('Banner Order'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'banner_order',
      ));
      
      $fieldset->addField('image', 'image', array(
          'label'     => Mage::helper('mapi')->__('Banner Image'),
          'required'  => false,
          'name'      => 'image',
	  ));

      $fieldset->addField('image_url', 'text', array(
          'label'     => Mage::helper('mapi')->__('Image Url'),
          'required'  => false,
          'name'      => 'image_url',
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
	  ));
		
      $fieldset->addField('link_url', 'text', array(
          'label'     => Mage::helper('mapi')->__('Link Url'),
          'required'  => false,
          'name'      => 'link_url',
          'after_element_html' => '<br>Mobile URL Ex: category:3 | product:405 | search:keyword | block:block_id',
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
     /* $outputFormat = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);	
      $fieldset->addField('item_active_from', 'date', array(
          'label'     => Mage::helper('mapi')->__('Active From'),
          'required'  => false,
          'name'      => 'item_active_from',
		'image'  => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).'/adminhtml/default/default/images/grid-cal.gif',
		'format' => $outputFormat,
		'time' => true, 
      ));
	 $fieldset->addField('item_active_to', 'date', array(
          'label'     => Mage::helper('mapi')->__('Active To'),
          'required'  => false,
          'name'      => 'item_active_to',
		'image'  => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).'/adminhtml/default/default/images/grid-cal.gif',
		'format' => $outputFormat,
		'time' => true, 
      )); */

      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('mapi')->__('Content'),
          'title'     => Mage::helper('mapi')->__('Content'),
          'style'     => 'width:600px; height:300px;',
          'wysiwyg'   => false,
          'required'  => false,
      ));
          
     
      if ( Mage::getSingleton('adminhtml/session')->getEasyBannerItemData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getEasyBannerItemData());
          Mage::getSingleton('adminhtml/session')->setEasyBannerItemData(null);
      } elseif ( Mage::registry('adobanneritem_data') ) {
          $form->setValues(Mage::registry('adobanneritem_data')->getData());
      }
      return parent::_prepareForm();
  }
}