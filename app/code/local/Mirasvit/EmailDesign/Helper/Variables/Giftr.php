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



class Mirasvit_EmailDesign_Helper_Variables_Giftr
{
    /**
     * Retrieve customer registry.
     *
     * @param $parent
     * @param $args
     *
     * @return bool|Mage_Core_Model_Abstract|Mirasvit_Giftr_Model_Registry
     */
    public function getRegistry($parent, $args)
    {
        $registry = Mage::getModel('giftr/registry');
        if ($parent->getData('registry')) {
            return $parent->getData('registry');
        } elseif ($parent->getData('registry_id')) {
            $registry = $registry->load($parent->getData('registry_id'));
        }
        $parent->setData('registry', $registry);

        return $registry;
    }

    /**
     * Retrieve item collection associated with customer's gift registry
     *
     * @param $parent
     * @param $args
     *
     * @return Varien_Data_Collection
     */
    public function getRegistryItems($parent, $args)
    {
        $itemCollection = new Varien_Data_Collection();
        if ($parent->getData('registry_items')) {
            return $parent->getData('registry_items');
        } else {
            $registry = $this->getRegistry($parent, $args);
            $itemCollection = Mage::getResourceModel('giftr/item_collection')
                ->addRegistryFilter($registry);
            $parent->setData('registry_items', $itemCollection);
        }

        return $itemCollection;
    }
}
