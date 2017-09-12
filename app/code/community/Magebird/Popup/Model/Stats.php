<?php class Magebird_Popup_Model_Stats extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('magebird_popup/stats');
    }

    public function load($id, $field= null)
    {
        return parent::load($id, $field);
    }

    function cleanOldEmails()
    {
        $core_write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $table = Mage::getSingleton("core/resource")->getTableName('magebird_popup_subscriber');
        $condition = array();
        $time = strtotime("-4 month");
        $condition[] = $core_write->quoteInto('date_created < ?', $time);
        $core_write->delete($table, $condition);
    }
} ?>