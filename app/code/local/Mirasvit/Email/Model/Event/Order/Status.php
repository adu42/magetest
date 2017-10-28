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



class Mirasvit_Email_Model_Event_Order_Status extends Mirasvit_Email_Model_Event_Abstract
{
    const EVENT_CODE = 'order_status|';

    public function getEventsGroup()
    {
        return Mage::helper('email')->__('Order');
    }

    public function getEvents()
    {
        $result = array();
        $result[self::EVENT_CODE] = __('Order obtained new status');

        $orderStatuses = Mage::getSingleton('sales/order_config')->getStatuses();
        foreach ($orderStatuses as $code => $name) {
            $result[self::EVENT_CODE.$code] = __("Order obtained '%s' status", $name);
        }

        return $result;
    }

    public function findEvents($eventCode, $timestamp)
    {
        $events = array();
        $fromDate = date('Y-m-d H:i:s', ($timestamp - (60 * 2))); // look for orders changed 2 minutes ago
        $resource = Mage::getSingleton('core/resource');

        $historyCollection = Mage::getModel('sales/order_status_history')->getCollection()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('main_table.created_at', array('gt' => $fromDate))
            ->setOrder('main_table.created_at', 'asc');

        $historyCollection->getSelect()
            ->joinLeft(
                array('order' => $resource->getTableName('sales/order')),
                'main_table.parent_id = order.entity_id',
                array(
                    'customer_name' => 'CONCAT_WS(" ", order.customer_firstname, order.customer_lastname)',
                    'customer_email' => 'order.customer_email',
                    'customer_id' => 'order.customer_id',
                    'store_id' => 'order.store_id',
                )
            )
            ->group('CONCAT(main_table.parent_id, main_table.status)');

        $historyCollectionData = $historyCollection->getData();
        foreach ($historyCollectionData as $history) {
            $code = self::EVENT_CODE.$history['status'];

            if (($code == $eventCode || $eventCode == self::EVENT_CODE) && $this->isOrderStatusFirst($history)) {
                $args = array(
                    'time' => strtotime($history['created_at']),
                    'created_at' => $history['created_at'],
                    'customer_email' => $history['customer_email'],
                    'customer_name' => $history['customer_name'],
                    'customer_id' => $history['customer_id'],
                    'order_id' => $history['parent_id'],
                    'store_id' => $history['store_id'],
                );

                $events[] = $args;
            }
        }

        return $events;
    }

    /**
     * Method checks if the records for the order with the same ID and status already exist in the table 'order_status_history'.
     *
     * When a comment is added to an order Magento creates a new record for the table 'order_status_history'
     * the extension may consider this record as a new event, this may lead to sending of duplicated emails to the customers.
     *
     * @param array $historyOrder - array with data of object Mage_Sales_Model_Order_Status_History
     *
     * @return bool - TRUE if a record is first for an order, otherwise - FALSE
     */
    private function isOrderStatusFirst($historyOrder)
    {
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_read');

        $query = "SELECT COUNT(*) FROM {$resource->getTableName('sales/order_status_history')} WHERE parent_id = :parent_id AND status = :status AND created_at < :created_at";
        $stmt = $connection->query($query, array(
            'parent_id' => $historyOrder['parent_id'],
            'status' => $historyOrder['status'],
            'created_at' => $historyOrder['created_at'],
        ));

        $result = !$stmt->fetchColumn();

        return $result;
    }
}
