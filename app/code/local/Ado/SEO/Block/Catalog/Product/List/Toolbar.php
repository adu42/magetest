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
class Ado_SEO_Block_Catalog_Product_List_Toolbar extends Mage_Catalog_Block_Product_List_Toolbar
{

    /**
     * Return current URL with rewrites and additional parameters
     *
     * @param array $params Query parameters
     * @return string
     */
    public function getPagerUrl($params = array())
    {
        if (!$this->helper('ado_seo')->isEnabled()) {
            return parent::getPagerUrl($params);
        }

        if ($this->helper('ado_seo')->isCatalogSearch()) {
            $params['isLayerAjax'] = null;
            $url = parent::getPagerUrl($params);
            $url = substr($url,0,7).str_replace(array('//','./'),'/',substr($url,7));
            return $url;
        }
        return $this->helper('ado_seo')->getPagerUrl($params);
    }
   /**
	* �.
	* by ado	
	*/
	 protected function _memorizeParam($param, $value)
    {
        return $this;
    }
}