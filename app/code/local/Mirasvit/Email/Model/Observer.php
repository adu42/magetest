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
 * @version   1.0.34
 * @build     705
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */
class Mirasvit_Email_Model_Observer extends Varien_Object
{
    public function sendQueue($observer)
    {
        $queueCollection = Mage::getModel('email/queue')->getCollection()
            ->addReadyFilter()
            ->setPageSize(10);

        foreach ($queueCollection as $item) {
            try {
                $item->send();
            } catch (Exception $e) {
                $item->error($e->__toString());
            }
        }
    }

    public function checkEvents()
    {
        $events = Mage::helper('email/event')->getActiveEvents();

        foreach ($events as $eventCode) {
            $event = Mage::helper('email/event')->getEventModel($eventCode);
            if ($event) {
                $event->check($eventCode);
            }
        }

        $triggers = Mage::getModel('email/trigger')->getCollection()
            ->addActiveFilter();

        foreach ($triggers as $trigger) {
            $trigger->processNewEvents();
        }

        return true;
    }

    /**
     * Event 'after_check_m_email_events'
     * Create new trigger related events based on newly registered events.
     *
     * @param Varien_Event_Observer $observer
     */
    public function afterCheckEvents(Varien_Event_Observer $observer)
    {
        $newEvents = $observer->getNewEvents();

        if (empty($newEvents)) {
            return;
        }

        if ($observer->getEventModel()->getTriggerId()) {
            $triggers = array($observer->getEventModel()->getTriggerId());
        } else {
            $triggers = Mage::helper('email/event')->getAssociatedTriggers($observer->getEventCode());
        }

        Mage::getModel('email/event')->addTriggerEvents($observer->getNewEvents(), $triggers);
    }

    public function clearOldData()
    {
        $monthAgo = date('Y-m-d H:i:s', Mage::getSingleton('core/date')->gmtTimestamp() - 30 * 24 * 60 * 60);

        # Step 1. Remove old events
        $collection = Mage::getModel('email/event')->getCollection()
            ->addFieldToFilter('updated_at', array('lt' => $monthAgo));

        foreach ($collection as $event) {
            $event->delete();
        }

        # Step 2. Remove old mails
        $collection = Mage::getModel('email/queue')->getCollection()
            ->addFieldToFilter('status', array('neq' => Mirasvit_Email_Model_Queue::STATUS_PENDING))
            ->addFieldToFilter('scheduled_at', array('lt' => $monthAgo));

        foreach ($collection as $queue) {
            $queue->delete();
        }
    }

    public function onWishlistShared($observer)
    {
        Mage::getModel('email/event_wishlist_wishlist')->check('wishlist_wishlist|shared', false, $observer);
    }

    public function onNewsletterSubscriberSaveBefore($observer)
    {
        $originalStatus = $observer->getEvent()->getDataObject()->getOrigData('subscriber_status');
        $status = $observer->getEvent()->getDataObject()->getSubscriberStatus();

        if ($originalStatus !== $status) {
            Mage::getModel('email/event_customer_newsletter')->check('customer_newsletter|subscription_status_changed', false, $observer);
        }

        if ($status == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED) {
            Mage::getModel('email/event_customer_newsletter')->check('customer_newsletter|subscribed', false, $observer);
        } elseif ($status == Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED) {
            Mage::getModel('email/event_customer_newsletter')->check('customer_newsletter|unsubscribed', false, $observer);
        }
    }

    public function onCustomerSaveAfter($observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        if ($customer->getId()) {
            // Process the event if a customer group was changed
            if ($customer->getOrigData('group_id') !== $customer->getGroupId()) {
                Mage::getModel('email/event_customer_groupchanged')->check(Mirasvit_Email_Model_Event_Customer_Groupchanged::EVENT_CODE, false, $observer);
            }
        }
    }

    public function onReviewBeforeSave($observer)
    {
        $originalReviewStatus = $observer->getEvent()->getDataObject()->getOrigData('status_id');
        $newReviewStatus = $observer->getEvent()->getDataObject()->getData('status_id');

        if ($newReviewStatus == Mage_Review_Model_Review::STATUS_APPROVED &&
            $originalReviewStatus !== $newReviewStatus
        ) {
            Mage::getModel('email/event_customer_review')->check('customer_review|approved', false, $observer);
        }
    }

    public function onEmailQueueGetContentAfter($observer)
    {
        $queue = $observer->getQueue();

        Mage::helper('email')->prepareQueueContent($queue);
    }

    public function deleteExpiredCoupons()
    {
        $coupons = Mage::getModel('salesrule/coupon')->getCollection()
            ->addFieldToFilter('code', array('like' => 'EML%'))
            ->addFieldToFilter('expiration_date', array(
                'neq' => '0000-00-00 00:00:00',
            ))
            ->addFieldToFilter('expiration_date', array(
                'lteq' => Mage::getSingleton('core/date')->gmtDate(),
            ));

        foreach ($coupons as $coupon) {
            $coupon->delete();
        }

        return $this;
    }
}
