<?php

class Ado_Api_Model_Slideitem extends Mage_Core_Model_Abstract
{
    const BANNERITEM_MEDIA_PATH = 'slides';
    public function _construct()
    {
        parent::_construct();
        $this->_init('mapi/slideitem');
    }


}