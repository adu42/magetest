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



class Mirasvit_EmailDesign_Model_Email_Template extends Mage_Core_Model_Email_Template
{
    /**
     * Array of variable codes that can be used in the transactional emails.
     *
     * @var array
     */
    private $defaultVariables = array('order', 'customer');

    /**
     * Add variables that can be used in default transactional emails.
     *
     * @see Mage_Core_Model_Email_Template::_addEmailVariables()
     */
    protected function _addEmailVariables($variables, $storeId)
    {
        $variables = parent::_addEmailVariables($variables, $storeId);
        foreach ($this->defaultVariables as $code) {
            $id = isset($variables[$code.'_id']) ? $variables[$code.'_id'] : null;
            if (isset($variables[$code]) || !$id) {
                continue;
            }

            switch ($code) {
                case 'order':
                    $variables[$code] = Mage::getModel('sales/order')->load($id);
                    break;
                case 'customer':
                    $variables[$code] = Mage::getModel('customer/customer')->load($id);
                    break;
            }
        }

        return $variables;
    }

    /**
     * Process email template content through template engine of Mirasvit extension too.
     *
     * @see Mage_Core_Model_Email_Template::getProcessedTemplate()
     */
    public function getProcessedTemplate(array $variables = array())
    {
        $content = parent::getProcessedTemplate($variables);

        $emailDesign = Mage::app()->getLayout()->createBlock('emaildesign/template');
        $content = $emailDesign->render($content, $variables);

        return $content;
    }
}
