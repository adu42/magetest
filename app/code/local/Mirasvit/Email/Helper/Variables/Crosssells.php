<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Follow Up Email
 * @version   1.1.23
 * @build     800
 * @copyright Copyright (C) 2017 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_Email_Helper_Variables_Crosssells
{
    public function getCrossSellHtml($parent, $args)
    {
        $collection = $this->getCrossSellProducts($parent, $args);

        $crossBlock = Mage::app()->getLayout()->createBlock('email/cross')
            ->setCollection($collection);

        return $crossBlock->toHtml();
    }

    public function getCrossSellProducts($parent, $args)
    {
        $productIds = $this->getCrossSellProductIds($parent, $args);
        $productIds[] = 0;

        $collection = Mage::getModel('catalog/product')->getCollection()
            ->setStoreId($parent->getStoreId())
            ->addFieldToFilter('entity_id', array('in' => $productIds))
            ->addAttributeToSelect('thumbnail')
            ->addAttributeToSelect('small_image')
            ->addAttributeToSelect('image')
            ->addAttributeToSelect('name')
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addStoreFilter()
            ->addUrlRewrite();

        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($collection);

        $collection->getSelect()->reset('order');
        $collection->getSelect()->order(new Zend_Db_Expr('RAND()'));

        return $collection;
    }

    public function getCrossSellProductIds($parent, $args)
    {
        if ($parent->hasData('cross_sell')) {
            return $parent->getData('cross_sell');
        }

        $productIds = array();

        if ($parent->getPreview()) {
            $collection = Mage::getModel('catalog/product')->getCollection();
            $collection->getSelect()->limit(100)
                ->order('RAND()');

            foreach ($collection as $item) {
                $productIds[] = $item->getId();
            }

            return $productIds;
        }

        if ($parent->getChain()) {
            $chain = $parent->getChain();

            if ($chain->getCrossSellsEnabled()) {
                $crossType = $chain->getCrossSellsTypeId();
                $productIds = array();

                switch ($crossType) {
                    case Mirasvit_Email_Model_System_Source_CrossSell::MAGE_CROSS:
                    case Mirasvit_Email_Model_System_Source_CrossSell::MAGE_RELATED:
                    case Mirasvit_Email_Model_System_Source_CrossSell::MAGE_UPSELLS:
                    case Mirasvit_Email_Model_System_Source_CrossSell::MAGE_NEW:
                        // base Products
                        $baseProducts = $this->_getBaseProducts($parent);

                        foreach ($baseProducts as $product) {
                            $crossIds = array();
                            if ($product) {
                                if ($crossType == Mirasvit_Email_Model_System_Source_CrossSell::MAGE_CROSS) {
                                    $crossIds = $product->getCrossSellProductIds();
                                } elseif ($crossType == Mirasvit_Email_Model_System_Source_CrossSell::MAGE_RELATED) {
                                    $crossIds = $product->getRelatedProductIds();
                                } elseif ($crossType == Mirasvit_Email_Model_System_Source_CrossSell::MAGE_UPSELLS) {
                                    $crossIds = $product->getUpSellProductIds();
                                }
                            }

                            $productIds = array_merge($crossIds, $productIds);
                        }

                        if ($crossType == Mirasvit_Email_Model_System_Source_CrossSell::MAGE_NEW) {
                            $productIds = $this->getNewArrivalProductIds();
                        }

                    break;

                    case Mirasvit_Email_Model_System_Source_CrossSell::AW_WBTAB:
                        if (Mage::helper('email')->isWBTABInstalled()) {
                            $baseProducts = $this->_getBaseProducts($parent);
                            $baseProductsIds = array();
                            foreach ($baseProducts as $product) {
                                $baseProductsIds[] = $product->getId();
                            }
                            $productIds = Mage::getModel('relatedproducts/api')
                                ->getRelatedProductsFor($baseProductsIds, $storeId);
                            $productIds = array_keys($productIds);
                        }
                    break;

                    case Mirasvit_Email_Model_System_Source_CrossSell::AW_ARP2:
                        if (Mage::helper('email')->isARP2Installed()
                            && class_exists('AW_Autorelated_Model_Api')
                            && ($parent->getQuoteId() || $parent->getOrderId())
                        ) {
                            $arp2Collection = Mage::getModel('awautorelated/blocks')->getCollection()
                                ->addTypeFilter(AW_Autorelated_Model_Source_Type::SHOPPING_CART_BLOCK)
                                ->addStatusFilter()
                                ->addDateFilter()
                                ->setPriorityOrder('DESC');
                            $ids = $arp2Collection->getAllIds();
                            if (count($ids) > 0) {
                                $quoteId = $parent->getQuoteId()
                                    ? $parent->getQuoteId()
                                    : $parent->getOrder()->getQuoteId();
                                foreach ($ids as $arp2Block) {
                                    $block = Mage::getModel('awautorelated/blocks')->load($arp2Block);
                                    $productIds = array_merge($productIds, Mage::getModel('awautorelated/api')
                                            ->getRelatedProductsForShoppingCartRule($arp2Block, $quoteId));
                                }
                            }
                        }
                    break;

                    case Mirasvit_Email_Model_System_Source_CrossSell::TM_CUSTOMER:
                        $baseProductsIds = $this->_getBaseProductsIds($parent);
                        $baseProductsIds[] = 0;

                        $collection = Mage::getResourceModel('catalog/product_collection');
                        $collection->getSelect()
                            ->join(
                                array('sc' => Mage::getResourceModel('soldtogether/customer')->getMainTable()),
                                'e.entity_id = sc.related_product_id',
                                array()
                            )
                            ->where('sc.product_id IN (?)', $baseProductsIds)
                            ->order(new Zend_Db_Expr('RAND()'));

                        $productIds = $collection->getAllIds();
                    break;

                    case Mirasvit_Email_Model_System_Source_CrossSell::TM_ORDER:
                        $baseProductsIds = $this->_getBaseProductsIds($parent);
                        $baseProductsIds[] = 0;

                        $collection = Mage::getResourceModel('catalog/product_collection');
                        $collection->getSelect()
                            ->join(
                                array('so' => Mage::getResourceModel('soldtogether/order')->getMainTable()),
                                'e.entity_id = so.related_product_id',
                                array()
                            )
                            ->where('so.product_id IN (?)', $baseProductsIds)
                            ->order(new Zend_Db_Expr('RAND()'));

                        $productIds = $collection->getAllIds();
                    break;

                    case Mirasvit_Email_Model_System_Source_CrossSell::AMASTY_MV:
                        if (Mage::helper('core')->isModuleEnabled('Amasty_Mostviewed')) {
                            $baseProductsIds = $this->_getBaseProductsIds($parent);
                            $baseProductsIds[] = 0;

                            foreach ($baseProductsIds as $id) {
                                $collection = Mage::helper('ammostviewed')->getViewedWith($id);
                                foreach ($collection as $product) {
                                    $productIds[$product->getId()] = $product->getId();
                                }
                            }
                        }
                    break;

                    case Mirasvit_Email_Model_System_Source_CrossSell::IWD_ARP:
                        if (Mage::helper('core')->isModuleEnabled('IWD_AutoRelatedProducts')) {
                            $baseProductsIds = $this->_getBaseProductsIds($parent);
                            $blocks = Mage::getModel('iwd_autorelatedproducts/blocks')->getCollection();
                            foreach ($blocks as $block) {
                                $block->load($block->getId());

                                 // Collect current products
                                $currentRule = $block->getCurrentConditions();
                                $currentRule->setProductsFilter($baseProductsIds);
                                $matchedProducts = $currentRule->getMatchingProductIds();

                                // Collect related products for current products
                                $relatedRule = $block->getRelatedConditions();
                                $relatedRule->setProductsFilter($matchedProducts);
                                $relatedProducts = $relatedRule->getMatchingProductIds();
                                $productIds = array_merge($productIds, $relatedProducts);
                            }
                        }
                    break;
                }

                shuffle($productIds);
            }
        }

        $parent->setData('cross_sell', $productIds);

        return $productIds;
    }

    protected function _getBaseProducts($parent)
    {
        $result = array();

        if ($parent->getOrder()) {
            foreach ($parent->getOrder()->getAllVisibleItems() as $item) {
                $result[] = $item->getProduct();
            }
        }

        if ($parent->getQuote() && count($result) == 0) {
            foreach ($parent->getQuote()->getAllVisibleItems() as $item) {
                $result[] = $item->getProduct();
            }
        }

        if ($parent->getCustomer() && count($result) == 0) {
            $orders = Mage::getModel('sales/order')
                ->getCollection()
                ->addAttributeToFilter('customer_id', $parent->getCustomer()->getId());
            foreach ($orders as $order) {
                foreach ($order->getAllVisibleItems() as $item) {
                    $result[] = $item->getProduct();
                }
            }
        }

        return $result;
    }

    protected function _getBaseProductsIds($parent)
    {
        $baseProducts = $this->_getBaseProducts($parent);
        $baseProductsIds = array();

        foreach ($baseProducts as $product) {
            $baseProductsIds[] = $product->getId();
        }

        return $baseProductsIds;
    }

    protected function getNewArrivalProductIds()
    {
        $todayStartOfDayDate = Mage::app()->getLocale()->date()
            ->setTime('00:00:00')
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        $todayEndOfDayDate = Mage::app()->getLocale()->date()
            ->setTime('23:59:59')
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());

        $collection = $collection->addStoreFilter()
            ->addAttributeToFilter('news_from_date', array('or' => array(
                0 => array('date' => true, 'to' => $todayEndOfDayDate),
                1 => array('is' => new Zend_Db_Expr('null')), ),
            ), 'left')
            ->addAttributeToFilter('news_to_date', array('or' => array(
                0 => array('date' => true, 'from' => $todayStartOfDayDate),
                1 => array('is' => new Zend_Db_Expr('null')), ),
            ), 'left')
            ->addAttributeToFilter(
                array(
                    array('attribute' => 'news_from_date', 'is' => new Zend_Db_Expr('not null')),
                    array('attribute' => 'news_to_date', 'is' => new Zend_Db_Expr('not null')),
                )
            )
            ->addAttributeToSort('news_from_date', 'desc')
            ->setPageSize(100)
            ->setCurPage(1);

        return $collection->getAllIds();
    }
}
