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


class Mirasvit_Email_Model_Resource_Trigger_Chain_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('email/trigger_chain');
    }

    protected function _afterLoad()
    {
    	parent::_afterLoad();
    	foreach ($this->getItems() as $item) {
    		$item->prepareSerializedData();
    	}

    	return $this;
    }

    /**
     * Method used as the callback to sort email chains by delay
     *
     * @var $itemA Mirasvit_Email_Model_Trigger_Chain
     * @var $itemB Mirasvit_Email_Model_Trigger_Chain
     *
     * @return int
     */
    protected function sortByDelay($itemA, $itemB)
    {
        if ($itemA->getDelayTimestamp() == $itemB->getDelayTimestamp()) {
            return 0;
        }

        return $itemA->getDelayTimestamp() > $itemB->getDelayTimestamp() ? 1 : -1;
    }

    /**
     * Sort chain collection by delay
     *
     * @return $this
     */
    public function orderByDelay()
    {
        foreach ($this->_items as $itemKey => $item) {
            $item->setDelayTimestamp($item->getDays() * 60 * 60 * 24 + $item->getHours() * 60 * 60 + $item->getMinutes() * 60);
        }

        usort($this->_items, array($this, 'sortByDelay'));

        return $this;
    }
}