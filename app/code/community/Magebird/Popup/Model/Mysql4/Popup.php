<?php class Magebird_Popup_Model_Mysql4_Popup extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('magebird_popup/popup', 'popup_id');
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!Mage::app()->getStore()->isAdmin() || Mage::app()->getRequest()->getActionName() == 'massStatus' || Mage::app()->getRequest()->getActionName() == 'massReset') return;
       /* // by@ado
        $this->mailchimpVars($object->getData('popup_content'));
        $this->getResponseCustoms($object->getData('popup_content'));
        $this->campaignMonitorCustoms($object->getData('popup_content'));
       */
        if ($object->getFromDate()) {
            $zendDate = new Zend_Date($object->getFromDate(), Varien_Date::DATETIME_INTERNAL_FORMAT);
            $object->setFromDate($zendDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
        } else {
            $object->setFromDate(null);
        }
        if ($object->getToDate()) {
            $zendDate = new Zend_Date($object->getToDate(), Varien_Date::DATETIME_INTERNAL_FORMAT);
            $object->setToDate($zendDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
        } else {
            $object->setToDate(null);
        }
        $pages = $object->getData('pages');
        if (!$pages || in_array(6, $pages) === FALSE) {
            $object->setSpecifiedUrl('');
        } else {
            $specifiedUrl = $object->getSpecifiedUrl();
            $specifiedUrl = str_replace(array("http://", "https://", "index.php/"), '', $specifiedUrl);
            $object->setSpecifiedUrl($specifiedUrl);
        }
        $specifiedUrl = $object->getSpecifiedNotUrl();
        $specifiedUrl = str_replace(array("http://", "https://", "index.php/"), '', $specifiedUrl);
        $object->setSpecifiedNotUrl($specifiedUrl);
        $specifiedUrl = $object->getIfReferral();
        $specifiedUrl = str_replace(array("http://", "https://", "index.php/"), '', $specifiedUrl);
        $object->setIfReferral($specifiedUrl);
        $specifiedUrl = $object->getIfNotReferral();
        $specifiedUrl = str_replace(array("http://", "https://", "index.php/"), '', $specifiedUrl);
        $object->setIfNotReferral($specifiedUrl);
        if (!$pages || in_array(2, $pages) === FALSE) {
            $object->setProductIds('');
        }
        if (!$pages || in_array(3, $pages) === FALSE) {
            $object->setCategoryIds('');
        }
        $object->setCookieId(str_replace(array("|", "=", ",", ":"), "", $object->getCookieId()));
        $popupContent = $object->getPopupContent();
        if (strpos($popupContent, '<p style="text-align: center;">{{') !== false) {
            $popupContent = str_replace('<p style="text-align: center;">{{', '<div style="text-align: center;">{{', $popupContent);
            $popupContent = str_replace('}}</p>', '}}</div>', $popupContent);
            $object->setPopupContent($popupContent);
        }
        return $this;
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        if (!Mage::app()->getStore()->isAdmin() || Mage::app()->getRequest()->getActionName() == 'massStatus' || Mage::app()->getRequest()->getActionName() == 'massReset') return;
        $table = Mage::getSingleton('core/resource')->getTableName('magebird_popup_store');
        $condition = $this->_getWriteAdapter()->quoteInto('popup_id = ?', $object->getId());
        $this->_getWriteAdapter()->delete($table, $condition);
        if (!$object->getData('stores')) {
            $object->setData('stores', array(0));
        }
        foreach ((array)$object->getData('stores') as $store) {
            $data = array();
            $data['popup_id'] = $object->getId();
            $data['store_id'] = $store;
            $this->_getWriteAdapter()->insert($table, $data);
        }
        $table_magebird_popup_page = Mage::getSingleton('core/resource')->getTableName('magebird_popup_page');
        $this->_getWriteAdapter()->delete($table_magebird_popup_page, $condition);
        if (!$object->getData('pages')) {
            $object->setData('pages', $object->getData('page_id'));
        }
        $pages = $object->getData('pages');
        if (!$pages || in_array(0, $pages)) {
            $object->setData('pages', array(0));
        }
        foreach ((array)$object->getData('pages') as $page) {
            $data = array();
            $data['popup_id'] = $object->getId();
            $data['page_id'] = $page;
            $this->_getWriteAdapter()->insert($table_magebird_popup_page, $data);
        }
        $table_magebird_popup_product = Mage::getSingleton('core/resource')->getTableName('magebird_popup_product');
        $this->_getWriteAdapter()->delete($table_magebird_popup_product, $condition);
        if ($object->getData('product_ids')) {
            $product_ids = explode(",", $object->getData('product_ids'));
        }
        if (empty($product_ids)) $product_ids[] = 0;
        foreach ($product_ids as $productId) {
            $data = array();
            $data['popup_id'] = $object->getId();
            $data['product_id'] = $productId;
            $this->_getWriteAdapter()->insert($table_magebird_popup_product, $data);
        }
        $table_magebird_popup_category = Mage::getSingleton('core/resource')->getTableName('magebird_popup_category');
        $this->_getWriteAdapter()->delete($table_magebird_popup_category, $condition);
        if ($object->getData('category_ids')) {
            $category_ids = explode(",", $object->getData('category_ids'));
        }
        if (empty($category_ids)) {
            $category_ids[] = 0;
        }
        foreach ($category_ids as $categoryId) {
            $data = array();
            $data['popup_id'] = $object->getId();
            $data['category_id'] = $categoryId;
            $this->_getWriteAdapter()->insert($table_magebird_popup_category, $data);
        }
        $table_magebird_popup_customer_group = Mage::getSingleton('core/resource')->getTableName('magebird_popup_customer_group');
        $this->_getWriteAdapter()->delete($table_magebird_popup_customer_group, $condition);
        foreach ((array)$object->getData('customer_group') as $group) {
            $data = array();
            $data['popup_id'] = $object->getId();
            $data['customer_group_id'] = $group;
            $this->_getWriteAdapter()->insert($table_magebird_popup_customer_group, $data);
        }
        $table_magebird_popup_day = Mage::getSingleton('core/resource')->getTableName('magebird_popup_day');
        $this->_getWriteAdapter()->delete($table_magebird_popup_day, $condition);
        foreach ((array)$object->getData('day') as $day) {
            $data = array();
            $data['popup_id'] = $object->getId();
            $data['day'] = $day;
            $this->_getWriteAdapter()->insert($table_magebird_popup_day, $data);
        }
        $table_magebird_popup_country = Mage::getSingleton('core/resource')->getTableName('magebird_popup_country');
        $this->_getWriteAdapter()->delete($table_magebird_popup_country, $condition);
        if ($object->getData('country_ids')) {
            $country_ids = explode(",", $object->getData('country_ids'));
        }
        if (empty($country_ids)) $country_ids[] = '';
        foreach ($country_ids as $countryId) {
            $data = array();
            $data['popup_id'] = $object->getId();
            $data['country_id'] = trim($countryId);
            $this->_getWriteAdapter()->insert($table_magebird_popup_country, $data);
        }
        $table_magebird_popup_notcountry = Mage::getSingleton('core/resource')->getTableName('magebird_popup_notcountry');
        $this->_getWriteAdapter()->delete($table_magebird_popup_notcountry, $condition);
        if ($object->getData('not_country_ids')) {
            $not_country_ids = explode(",", $object->getData('not_country_ids'));
        }
        if (!empty($not_country_ids)) {
            foreach ($not_country_ids as $countryId) {
                $data = array();
                $data['popup_id'] = $object->getId();
                $data['country_id'] = trim($countryId);
                $this->_getWriteAdapter()->insert($table_magebird_popup_notcountry, $data);
            }
        }
        $table_magebird_popup_referral = Mage::getSingleton('core/resource')->getTableName('magebird_popup_referral');
        $this->_getWriteAdapter()->delete($table_magebird_popup_referral, $condition);
        $if_referral = null;
        if ($object->getData('if_referral')) {
            $if_referral = explode(",,", $object->getData('if_referral'));
        }
        if (empty($if_referral)) $if_referral[] = '';
        foreach ($if_referral as $referral) {
            $data = array();
            $data['popup_id'] = $object->getId();
            $data['referral'] = $referral;
            $this->_getWriteAdapter()->insert($table_magebird_popup_referral, $data);
        }
        $if_referral = null;
        $table_magebird_popup_notreferral = Mage::getSingleton('core/resource')->getTableName('magebird_popup_notreferral');
        $this->_getWriteAdapter()->delete($table_magebird_popup_notreferral, $condition);
        if ($object->getData('if_not_referral')) {
            $if_referral = explode(",,", $object->getData('if_not_referral'));
        }
        if (empty($if_referral)) $if_referral[] = '';
        foreach ($if_referral as $referral) {
            $data = array();
            $data['popup_id'] = $object->getId();
            $data['not_referral'] = $referral;
            $this->_getWriteAdapter()->insert($table_magebird_popup_notreferral, $data);
        }
        $table_magebird_popup_stats = Mage::getSingleton("core/resource")->getTableName('magebird_popup_stats');
        $popup_id = intval($object->getId());
        $sql = "INSERT IGNORE INTO `{$table_magebird_popup_stats}` (popup_id) VALUE ($popup_id)";
        $this->_getWriteAdapter()->query($sql);
        return parent::_afterSave($object);
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        if (!Mage::app()->getStore()->isAdmin() || Mage::app()->getRequest()->getActionName() == 'massStatus' || Mage::app()->getRequest()->getActionName() == 'massReset') return;
        $table = Mage::getSingleton('core/resource')->getTableName('magebird_popup_store');
        $select = $this->_getReadAdapter()->select()->from($table)->where('popup_id = ?', $object->getId());
        if ($items = $this->_getReadAdapter()->fetchAll($select)) {
            $store_ids = array();
            foreach ($items as $item) {
                $store_ids[] = $item['store_id'];
            }
            $object->setData('store_id', $store_ids);
        }
        $table_magebird_popup_page = Mage::getSingleton('core/resource')->getTableName('magebird_popup_page');
        $select = $this->_getReadAdapter()->select()->from($table_magebird_popup_page)->where('popup_id = ?', $object->getId());
        if ($items = $this->_getReadAdapter()->fetchAll($select)) {
            $page_ids = array();
            foreach ($items as $item) {
                $page_ids[] = $item['page_id'];
            }
            $object->setData('page_id', $page_ids);
        }
        $table_magebird_popup_product = Mage::getSingleton('core/resource')->getTableName('magebird_popup_product');
        $select = $this->_getReadAdapter()->select()->from($table_magebird_popup_product)->where('popup_id = ?', $object->getId());
        if ($items = $this->_getReadAdapter()->fetchAll($select)) {
            $product_ids = array();
            foreach ($items as $item) {
                $product_ids[] = $item['product_id'];
            }
            $product_ids = implode(",", $product_ids);
            $object->setData('product_ids', $product_ids);
        }
        $table_magebird_popup_category = Mage::getSingleton('core/resource')->getTableName('magebird_popup_category');
        $select = $this->_getReadAdapter()->select()->from($table_magebird_popup_category)->where('popup_id = ?', $object->getId());
        if ($items = $this->_getReadAdapter()->fetchAll($select)) {
            $category_ids = array();
            foreach ($items as $item) {
                $category_ids[] = $item['category_id'];
            }
            $category_ids = implode(",", $category_ids);
            $object->setData('category_ids', $category_ids);
        }
        $table_magebird_popup_customer_group = Mage::getSingleton('core/resource')->getTableName('magebird_popup_customer_group');
        $select = $this->_getReadAdapter()->select()->from($table_magebird_popup_customer_group)->where('popup_id = ?', $object->getId());
        if ($items = $this->_getReadAdapter()->fetchAll($select)) {
            $customer_group_ids = array();
            foreach ($items as $item) {
                $customer_group_ids[] = $item['customer_group_id'];
            }
            $object->setData('customer_group', $customer_group_ids);
        }
        $table_magebird_popup_day = Mage::getSingleton('core/resource')->getTableName('magebird_popup_day');
        $select = $this->_getReadAdapter()->select()->from($table_magebird_popup_day)->where('popup_id = ?', $object->getId());
        if ($items = $this->_getReadAdapter()->fetchAll($select)) {
            $days = array();
            foreach ($items as $item) {
                $days[] = $item['day'];
            }
            $object->setData('day', $days);
        }
        $table_magebird_popup_country = Mage::getSingleton('core/resource')->getTableName('magebird_popup_country');
        $select = $this->_getReadAdapter()->select()->from($table_magebird_popup_country)->where('popup_id = ?', $object->getId());
        if ($items = $this->_getReadAdapter()->fetchAll($select)) {
            $country_ids = array();
            foreach ($items as $item) {
                $country_ids[] = $item['country_id'];
            }
            $country_ids = implode(",", $country_ids);
            $object->setData('country_ids', $country_ids);
        }
        $table_magebird_popup_notcountry = Mage::getSingleton('core/resource')->getTableName('magebird_popup_notcountry');
        $select = $this->_getReadAdapter()->select()->from($table_magebird_popup_notcountry)->where('popup_id = ?', $object->getId());
        if ($items = $this->_getReadAdapter()->fetchAll($select)) {
            $country_ids = array();
            foreach ($items as $item) {
                $country_ids[] = $item['country_id'];
            }
            $country_ids = implode(",", $country_ids);
            $object->setData('not_country_ids', $country_ids);
        }
        $table_magebird_popup_referral = Mage::getSingleton('core/resource')->getTableName('magebird_popup_referral');
        $select = $this->_getReadAdapter()->select()->from($table_magebird_popup_referral)->where('popup_id = ?', $object->getId());
        if ($items = $this->_getReadAdapter()->fetchAll($select)) {
            $data = array();
            foreach ($items as $item) {
                $data[] = $item['referral'];
            }
            $if_referral = implode(",,", $data);
            $object->setData('if_referral', $if_referral);
        }
        $table_magebird_popup_notreferral = Mage::getSingleton('core/resource')->getTableName('magebird_popup_notreferral');
        $select = $this->_getReadAdapter()->select()->from($table_magebird_popup_notreferral)->where('popup_id = ?', $object->getId());
        if ($items = $this->_getReadAdapter()->fetchAll($select)) {
            $data = array();
            foreach ($items as $item) {
                $data[] = $item['not_referral'];
            }
            $if_referral = implode(",,", $data);
            $object->setData('if_not_referral', $if_referral);
        }
        return parent::_afterLoad($object);
    }

    protected function _beforeDelete(Mage_Core_Model_Abstract $object)
    {
        $table = Mage::getSingleton('core/resource')->getTableName('magebird_popup_store');
        $readAdapter = $this->_getReadAdapter();
        $readAdapter->delete($table, 'popup_id=' . $object->getId());
    }

    function mailchimpVars($popupContent)
    {return;
        $_popupContents = explode('mailchimp_list_id="', $popupContent);
        if (!isset($_popupContents[1])) return;
        require_once(Mage::getBaseDir('lib') . '/magebird/popup/MCAPI.class.php');
        $mcApi = new MCAPI(Mage::getStoreConfig('magebird_popup/services/mailchimp_key'));
        $vars = explode('"', $_popupContents[1]);
        $vars = $vars[0];
        if ($vars) {
            $resources = $mcApi->listMergeVars($vars);
            $hasCoupon = false;
            foreach ($resources as $res) {
                if ($res['tag'] == 'POPUP_COUP') {
                    $hasCoupon = true;
                    return;
                }
            }
            if (!$hasCoupon) {
                $mcApi->listMergeVarAdd($vars, 'POPUP_COUP', 'Popup Coupon Code');
            }
            if ($mcApi->errorCode) {
                Mage::throwException("Mailchimp api error " . $mcApi->errorMessage);
            }
        }
    }

    function getResponseCustoms($popupContent)
    {    return;
        $_popupContents = explode('gr_campaign_token="', $popupContent);
        if (!isset($_popupContents[1])) return;
        require_once(Mage::getBaseDir('lib') . '/magebird/popup/GetResponse/GetResponseAPI.class.php');
        $mcApi = new GetResponse(Mage::getStoreConfig('magebird_popup/services/getresponse_key'));
        $_popupContent = explode('"', $_popupContents[1]);
        $_popupContent = $_popupContent[0];
        if ($_popupContent) {
            $campaignPredefines = get_object_vars($mcApi->getCampaignPredefines($_popupContent));
            if (array_key_exists('code', $campaignPredefines)) {
                Mage::throwException("GetResponse api error: " . $campaignPredefines['message'] . " or wrong campaign token.");
            } elseif (!in_array('POPUP_COUPON', $campaignPredefines)) {
                $mcApi = $mcApi->addCampaignPredefine($_popupContent, 'POPUP_COUPON', 'Popup coupon');
            }
        }
    }

    function campaignMonitorCustoms($popupContent)
    {   return;
        $_popupContents = explode('cm_list_id="', $popupContent);
        if (!isset($_popupContents[1])) return;
        require_once(Mage::getBaseDir('lib') . '/magebird/popup/Campaignmonitor/csrest_lists.php');
        $apikey = array('api_key' => Mage::getStoreConfig('magebird_popup/services/campaignmonitor_key'));
        $_popupContent = explode('"', $_popupContents[1]);
        $_popupContent = $_popupContent[0];
        $cs_rest_Lists = new CS_REST_Lists($_popupContent, $apikey);
        $result = $cs_rest_Lists->get_custom_fields();
        if (!$result->was_successful()) {
            Mage::throwException("Campaign Monitor error: " . $result->http_status_code);
        }
        $entitys = $result->response;
        $hasCoupon = false;
        foreach ($entitys as $entity) {
            if ($entity->Key == '[POPUP_COUPON]') {
                $hasCoupon = true;
                return;
            }
        }
        if (!$hasCoupon) {
            $result = $cs_rest_Lists->create_custom_field(array('FieldName' => 'POPUP_COUPON', 'DataType' => CS_REST_CUSTOM_FIELD_TYPE_TEXT));
        }
        if (!$result->was_successful()) {
            Mage::throwException("Campaign Monitor error: " . $result->http_status_code);
        }
    }
} ?>