<?php

class Ado_Api_Model_Slide extends Mage_Core_Model_Abstract
{
    protected $_slideItems=null;
    public function _construct()
    {
        parent::_construct();
        $this->_init('mapi/slide');
    }

    /**
     * @param $identifier
     * @return Mage_Core_Model_Abstract
     */
    public function loadByIdentifier($identifier){
        return parent::load($identifier,'identifier');
    }

    /**
     * 获得多条slide item
     * @return mixed
     */
    public function getSlideItems(){
        if($this->_slideItems==null)
            $this->_slideItems = Mage::getModel('mapi/slideitem')->getCollection()->addActiveFilter()->addSlideIdFilter($this->getId());
        return $this->_slideItems;
    }

    /**
     * 是否有条目
     * @return int
     */
    public function hasItems(){
        return count($this->_slideItems);
    }
}