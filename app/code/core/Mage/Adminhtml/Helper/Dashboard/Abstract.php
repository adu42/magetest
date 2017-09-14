<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition End User License Agreement
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magento.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright Copyright (c) 2006-2017 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Adminhtml abstract  dashboard helper.
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
 abstract class Mage_Adminhtml_Helper_Dashboard_Abstract extends Mage_Core_Helper_Data
 {
        /**
         * Helper collection
         *
         * @var Mage_Core_Model_Mysql_Collection_Abstract|Mage_Eav_Model_Entity_Collection_Abstract|array
         */
        protected  $_collection;

        /**
         * Parameters for helper
         *
         * @var array
         */
        protected  $_params = array();

        public function getCollection()
        {
            if(is_null($this->_collection)) {
                $this->_initCollection();
            }
            return $this->_collection;
        }

        abstract protected  function _initCollection();

        /**
         * Returns collection items
         *
         * @return array
         */
        public function getItems()
        {
            return is_array($this->getCollection()) ? $this->getCollection() : $this->getCollection()->getItems();
        }

        public function getCount()
        {
            return sizeof($this->getItems());
        }

        public function getColumn($index)
        {
            $result = array();
            foreach ($this->getItems() as $item) {
                if (is_array($item)) {
                    if(isset($item[$index])) {
                        $result[] = $item[$index];
                    } else {
                        $result[] = null;
                    }
                } elseif ($item instanceof Varien_Object) {
                    $result[] = $item->getData($index);
                } else {
                    $result[] = null;
                }
            }
            return $result;
        }

        public function setParam($name, $value)
        {
            $this->_params[$name] = $value;
        }

        public function setParams(array $params)
        {
            $this->_params = $params;
        }

        public function getParam($name)
        {
            if(isset($this->_params[$name])) {
                return $this->_params[$name];
            }

            return null;
        }

        public function getParams()
        {
            return $this->_params;
        }

 }
