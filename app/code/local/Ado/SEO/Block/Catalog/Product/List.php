<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright  Copyright (c) 2006-2017 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Product list
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Ado_SEO_Block_Catalog_Product_List extends Mage_Catalog_Block_Product_List
{

    /**
     * Retrieve loaded category collection
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected function _getProductCollection()
    {

        if (is_null($this->_productCollection)) {

            $layer = $this->getLayer();



            /* @var $layer Mage_Catalog_Model_Layer */
            if ($this->getShowRootCategory()) {
                $this->setCategoryId(Mage::app()->getStore()->getRootCategoryId());
            }

            if (Mage::registry('product')) {
                /** @var Mage_Catalog_Model_Resource_Category_Collection $categories */
                $categories = Mage::registry('product')->getCategoryCollection()
                    ->setPage(1, 1)
                    ->load();
                if ($categories->count()) {
                    $this->setCategoryId($categories->getFirstItem()->getId());
                }
            }

            $origCategory = null;
            if ($this->getCategoryId()) {
                $category = Mage::getModel('catalog/category')->load($this->getCategoryId());
                if ($category->getId()) {
                    $origCategory = $layer->getCurrentCategory();
                    $category->setColor($this->getRequest()->getParam(Ado_SEO_Block_Catalog_Product_List_Colors::COLOR_ATTRIBUTE_CODE));
                    $layer->setCurrentCategory($category);
                    $this->addModelTags($category);
                }
            }
            $layer->getCurrentCategory()->setColor($this->getRequest()->getParam(Ado_SEO_Block_Catalog_Product_List_Colors::COLOR_ATTRIBUTE_CODE));
            $layer->setColor($this->getRequest()->getParam(Ado_SEO_Block_Catalog_Product_List_Colors::COLOR_ATTRIBUTE_CODE));
            $this->_productCollection = $layer->getProductCollection();

            $this->prepareSortableFieldsByCategory($layer->getCurrentCategory());

            if ($origCategory) {
                $layer->setCurrentCategory($origCategory);
            }
        }

        return $this->_productCollection;
    }

    /**
     * 获得请求的color参数
     * @return bool|string
     */
    public function getRequestColor(){
        if($color = $this->getRequest()->getParam(Ado_SEO_Block_Catalog_Product_List_Colors::COLOR_ATTRIBUTE_CODE)){
           return $color = trim(strtolower($color));
        }
        return false;
    }

    /**
     * 给phtml文件去装配参数
     * @return string
     */
    public function getColorQueryVarname(){
        return Ado_SEO_Block_Catalog_Product_List_Colors::COLOR_ATTRIBUTE_CODE;
    }

    /**
     * 列表页每个商品下面的图片颜色切换
     * @param $product
     * @return mixed
     */
    public function getMiniColorHtml($product){
         $block = $this->getLayout()->createBlock('ado_seo/catalog_product_list_minicolor');
         return $block->getMiniColorHtml($product);
    }

    /**
     * 列表页上面工具栏上的图片切换
     * @param $product
     * @return mixed
     */
    public function getColorFilterHtml(){
        $block = $this->getLayout()->createBlock('ado_seo/catalog_product_list_colors');
        return $block->getColorFilterHtml();
    }



}
