<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition End User License Agreement
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magento.com/license/enterprise-edition
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
 * @copyright Copyright (c) 2006-2017 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */
class Mage_Catalog_Block_Product_List_Recommend extends Mage_Catalog_Block_Product_List
{
    protected function _getProductCollection()
    {
        if (is_null($this->_productCollection)) {
            $collection = Mage::getResourceModel('catalog/product_collection');
            Mage::getModel('catalog/layer')->prepareProductCollection($collection);
            // 促销推荐
            $collection->addStoreFilter();

            $items = Mage::getSingleton('catalog/session')->getData('visited_sku');
            try{
                if(!empty($items))$items= unserialize($items);
            }catch (Exception $e){ $items = array();}
            // 促销推荐

            $condition[]= array('attribute' => 'promotion', 'eq' => '1');
            if(!empty($items)){
                // 已访问推荐
                $condition[]= array('attribute' => 'entity_id', 'in' => $items);
            }
            $collection->addAttributeToFilter($condition);

            $this->_productCollection = $collection;
        }
        return $this->_productCollection;
    }
}
