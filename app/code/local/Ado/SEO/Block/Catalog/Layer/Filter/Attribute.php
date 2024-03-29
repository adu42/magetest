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
class Ado_SEO_Block_Catalog_Layer_Filter_Attribute extends Mage_Catalog_Block_Layer_Filter_Attribute
{

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();

        if ($this->helper('ado_seo')->isEnabled()
            && $this->helper('ado_seo')->isMultipleChoiceFiltersEnabled()) {
            /**
             * Modify template for multiple filters rendering
             * It has checkboxes instead of classic links
             */
            $this->setTemplate('ado_seo/catalog/layer/filter.phtml');
        }
    }

}