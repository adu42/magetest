<?php
/**
 * Created by PhpStorm.
 * User: leo
 * Date: 6/5/15
 * Time: 3:17 PM
 */

class Ado_Api_Model_Currency extends Ado_Api_Model_Abstract {


    /**
     * @param $price
     * @return array|string
     */
    public function getCurrencyPrice($price){
        return Mage::helper('core')->currency($price, false, false);
    }



}