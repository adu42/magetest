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



class Mirasvit_Email_Model_Resource_Event extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('email/event', 'event_id');
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if ($object->isObjectNew() && !$object->hasCreatedAt()) {
            $object->setCreatedAt(Mage::getSingleton('core/date')->gmtDate());
        }

        $object->setUpdatedAt(Mage::getSingleton('core/date')->gmtDate());

        if ($object->hasData('args')) {
            $object->setArgsSerialized(serialize($object->getData('args')));
        }

        return parent::_beforeSave($object);
    }

    protected function _beforeDelete(Mage_Core_Model_Abstract $object)
    {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $connection->delete($this->getTable('email/event_trigger'), 'event_id = '.$object->getId());

        return parent::_beforeDelete($object);
    }

    /**
     * Create new trigger related events
     * if similar records exists - just update their status and created_at/updated_at dates.
     *
     * @param array $data
     */
    public function addTriggerEvents($data)
    {
        $columns = array('status', 'created_at', 'updated_at');
        $connection = $this->_getWriteAdapter();
        $connection->insertOnDuplicate($this->getTable('email/event_trigger'), $data, $columns);
    }

    public function addProcessedTriggerId($eventId, $triggerId)
    {
        $data = array('status' => 'done', 'updated_at' => Mage::getSingleton('core/date')->gmtDate());
        $where = array('trigger_id = ?' => (int) $triggerId, 'event_id = ?' => (int) $eventId);

        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $connection->update($this->getTable('email/event_trigger'), $data, $where);
    }

    public function getProcessedTriggerIds($eventId)
    {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');

        $select = $connection->select()->from($this->getTable('email/event_trigger'))->where('event_id=?', $eventId);

        return $connection->fetchAll($select);
    }

    public function removeProcessedTriggers($eventId)
    {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');

        $connection->delete($this->getTable('email/event_trigger'),
            'event_id = '.$eventId);

        return true;
    }
}
