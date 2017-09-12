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


/**
 * Event Class.
 *
 * @category Mirasvit
 */
class Mirasvit_Email_Model_Event extends Mage_Core_Model_Abstract
{
    protected $_args = null;

    protected function _construct()
    {
        $this->_init('email/event');
    }

    /**
     * Return array of event arguments.
     *
     * @return array
     */
    public function getArgs()
    {
        if ($this->_args == null) {
            $this->_args = unserialize($this->getData('args_serialized'));
        }

        return $this->_args;
    }

    /**
     * Create new trigger related events.
     *
     * @param array $events - array of IDs of newly created events
     * @param array $triggers - triggers associated with passed events
     */
    public function addTriggerEvents($events, $triggers)
    {
        $data = array();
        foreach ($events as $eventId) {
            foreach ($triggers as $triggerId) {
                $date = Mage::getSingleton('core/date')->gmtDate();
                $data[] = array(
                    'event_id' => $eventId,
                    'trigger_id' => $triggerId,
                    'status' => 'new',
                    'created_at' => $date,
                    'updated_at' => $date,
                );
            }
        }

        $this->getResource()->addTriggerEvents($data);
    }

    public function addProcessedTriggerId($triggerId)
    {
        $this->getResource()->addProcessedTriggerId($this->getId(), $triggerId);
    }

    public function removeProcessedTriggers()
    {
        $this->getResource()->removeProcessedTriggers($this->getId());
    }
}
