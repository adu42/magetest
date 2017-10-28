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



abstract class Mirasvit_Email_Model_Service_EventSaveProcessor_AbstractSaveProcessor
    implements Mirasvit_Email_Model_Service_EventSaveProcessorInterface
{
    protected function save($eventCode, $uniqueKey, $args)
    {
        $event = Mage::getModel('email/event')->setCode($eventCode)
            ->setUniqKey($uniqueKey)
            ->setArgs($args)
            ->setStoreIds($args['store_id'])
            ->setCreatedAt(date('Y-m-d H:i:s', $args['time']))
            ->save();

        return $event;
    }

    /**
     * Prepare event arguments.
     *
     * @param array $args
     *
     * @return array
     */
    protected function prepareArgs($args)
    {
        if (!isset($args['expire_after'])) {
            $args['expire_after'] = 3600;
        }

        if (!isset($args['time'])) {
            $args['time'] = time();
        }

        return $args;
    }

    /**
     * Check if not expired event with the same arguments exists yet.
     *
     * @param string     $code
     * @param string     $uniqKey
     * @param string|int $gmtExpireAt - timestamp, which indicates event expiration date
     *
     * @return Mirasvit_Email_Model_Event|void
     */
    protected function checkSimilarEvent($code, $uniqKey, $gmtExpireAt)
    {
        $event = Mage::getModel('email/event')->getCollection()
            ->addFieldToFilter('uniq_key', $uniqKey)
            ->addFieldToFilter('code', $code)
            ->addFieldToFilter('created_at', array('gt' => date('Y-m-d H:i:s', $gmtExpireAt)))
            ->getFirstItem();

        if ($event->getId()) {
            return $event;
        }
    }
}