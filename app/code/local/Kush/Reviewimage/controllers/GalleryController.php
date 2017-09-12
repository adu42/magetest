<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/8
 * Time: 16:39
 */
require_once("Kush/Reviewimage/controllers/ProductController.php");

class Kush_Reviewimage_GalleryController extends Kush_Reviewimage_ProductController
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function loadAction()
    {
        $this->loadLayout();
        $this->renderLayout();
        /*
        $page =  (int) $this->_getRequest()->getParam('p',false);
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('review_gallery_load');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();

        $result['update_section'] = array(
            'page' => '3',
            'html' => $output
        );

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        */

    }


    public function initAction()
    {
        $model = Mage::getModel('catalog/resource_setup', 'catalog_resource');
        $model->startSetup();
        $attributeName = 'show_in_reviews';
        $attributeLabel = 'Show In Reviews';
        $getAttribute = $model->getAttribute(Mage_Catalog_Model_Category::ENTITY, $attributeName);
        if (!$getAttribute) {
            $model->addAttribute(Mage_Catalog_Model_Category::ENTITY, $attributeName, array(
                'group' => 'General Information',
                'input' => 'select',
                'type' => 'varchar',
                'source' => 'eav/entity_attribute_source_boolean',
                'label' => $attributeLabel,
                'backend' => '',
                'filterable' => false,
                'visible' => true,
                'required' => false,
                'visible_on_front' => true,
                'sort_order' => 19,
                'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
            ));
            echo $attributeLabel . ' ok<br>';
        } else {
            echo $attributeLabel . ' exsit<br>';
        }
        $model->endSetup();
    }

    public function testAction()
    {
        $categories = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('show_in_reviews')
            ->addAttributeToFilter('show_in_reviews', 1)
            ->setOrder('level')
            ->setOrder('position')
            ->setOrder('entity_id');

        foreach ($categories as $category) {
            print_r($category->getData());
            /*
                        $process = Mage::getModel('index/process')->load(5); $process->reindexAll();
                        $process = Mage::getModel('index/process')->load(6); $process->reindexAll();

                        $cat = Mage::getModel("catalog/category")->load($category->getId());
                        var_dump($cat->getData('mymodule_myattribute')); // Gives result
                        var_dump($cat->getMymoduleMyattribute()); // Gives result

                        var_dump($cat->getName()); // My Cool Category Name
            */

        }
    }


}