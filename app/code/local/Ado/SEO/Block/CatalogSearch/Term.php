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
class Ado_SEO_Block_CatalogSearch_Term extends Mage_CatalogSearch_Block_Term
{

    public function __construct()
    {
        parent::__construct();
    }

    protected function _loadTerms()
    {
        if (empty($this->_terms)) {
            $this->_terms = array();
            $terms = Mage::getResourceModel('catalogsearch/query_collection')
                ->setPopularQueryFilter(Mage::app()->getStore()->getId())
                ->addFieldToFilter('display_in_terms',1)
                ->setPageSize(100)
                ->load()
                ->getItems();

            if( count($terms) == 0 ) {
                return $this;
            }


            $this->_maxPopularity = reset($terms)->getPopularity();
            $this->_minPopularity = end($terms)->getPopularity();
            $range = $this->_maxPopularity - $this->_minPopularity;
            $range = ( $range == 0 ) ? 1 : $range;
            foreach ($terms as $term) {
                if( !$term->getPopularity() ) {
                    continue;
                }
                $term->setRatio(($term->getPopularity()-$this->_minPopularity)/$range);
                $temp[$term->getName()] = $term;
                $termKeys[] = $term->getName();
            }
            natcasesort($termKeys);

            foreach ($termKeys as $termKey) {
                $this->_terms[$termKey] = $temp[$termKey];
            }
        }
        return $this;
    }
    
    public function getSearchUrl($obj)
    {
        $keyword = $obj->getName();
        $keyword = strtolower($keyword);
        $keyword = str_replace(array(' ','+'),'_',$keyword);
        $url = Mage::getModel('core/url');
        return $url->getUrl('search').$keyword.'.html';
    }

}