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



class Mirasvit_EmailDesign_Helper_Variables_Customer
{
    public function getCustomerName($parent, $args)
    {
        $name = $parent->getData('customer_name');
        $name = ucwords($name);

        if (!trim($name) && isset($args[0])) {
            $name = $args[0];
        }

        return $name;
    }

    public function getFirstname($parent, $args)
    {
        $name = $this->getCustomerName($parent, $args);

        return substr($name, 0, strpos($name, ' ') ? strpos($name, ' ') : 1000);
    }

    public function getLastname($parent, $args)
    {
        $name = $this->getCustomerName($parent, $args);

        return substr($name, strpos($name, ' ') ? strpos($name, ' ') + 1 : 0);
    }

    public function getCustomer($parent, $args)
    {
        $id = intval($parent->getData('customer_id'));
        $customer = Mage::getModel('customer/customer');
        if ($id) {
            $customer = $customer->load($id);
        }

        return $customer;
    }
}
