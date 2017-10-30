<?php

class Ado_Api_Model_Resource_Slide_Collection extends Mage_Core_Model_Resource_Collection_Abstract
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
        $this->getSelect()->where('active_from=null or active_from < ?',$today)
            ->where('active_to=null or active_to >= ?',$today)
        ->where('status=1');
        return $this;
    }

}