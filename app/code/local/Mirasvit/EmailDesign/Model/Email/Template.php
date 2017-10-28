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
        // Class 'Mage_Core_Model_Email_Template_Abstract' exists since version 1.9.x
        if (method_exists('Mage_Core_Model_Email_Template', '_addEmailVariables')) {
            $variables = parent::_addEmailVariables($variables, $storeId);
        } else {
            if (!isset($variables['store'])) {
                $variables['store'] = Mage::app()->getStore($storeId);
            }
            if (!isset($variables['logo_url'])) {
                $variables['logo_url'] = $this->_getLogoUrl($storeId);
            }
            if (!isset($variables['logo_alt'])) {
                $variables['logo_alt'] = $this->_getLogoAlt($storeId);
            }
        }

        foreach ($this->defaultVariables as $code) {
            $id = isset($variables[$code.'_id']) ? $variables[$code.'_id'] : null;
            if (isset($variables[$code]) || !$id) {
                continue;
            }

            switch ($code) {
                case 'order':
                    $variables[$code] = Mage::getModel('sales/order')->load($id);

                    // if payment method does not exist (outdated|removed) Magento throws an exception,
                    // so we use it only if a method is still available
                    if ($variables[$code]->getPayment() && $variables[$code]->getPayment()->getMethod()) {
                        $paymentBlock = Mage::helper('payment')->getInfoBlock($variables[$code]->getPayment())
                            ->setIsSecureMode(true);
                        $paymentBlock->getMethod()->setStore($this->getStoreId());
                        // Set variable {{var payment_html}}
                        $variables['payment_html'] = $paymentBlock->toHtml();
                    }
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

    public function applyDefaultFilter($html, $variables)
    {
        $storeId = intval($variables['store_id']);

        $processor = Mage::getModel('core/email_template_filter');
        $processor->setStoreId($storeId);

        $template = Mage::getModel('core/email_template');

        if (method_exists($processor, 'setTemplateProcessor')) {
            $processor->setTemplateProcessor(array($template, 'getTemplateByConfigPath'));
        }

        $processor
            ->setIncludeProcessor(array($template, 'getInclude'))
            ->setVariables($this->_addEmailVariables($variables, $storeId));

        $html = $processor->filter($html);

        if (method_exists($processor, 'getInlineCssFile')) {
            $template->setInlineCssFile($processor->getInlineCssFile())
                ->setTemplateType(Mage_Core_Model_Template::TYPE_HTML);

            $html = $template->getPreparedTemplateText($html);
        }

        return $html;
    }
}
