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



class Mirasvit_Email_Model_Service_EventSaveProcessor_DefaultSaveProcessor
    extends Mirasvit_Email_Model_Service_EventSaveProcessor_AbstractSaveProcessor
{
    /**
     * {@inheritdoc}
     */
    public function saveEvent($eventCode, $uniqueKey, $args)
    {
        $args = $this->prepareArgs($args);
        $event = $this->checkSimilarEvent($eventCode, $uniqueKey, $args['time'] - $args['expire_after']);

        // Return null if similar event exists only for auto generation
        if ($event) {
            return null;
        }

        return $this->save($eventCode, $uniqueKey, $args);
    }
}