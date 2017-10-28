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


class Mirasvit_EmailDesign_Helper_Variables_Order
{
    /**
     * Get order object associated with the event.
     *
     * @param $parent
     * @param $args
     *
     * @return bool|Mage_Core_Model_Abstract|Mage_Sales_Model_Order
     */
    public function getOrder($parent, $args)
    {
        $order = false;

        if ($parent->getData('order')) {
            return $parent->getData('order');
        } elseif ($parent->getData('order_id')) {
            $order = Mage::getModel('sales/order')->load($parent->getData('order_id'));
        }

        $parent->setData('order', $order);

        return $order;
    }

    public function getFirstOrderedProduct($parent, $args)
    {
        $product = false;

        if ($order = $this->getOrder($parent, $args)) {
            foreach ($order->getAllVisibleItems() as $item) {
                $product = $item->getProduct();
                break;
            }
        }

        return $product;
    }
    
    public function getFirstOrderedProductName($parent, $args)
    {
        if ($product = $this->getFirstOrderedProduct($parent, $args)) {
            return $product->getName();
        }

        return false;
    }

    /**
     * Get order item object associated with the event.
     * Or get the first item from ordered items collection.
     *
     * @param $parent
     * @param $args
     *
     * @return null|Mage_Core_Model_Abstract|Mage_Sales_Model_Order_Item
     */
    public function getOrderItem($parent, $args)
    {
        $item = null;
        if ($parent->getData('order_item')) {
            return $parent->getData('order_item');
        } elseif ($parent->getData('order_item_id')) {
            $item = Mage::getModel('sales/order_item')->load($parent->getData('order_item_id'));
        } elseif($parent->getData('order_id')) {
            // Filter items by `parent_item_id` IS NULL
            $item = $this->getOrder($parent, $args)->getItemsCollection(array(), true)->getFirstItem();
        }

        $parent->setData('order_item', $item);

        return $item;
    }
}
