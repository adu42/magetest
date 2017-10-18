<?php

class Ado_Api_Model_Slide extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('mapi/slide');
    }
}