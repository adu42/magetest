<?php

/**
 * Ado Ciobanu
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @package     Ado_Seo
 * @copyright   Copyright (c) 2013 Ado Ciobanu
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Ado_SEO_Model_Catalog_Layer_Filter_Item extends Mage_Catalog_Model_Layer_Filter_Item
{

    protected $_helper;
    protected $_myRequestVar;

    protected function _helper()
    {
        if ($this->_helper === null) {
            $this->_helper = Mage::helper('ado_seo');
        }
        return $this->_helper;
    }
    
    
    protected function _getRequestVar(){
        if ($this->_myRequestVar === null) {
            $this->_myRequestVar = $this->_helper()->getFilterDEVarname($this->getFilter()->getRequestVar());
        }
        return $this->_myRequestVar;
    }

    /**
     * Get filter item url
     *
     * @return string
     */
    public function getUrl()
    {
        if (!$this->_helper()->isEnabled()) {
            return parent::getUrl();
        }

        $values = $this->getFilter()->getValues();
        if (!empty($values)) {
            $tmp = array_merge($values, array($this->getValue()));
            // Sort filters - small SEO improvement
            asort($tmp);
            $values = implode(Ado_SEO_Model_System_Rewrite::MULTIPLE_FILTERS_DELIMITER, $tmp);
        } else {
            $values = $this->getValue();
        }

        if ($this->_helper()->isCatalogSearch()) {
            $query = array(
                'isLayerAjax' => null,
                $this->_getRequestVar() => $values,
                Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
            );
            $url = Mage::getUrl('*/*/*', array('_current' => true, '_use_rewrite' => true, '_query' => $query));
            $url = substr($url,0,7).str_replace(array('//','./'),'/',substr($url,7));
            return $url;
        }

        return $this->_helper()->getFilterUrl(array(
            $this->_getRequestVar() => $values
        ));
    }

    /**
     * Get url for remove item from filter
     *
     * @return string
     */
    public function getRemoveUrl()
    {
        if (!$this->_helper()->isEnabled()) {
            return parent::getRemoveUrl();
        }

        $values = $this->getFilter()->getValues();
        
        if (!empty($values)) {
            $tmp = array_diff($values, array($this->getValue()));
            if (!empty($tmp)) {
                $values = implode(Ado_SEO_Model_System_Rewrite::MULTIPLE_FILTERS_DELIMITER, $tmp);
            } else {
                $values = null;
            }
        } else {
            $values = null;
        }
        if ($this->_helper()->isCatalogSearch()) {
            $query = array(
                'isLayerAjax' => null,
                $this->_getRequestVar() => $values
            );
            $params['_current'] = true;
            $params['_use_rewrite'] = true;
            $params['_query'] = $query;
            $params['_escape'] = true;
            $url = Mage::getUrl('*/*/*', $params);
            $url = substr($url,0,7).str_replace(array('//','./'),'/',substr($url,7));
            return $url;
        }

        return $this->_helper()->getFilterUrl(array(
            $this->_getRequestVar() => $values
        ));
    }

    /**
     * Check if current filter is selected
     * 
     * @return boolean 
     */
    public function isSelected()
    {
        $values = $this->getFilter()->getValues();
        if (in_array($this->getValue(), $values)) {
            return true;
        }
        return false;
    }

}
