<?php

class Ado_Api_Model_Mysql4_Slideitem extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the easybanner_id refers to the key field in your database table.
        $this->_init('mapi/slideitem', 'slide_item_id');
    }
}