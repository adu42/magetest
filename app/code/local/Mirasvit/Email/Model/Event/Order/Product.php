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



class Mirasvit_Email_Model_Event_Order_Product extends Mirasvit_Email_Model_Event_Abstract
{
    const EVENT_CODE = 'order_product';

    public function getEventsGroup()
    {
        return Mage::helper('email')->__('Product');
    }

    public function getEvents()
    {
        $result = array();
        $result[self::EVENT_CODE] = __('New product ordered');

        return $result;
    }

    public function findEvents($eventCode, $timestamp)
    {
        $events = array();
        $fromDate = date('Y-m-d H:i:s', $timestamp);
        $resource = Mage::getSingleton('core/resource');

        $products = Mage::getResourceModel('sales/order_item_collection')
            ->addFieldToFilter('main_table.created_at', array('gt' => $fromDate))
            ->filterByParent();

        $products->getSelect()->joinLeft(array('order' => $resource->getTableName('sales/order')),
            'main_table.order_id = order.entity_id',
            array(
                'customer_id' => 'order.customer_id',
                'customer_email' => 'order.customer_email',
                'customer_name' => 'CONCAT_WS(" ", order.customer_firstname, order.customer_lastname)',
            )
        );

        foreach ($products as $product) {
            $args = array(
                'time' => strtotime($product['created_at']),
                'created_at' => $product['created_at'],
                'customer_email' => $product['customer_email'],
                'customer_name' => $product['customer_name'],
                'customer_id' => $product['customer_id'],
                'order_id' => $product['order_id'],
                'order_item_id' => $product['item_id'],
                'store_id' => $product['store_id'],
            );

            $events[] = $args;
        }

        return $events;
    }

    public function isActive()
    {
        return false;
    }
}
