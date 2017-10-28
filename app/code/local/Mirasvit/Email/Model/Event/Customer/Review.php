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



class Mirasvit_Email_Model_Event_Customer_Review extends Mirasvit_Email_Model_Event_Abstract
{
    const EVENT_CODE = 'customer_review|';

    public function getEventsGroup()
    {
        return Mage::helper('email')->__('Customer');
    }

    public function getEvents()
    {
        $result = array();

        $result[self::EVENT_CODE.'new'] = Mage::helper('email')->__('Customer submits review');
        $result[self::EVENT_CODE.'approved'] = Mage::helper('email')->__('Customer review is approved');

        return $result;
    }

    public function findEvents($eventCode, $timestamp)
    {
        $events = array();
        $fromDate = date('Y-m-d H:i:s', $timestamp);
        $attrFirstname = Mage::getSingleton('eav/config')->getAttribute('customer', 'firstname');
        $attrLastname = Mage::getSingleton('eav/config')->getAttribute('customer', 'lastname');
        $collection = Mage::getModel('review/review')->getCollection();

        $collection->getSelect()
            ->columns(array(
                'customer_name' => 'CONCAT(c_firstname.value , " ", c_lastname.value)',
                'store_id' => 'detail.store_id',
            ))
            ->join(array('review_entity' => $collection->getResource()->getTable('review/review_entity')),
                'main_table.entity_id = review_entity.entity_id',
                array()
            )
            ->join(array('c' => $collection->getResource()->getTable('customer/entity')),
                'detail.customer_id = c.entity_id',
                array('email')
            )
            ->joinLeft(array('c_firstname' => $attrFirstname->getBackend()->getTable()),
                'c.entity_id = c_firstname.entity_id AND c_firstname.attribute_id = '.$attrFirstname->getAttributeId(),
                array()
            )
            ->joinLeft(array('c_lastname' => $attrLastname->getBackend()->getTable()),
                'c.entity_id = c_lastname.entity_id AND c_lastname.attribute_id = '.$attrLastname->getAttributeId(),
                array()
            )
            ->joinLeft(array('product' => $collection->getResource()->getTable('catalog/product')),
                'entity_pk_value = product.entity_id',
                array('sku')
            )
            ->where('review_entity.entity_code = ?', Mage_Review_Model_Review::ENTITY_PRODUCT_CODE)
            ->where('main_table.created_at >= ?', $fromDate);

        // Only if eventCode is 'customer_review|approved'
        if ($eventCode === self::EVENT_CODE.'approved') {
            $collection->getSelect()->where('main_table.status_id = ?', Mage_Review_Model_Review::STATUS_APPROVED);
        }

        foreach ($collection as $review) {
            $events[] = array(
                'time' => strtotime($review->getCreatedAt()),
                'customer_email' => $review->getEmail(),
                'customer_name' => $review->getCustomerName(),
                'customer_id' => $review->getCustomerId(),
                'store_id' => $review->getStoreId(),
                'review_id' => $review->getId(),
                'sku' => $review->getSku(),
                'product_id' => $review->getEntityPkValue(),
            );
        }

        return $events;
    }

    protected function observe($eventCode, $observer)
    {
        $review = $observer->getDataObject();
        if (!$review->getCustomerId()) {
            return array();
        }

        $customer = Mage::getModel('customer/customer')->load($review->getCustomerId());
        $product = Mage::getModel('catalog/product')->load($review->getEntityPkValue());

        $event = array(
            'time' => strtotime($review->getCreatedAt()),
            'customer_email' => $customer->getEmail(),
            'customer_name' => $customer->getName(),
            'customer_id' => $customer->getId(),
            'store_id' => $review->getStoreId(),
            'review_id' => $review->getId(),
            'sku' => $product->getSku(),
            'product_id' => $review->getEntityPkValue(),
        );

        return array($event);
    }
}
