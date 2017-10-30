<?php

class Ado_Api_Model_Resource_Slide extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the easybanner_id refers to the key field in your database table.
        $this->_init('mapi/slide', 'slide_id');
    }

    /**
     * Load an object using 'identifier' field if there's no field specified and value is not numeric
     *
     * @param Mage_Core_Model_Abstract $object
     * @param mixed $value
     * @param string $field
     * @return Mage_Cms_Model_Resource_Page
     */
    public function load(Mage_Core_Model_Abstract $object, $value, $field = null)
    {
        if (!is_numeric($value) && is_null($field)) {
            $field = 'identifier';
        }

        return parent::load($object, $value, $field);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param Mage_Cms_Model_Page $object
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $select->where('status = ?', 1)
            ->where('active_from','>=',date('Y-m-d'))
            ->where('active_to','<',date('Y-m-d',strtotime('+1 day')))
            ->order('update_time DESC')
            ->limit(1);
        ;
        return $select;
    }

    /**
     * Retrieve load select with filter by identifier, store and activity
     *
     * @param string $identifier
     * @param int|array $store
     * @param int $isActive
     * @return Varien_Db_Select
     */
    protected function _getLoadByIdentifierSelect($identifier, $isActive = null)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(array('cp' => $this->getMainTable()))
            ->where('cp.identifier = ?', $identifier);

        if (!is_null($isActive)) {
            $select->where('cp.status = ?', $isActive);
        }
        return $select;
    }

}