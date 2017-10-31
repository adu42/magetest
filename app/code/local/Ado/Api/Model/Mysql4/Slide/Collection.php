<?php

class Ado_Api_Model_Mysql4_Slide_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('mapi/slide');
    }

    /**
     * @param $identifier
     * @return $this
     */
    public function addIdentifierFilter($identifier){
        $this->getSelect()->where('identifier = ?',$identifier);
        return $this;
    }

    /*
     * 可以使用
     */
    public function addActiveFilter()
    {
        $today = date('Y-m-d H:i:s', strtotime('+1 day'));
        $this->getSelect()->where('ISNULL(active_from) or active_from <= ?',$today)
            ->where('ISNULL(active_to) or active_to >= ?',$today)
            ->where('status=1');
        return $this;
    }
}