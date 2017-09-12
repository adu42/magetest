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
 * @package     Mage_Catalog
 * @copyright Copyright (c) 2006-2016 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Product description block
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Block_Product_View_Attributes extends Mage_Core_Block_Template
{
    protected $_product = null;

    function getProduct()
    {
        if (!$this->_product) {
            $this->_product = Mage::registry('product');
        }
        return $this->_product;
    }

    /**
     * $excludeAttr is optional array of attribute codes to
     * exclude them from additional data array
     *
     * @param array $excludeAttr
     * @return array
     */
    public function getAdditionalData(array $excludeAttr = array())
    {
        $data = array();
        $product = $this->getProduct();
        $attributes = $product->getAttributes();
        foreach ($attributes as $attribute) {
//            if ($attribute->getIsVisibleOnFront() && $attribute->getIsUserDefined() && !in_array($attribute->getAttributeCode(), $excludeAttr)) {
            if ($attribute->getIsVisibleOnFront() && !in_array($attribute->getAttributeCode(), $excludeAttr)) {
                $value = $attribute->getFrontend()->getValue($product);
                $value = trim($value);
                if (!$product->hasData($attribute->getAttributeCode())) {
                    continue;
                    $value = Mage::helper('catalog')->__('N/A');
                } elseif ((string)$value == '') {
                    continue;
                    $value = Mage::helper('catalog')->__('No');
                } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                    $value = Mage::app()->getStore()->convertPrice($value, true);
                }

                $_as = '';
                if (is_string($value) && strlen($value)) {
                    if ($attribute->getAttributeCode() == 'fabric') {
                        $bv = $product->getData($attribute->getAttributeCode());
                        if ($bv) {
                            if (!is_array($bv)) $bv = array($bv);
                            $options = $attribute->setStoreId(0)->getSource()->getAllOptions(false);
                            if (!empty($options)) {
                                foreach ($options as $option) {
                                    if ($option['value'] == $bv[0]) {
                                        $_as = trim($option['label']);
                                        $_as = strtolower($_as);
                                        $_as = str_replace(' ', '_', $_as);
                                        $_as = preg_replace("#[^A-z0-9_]#", '', $_as);
                                        break;
                                    }
                                }
                            }
                        }
                    }

                    $_value = explode(',', $value);
                    foreach ($_value as &$_val) {
                        $_val = $this->__(trim($_val));
                    }
                    $value = implode(',', $_value);

                    $data[$attribute->getAttributeCode()] = array(
                        'label' => $attribute->getStoreLabel(),
                        'value' => $value,
                        'code' => $attribute->getAttributeCode(),
                        'as' => $_as
                    );
                }
            }
        }
        return $data;
    }
}
