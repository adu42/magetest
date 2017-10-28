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
        Mage::getModel('email/service_eventGenerateService')->generate(Mage::helper('email/event')->getActiveEvents());

        $triggers = Mage::getModel('email/trigger')->getCollection()
            ->addActiveFilter();

        foreach ($triggers as $trigger) {
            $trigger->processNewEvents();
        }

        return true;
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
}
