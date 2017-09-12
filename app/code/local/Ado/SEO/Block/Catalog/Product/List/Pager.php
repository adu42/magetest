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
class Ado_SEO_Block_Catalog_Product_List_Pager extends Mage_Page_Block_Html_Pager
{

    protected $_mode = '';
    protected $_customUrl = '';
    protected function _construct()
    {
        parent::_construct();
    }
    /**
     * Return current URL with rewrites and additional parameters
     *
     * @param array $params Query parameters
     * @return string
     */
    public function getPagerUrl($params = array())
    {
        if (!Mage::helper('ado_seo')->isEnabled()) {
            return parent::getPagerUrl($params);
        }

        if ($this->helper('ado_seo')->isCatalogSearch()) {
            $params['isLayerAjax'] = null;
            $url = parent::getPagerUrl($params);
            $url = substr($url,0,7).str_replace(array('//','./'),'/',substr($url,7));
            return $url;
        }
        if($this->getQueryMode()){
            $urlParams = array();
            $urlParams['_current']  = true;
            $urlParams['_escape']   = true;
            $urlParams['_use_rewrite']   = true;
            $urlParams['_query']    = $params;
            $url = $this->getCustomUrl();
            $url = $this->getUrl($url, $urlParams);
            $mode = $this->getQueryMode();
            $_models = explode('|',$mode);
            if(count($_models)==2){
                $url = str_replace('/'.$_models[0].'/','/'.$_models[1].'/',$url);
            }
            $url = str_replace('/?','?',$url);
            return $url;
        }
        
        return $this->helper('ado_seo')->getPagerUrl($params);
    }

    public function getQueryMode(){
    return $this->_mode;
}

    public function setQueryMode($mode=''){
        $this->_mode=$mode;
        return $this;
    }
    public function getCustomUrl(){
        return $this->_customUrl;
    }

    public function setCustomUrl($url=''){
        $this->_customUrl=$url;
        return $this;
    }
}