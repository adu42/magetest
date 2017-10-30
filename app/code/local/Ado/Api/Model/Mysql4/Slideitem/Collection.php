<?php

class Ado_Api_Model_Mysql4_Slideitem_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('mapi/slideitem');
    }

    /**
     *
     * @param $slideId
     * @return $this
     */
    public function addSlideIdFilter($slideId){
        $this->addFieldToFilter('slide_id', $slideId);
        return $this;
    }

    /**
     * @param $form
     * @param $to
     * @return $this
     */
    public function addActiveFilter(){
        $today = date('Y-m-d H:i:s', strtotime('+1 day'));
        $this->getSelect()->where('item_active_from=null or item_active_from < ?',$today)
            ->where('item_active_to=null or item_active_to >= ?',$today)
            ->where('status=1');
        return $this;
    }

    /**
     * @param $slideItemId
     * @param bool $exclude
     * @return $this
     */
    public function addIdFilter($slideItemId, $exclude = false)
    {
        if (empty($slideItemId)) {
            $this->_setIsLoaded(true);
            return $this;
        }
        if (is_array($slideItemId)) {
            if (!empty($slideItemId)) {
                if ($exclude) {
                    $condition = array('nin' => $slideItemId);
                } else {
                    $condition = array('in' => $slideItemId);
                }
            } else {
                $condition = '';
            }
        } else {
            if ($exclude) {
                $condition = array('neq' => $slideItemId);
            } else {
                $condition = $slideItemId;
            }
        }
        $this->addFieldToFilter('slide_item_id', $condition);
        return $this;
    }
}