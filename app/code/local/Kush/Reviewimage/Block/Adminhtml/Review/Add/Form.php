<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/2
 * Time: 16:14
 */
class Kush_Reviewimage_Block_Adminhtml_Review_Add_Form extends Mage_Adminhtml_Block_Review_Add_Form
{
    protected function _prepareForm()
    {
        $helper =  Mage::helper("reviewimage");
        $form = new Varien_Data_Form(array(
            'id'    => 'edit_form',
            'action'=>  $this->getUrl('*/*/save',array('id' => $this->getRequest()->getParam('id'))),
            'method'=> 'post',
            'enctype'   => 'multipart/form-data'
        ));

        $fieldset = $form->addFieldset('add_review_form', array('legend' => Mage::helper('review')->__('Review Details'), 'class' => 'fieldset-wide'));

      //  $fieldset->addType('image', Mage::getConfig()->getBlockClassName('reviewimage/review_helper_image'));

        $fieldset->addField('product_name', 'note', array(
            'label'     => Mage::helper('review')->__('Product'),
            'text'      => 'product_name',
        ));

        $fieldset->addField('detailed_rating', 'note', array(
            'label'     => Mage::helper('review')->__('Product Rating'),
            'required'  => true,
            'text'      => '<div id="rating_detail">'
                . $this->getLayout()->createBlock('adminhtml/review_rating_detailed')->toHtml() . '</div>',
        ));

        $fieldset->addField('status_id', 'select', array(
            'label'     => Mage::helper('review')->__('Status'),
            'required'  => true,
            'name'      => 'status_id',
            'values'    => Mage::helper('reviewimage')->getReviewStatusesOptionArray(),
        ));

        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $field = $fieldset->addField('select_stores', 'multiselect', array(
                'label'     => Mage::helper('review')->__('Visible In'),
                'required'  => true,
                'name'      => 'select_stores[]',
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(),
            ));
            $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
            $field->setRenderer($renderer);
        }


        $fieldset->addField('title', 'text', array(
            'name'      => 'title',
            'title'     => Mage::helper('review')->__('Title'),
            'label'     => Mage::helper('review')->__('Title'),
            'maxlength' => '255',
            'required'  => false,
        ));

        $fieldset->addField('nickname', 'text', array(
            'name'      => 'nickname',
            'title'     => Mage::helper('review')->__('Nickname'),
            'label'     => Mage::helper('review')->__('Nickname'),
            'maxlength' => '50',
            'required'  => true,
            'style'     => 'width:100px !important',
        ));

        $fieldset->addField('detail', 'textarea', array(
            'name'      => 'detail',
            'title'     => Mage::helper('review')->__('Review'),
            'label'     => Mage::helper('review')->__('Review'),
            'style'     => 'height:8em;',
            'required'  => true,
        ));


        $outputFormat = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
        $fieldset->addField('created_at', 'date', array(
            'label'     => Mage::helper('review')->__('Created At'),
            'required'  => false,
            'name'      => 'created_at',
            'image'  => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).'/adminhtml/default/default/images/grid-cal.gif',
            'format' => $outputFormat,
            'time' => true,
        ));

        if ($helper->getActive() == '1'):
            $nums = $helper->maxImages();
            $olddir =   $helper->getImagePath();
            foreach ($nums as $n) {
                $field = 'review_image_' . $n;
                $fieldset->addField($field, 'image', array(
                    'label' => Mage::helper('review')->__('Review Image'),
                    'name' => $field,
                    //  'default_value' => $imageNewName,
                ));
            }
        endif;

        $fieldset->addField('review_catalog', 'select', array(
            'name'      => 'review_catalog',
            'title'     => Mage::helper('review')->__('Catalog Id'),
            'label'     => Mage::helper('review')->__('Catalog Id'),
            // 'maxlength' => '10',
            'required'  => false,
            // 'style'     => 'width:100px !important',
            'values' => Mage::helper('reviewimage')->getReviewCatalogOptionArray(),
        ));

        $fieldset->addField('review_position', 'text', array(
            'name'      => 'review_position',
            'label'     => Mage::helper('review')->__('Position'),
            'style'     => 'width:100px !important',
        ));

        $fieldset->addField('review_home', 'select', array(
            'label'     => Mage::helper('review')->__('Show in Home'),
            'name'      => 'review_home',
            'values'    => array(
                array(
                    'value'     => 1,
                    'label'     => Mage::helper('core')->__('Yes'),
                ),
                array(
                    'value'     => 0,
                    'label'     => Mage::helper('core')->__('No'),
                ),
            ),
        ));

        $fieldset->addField('review_sidebar', 'select', array(
            'label'     => Mage::helper('review')->__('Show in Catalog'),
            'name'      => 'review_sidebar',
            'values'    => array(
                array(
                    'value'     => 1,
                    'label'     => Mage::helper('core')->__('Yes'),
                ),
                array(
                    'value'     => 0,
                    'label'     => Mage::helper('core')->__('No'),
                ),
            ),
        ));







        $fieldset->addField('product_id', 'hidden', array(
            'name'      => 'product_id',
        ));

        /*$gridFieldset = $form->addFieldset('add_review_grid', array('legend' => Mage::helper('review')->__('Please select a product')));
        $gridFieldset->addField('products_grid', 'note', array(
            'text' => $this->getLayout()->createBlock('adminhtml/review_product_grid')->toHtml(),
        ));*/

        $form->setMethod('post');
        $form->setUseContainer(true);
        $form->setId('edit_form');
        $form->setAction($this->getUrl('*/*/post'));
        $this->setForm($form);
    }
}