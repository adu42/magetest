<?php require_once(Mage::getBaseDir('lib') . '/magebird/popup/Mobile_Detect.php');
require_once(Mage::getBaseDir('lib') . '/magebird/popup/MaxMind/Db/Reader2.php');
require_once(Mage::getBaseDir('lib') . '/magebird/popup/MaxMind/Db/Reader/Decoder2.php');
require_once(Mage::getBaseDir('lib') . '/magebird/popup/MaxMind/Db/Reader/InvalidDatabaseException2.php');
require_once(Mage::getBaseDir('lib') . '/magebird/popup/MaxMind/Db/Reader/Metadata2.php');
require_once(Mage::getBaseDir('lib') . '/magebird/popup/MaxMind/Db/Reader/Util2.php');

class Magebird_Popup_Block_Popup extends Magebird_Popup_Block_PopupCustomize
{
    protected $zSUPXTmlji1 = null;

    public function getPopup()
    {
        $magebird_popup_day = Mage::getSingleton("core/resource")->getTableName('magebird_popup_day');
        $table = (boolean)Mage::getSingleton('core/resource')->getConnection('core_write')->showTableStatus(trim($magebird_popup_day, '`'));
        if (!$table) {
            Mage::log('Magebird magebird_popup_day table does not exist');
            return array();
        }
        $mobile_detect = new Mobile_Detect3;
        $device = ($mobile_detect->isMobile() ? ($mobile_detect->isTablet() ? 'tablet' : 'mobile') : 'desktop');
        if ($this->getPreviewId() && $this->getRequest()->getModuleName() == "magebird_popup") {
            $previewId = intval($this->getPreviewId());
            $collection = Mage::getModel('magebird_popup/popup')->getCollection();
            $collection->addFieldToFilter('popup_id', $previewId);
            $popup = array($collection->getLastItem());
        } elseif ($this->getTemplateId() && $this->getRequest()->getModuleName() == "magebird_popup") {
            $templateId = intval($this->getTemplateId());
            $collection = Mage::getModel('magebird_popup/template')->getCollection();
            $collection->addFieldToFilter('template_id', $templateId);
            $popup = $collection->getLastItem();
        } else {
            $collection = Mage::getModel('magebird_popup/popup')->getCollection();
            $collection->getSelect()->columns('GROUP_CONCAT(page_id) AS page_ids');
            switch ($device) {
                case 'tablet':
                    $collection->addFieldToFilter('devices', array('in' => array(1, 4, 5, 6)));
                    break;
                case 'mobile':
                    $collection->addFieldToFilter('devices', array('in' => array(1, 3, 5, 7)));
                    break;
                default:
                    $collection->addFieldToFilter('devices', array('in' => array(6, 7, 2, 1)));
            }
            if (!$this->checkLicence()) {
                $collection->addFieldToFilter('status', 4);
            }
            $cookie = Mage::getSingleton('core/cookie');
            if ($lastPageviewId = Mage::helper('magebird_popup')->getPopupCookie('lastPageviewId')) {
                Mage::helper('magebird_popup')->setPopupCookie('lastPageviewId', '');
                Mage::getModel('magebird_popup/popup')->checkIfPageRefreshed($lastPageviewId);
            }
            $lastSession = Mage::helper('magebird_popup')->getPopupCookie('lastSession');
            if (!$lastSession) {
                Mage::helper('magebird_popup')->setPopupCookie('lastSession', $cookie->get('frontend'));
            }
            if ($lastSession && $lastSession != $cookie->get('frontend')) {
                $collection->addFieldToFilter('if_returning', array('in' => array(1, 2)));
            } else {
                $collection->addFieldToFilter('if_returning', array('in' => array(1, 3)));
            }
            $numVisitedPages = intval(Mage::getSingleton('core/session')->getNumVisitedPages()) + 1;
            Mage::getSingleton('core/session')->setNumVisitedPages($numVisitedPages);
            $collection->addFieldToFilter('num_visited_pages', array(array('lteq' => $numVisitedPages), array('eq' => 0),));
            $deniedIds = $this->getDeniedIds($cookie);
            $collection->addStoreFilter(Mage::app()->getStore())->addNowFilter()->addFieldToFilter('cookie_id', array('nin' => $deniedIds))->addFieldToFilter('main_table.user_ip', array(array('like' => '%' . $_SERVER['REMOTE_ADDR'] . '%'), array('like' => ''),))->addFieldToFilter('product_in_cart', array('in' => array(0, $this->getProductInCart())))->addFieldToFilter('cart_subtotal_min', array(array('gt' => $this->getSubtotal()), array('eq' => 0),))->addFieldToFilter('cart_subtotal_max', array(array('lt' => $this->getSubtotal()), array('eq' => 0),))->addFieldToFilter('status', 1);
            $pageId = $this->getTargetPageId();
            $productId = null;
            if ($pageId == 2) {
                $productId = $this->getFilterId();
            } else {
                $productId = null;
            }
            $collection->addProductFilter($productId, $pageId);
            $collection->addCategoryFilter($this->getFilterId(), $pageId);
            $collection->addPageFilter($pageId);
            $collection->addIpFilter();
            $collection->addIfRefferalFilter();
            $collection->addCustomerGroupsFilter();
            $collection->addDaysFilter();
            $geodb = new Reader2(Mage::getBaseDir('lib') . '/magebird/popup/MaxMind/GeoLite2-Country.mmdb');
            $geo = $geodb->get($_SERVER['REMOTE_ADDR']);
            if (isset($geo['country'])) {
                $collection->addCountryFilter($geo['country']['iso_code']);
                $collection->addNotCountryFilter($geo['country']['iso_code']);
            }
            $isLoggedIn = $this->helper('customer')->isLoggedIn();
            if ($isLoggedIn) {
                $collection->addFieldToFilter('user_login', array('in' => array(1, 2)));
            } else {
                $collection->addFieldToFilter('user_login', array('in' => array(1, 3)));
            }
            if ($this->getRequest()->getParam('cEnabled') == "false") {
                $collection->addFieldToFilter('cookies_enabled', array('in' => array(1, 3)));
            } else {
                $collection->addFieldToFilter('cookies_enabled', array('in' => array(1, 2)));
            }
            $collection->getSelect()->order('priority', 'asc');
            $collection->getSelect()->order('stop_further', 'asc');
            $collection->getSelect()->order(new Zend_Db_Expr('RAND()'));
            $stop_further = false;
            $cookie_ids = array();
            $checkPendingOrder = false;
            $chkd_pending_order = false;
           // $products = $this->getProductInCart();
            foreach ($collection as $key => $popup) {
                if ($popup->getData('showing_frequency') == 7) {
                    $popupIds = Mage::getSingleton('customer/session')->getData('popupIds');
                    if (!$popupIds) $popupIds = array();
                    if (in_array($popup->getData('cookie_id'), $popupIds)) {
                        $collection->removeItemByKey($key);
                        continue;
                    } else {
                        array_push($popupIds, $popup->getData('cookie_id'));
                        Mage::getSingleton('customer/session')->setData('popupIds', $popupIds);
                    }
                }
                if ($stop_further == true || in_array($popup->getData('cookie_id'), $cookie_ids)) {
                    $collection->removeItemByKey($key);
                    continue;
                }
                $cookie_ids[] = $popup->getData('cookie_id');
                $page_ids = explode(",", $popup->getData('page_ids'));
                $product_ids = explode(",", $popup->getData('product_ids'));
                $category_ids = explode(",", $popup->getData('category_ids'));
                if (!$collection->specifiedUrlFilter($popup->getData('specified_url')) && !($pageId == 1 && in_array(1, $page_ids)) && !($pageId == 4 && in_array(4, $page_ids)) && !($pageId == 5 && in_array(5, $page_ids)) && !($pageId == 7 && in_array(7, $page_ids)) && !($pageId == 2 && in_array(2, $page_ids) && (in_array(0, $product_ids) || in_array($this->getFilterId(), $product_ids))) && !($pageId == 3 && in_array(3, $page_ids) && (in_array(0, $category_ids) || in_array($this->getFilterId(), $category_ids)))) {
                    $collection->removeItemByKey($key);
                    continue;
                }
                if ($collection->specifiedUrlFilter($popup->getData('specified_not_url'), true)) {
                    $collection->removeItemByKey($key);
                    continue;
                }
                if (!$collection->productCatFilter($popup->getData('product_categories'))) {
                    $collection->removeItemByKey($key);
                    continue;
                }
                if (!$collection->productCartAttrFilter($popup->getData('product_cart_attr'))) {
                    $collection->removeItemByKey($key);
                    continue;
                }
                if (!$collection->notCartProductsFilter($popup->getData('not_product_cart_attr'))) {
                    $collection->removeItemByKey($key);
                    continue;
                }
                if (!$collection->cartProductCatFilter($popup->getData('cart_product_categories'))) {
                    $collection->removeItemByKey($key);
                    continue;
                }
                if (!$collection->addProductAttrFilter($popup->getData('product_attribute'), $productId)) {
                    $collection->removeItemByKey($key);
                    continue;
                }
                if ($popup->getData('if_pending_order')) {
                    if (!$chkd_pending_order) {
                        $checkPendingOrder = $this->checkPendingOrder();
                        $chkd_pending_order = true;
                    }
                    if (!$checkPendingOrder) {
                        $collection->removeItemByKey($key);
                        continue;
                    }
                }
                if (($popup->getData('background_color') == 3 || $popup->getData('background_color') == 4) && $popup->getData('show_when') == 1) {
                    $this->setView($popup);
                }
                if ($popup->getData('stop_further') == 1) {
                    $stop_further = true;
                }
            }
        }
        return parent::getPopupCustomize($collection);
    }

    public function getDeniedIds($cookie)
    {
        $popupIds = $cookie->get('popupIds');
        $popupIds = unserialize($popupIds);
        $deniedIds[] = '';
        if ($popupIds) {
            foreach ($popupIds as $popupId => $popupTime) {
                if ($popupTime >= time() && !in_array(strval($popupId), $deniedIds)) {
                    $deniedIds[] = strval($popupId);
                }
            }
        }
        $popupIds = $cookie->get('popup_ids');
        $popupIds = explode("|", $popupIds);
        if ($popupIds) {
            foreach ($popupIds as $key => $popupId) {
                $_popup = explode("=", $popupId);
                if (!isset($_popup[1])) continue;
                $popupTime = $_popup[1];
                $popupId = $_popup[0];
                if ($popupTime >= time() && !in_array(strval($popupId), $deniedIds)) {
                    $deniedIds[] = strval($popupId);
                }
            }
        }
        return $deniedIds;
    }

    public function getPreviewId()
    {
        return $this->getRequest()->getParam('previewId');
    }

    public function getTemplateId()
    {
        return $this->getRequest()->getParam('templateId');
    }

    public function getSubtotal()
    {
        return Mage::helper('magebird_popup')->getBaseSubtotal();
    }

    public function getTargetPageId()
    {
        return Mage::helper('magebird_popup')->getTargetPageId();
    }

    public function setView($popup)
    {
        if (!Mage::helper('magebird_popup')->getIsCrawler()) {
            Mage::getModel('magebird_popup/popup')->setPopupData($popup->getData('popup_id'), 'views', $popup->getData('views') + 1);
            Mage::getModel('magebird_popup/popup')->uniqueViewStats($popup->getData('popup_id'));
        }
    }

    public function getFilterId()
    {
        return Mage::helper('magebird_popup')->getFilterId();
    }

    function getProductInCart()
    {
        if (Mage::helper('checkout/cart')->getItemsCount()) {
            return 1;
        }
        return 2;
    }

    function checkPendingOrder()
    {
        if (Mage::helper('magebird_popup')->getPopupCookie('magentoSessionId') == $_COOKIE['frontend']) {
            return Mage::helper('magebird_popup')->getPopupCookie('pendingOrder');
        }
        return 0;
    }

    public function getPrefixedCss($css, $prefix)
    {
        $css_array = explode('}', $css);
        foreach ($css_array as &$part) {
            $part = trim($part);
            if (empty($part)) {
                continue;
            }
          //  $hash = substr(str_shuffle("dpqzsjhiunbhfcjseepudpn"), 0, 6);
            $css_per_array = explode('{', $part);
            if (substr_count($part, "{") == 2) {
                $mediaQuery = $css_per_array[0] . "{";
                $css_per_array[0] = $css_per_array[1];
                $css_nested = true;
            }
            $css_selectors = explode(',', $css_per_array[0]);
            foreach ($css_selectors as &$subPart) {
                if (trim($subPart) == "@font-face" || strpos($subPart, ".dialog ") !== false || strpos($subPart, " .dialog") !== false || strpos($subPart, ".dialog#") !== false || strpos($subPart, ".dialog.") !== false || strpos($subPart, "dialogBg") !== false || (strpos($subPart, ".dialog") !== false && strlen($subPart) == 7)) continue;
                if (strpos($subPart, $prefix) !== false) {
                    $subPart = trim($subPart);
                } elseif (strpos($subPart, ".mbdialog") !== false) {
                    $subPart = str_replace(".mbdialog", $prefix, $subPart);
                } else {
                    $subPart = $prefix . ' ' . trim($subPart);
                }
            }
            if (substr_count($part, "{") == 2) {
                $part = $mediaQuery . "\n" . implode(', ', $css_selectors) . "{" . $css_per_array[2];
            } elseif (empty($part[0]) && $css_nested) {
                $css_nested = false;
                $part = implode(', ', $css_selectors) . "{" . $css_per_array[2] . "}\n";
            } else {
                $part = implode(', ', $css_selectors) . "{" . $css_per_array[1];
            }
        }
        $css = implode("}\n", $css_array);
        return $css;
    }

    public function getHtmlAttributeVal($petten, $html)
    {
        $re = '/' . preg_quote($petten) . '=([\'"])?((?(1).+?|[^\s>]+))(?(1)\1)/is';
        if (preg_match($re, $html, $match)) {
            return urldecode($match[2]);
        }
        return false;
    }

    public function checkLicence()
    {
        /*
        $extension_key = Mage::getStoreConfig('magebird_popup/general/extension_key');
        $config = Mage::getModel('core/config_data');
        $trial_start = $config->load('magebird_popup/general/trial_start', 'path')->getData('value');
        if (empty($extension_key) && ($trial_start < strtotime('-7 days'))) {
            return false;
        }
        */
        return true;
    }

    public function parseProdAttr($html)
    {
        $html_rubble = explode('{{productAttribute="', $html);
        unset($html_rubble[0]);
        if (count($html_rubble > 0)) {
            $product = Mage::helper('magebird_popup')->getProduct();
            if (!$product) return $html;
            foreach ($html_rubble as $attr) {
                $_attr = explode('"}}', $attr);
                $productAttribute = $_attr[0];
                $productAttributeTemplate = '{{productAttribute="' . $productAttribute . '"}}';
                if ($product->getData($productAttribute)) {
                    if ($product->getAttributeText($productAttribute)) {
                        $attributeText = $product->getAttributeText($productAttribute);
                    } else {
                        $attributeText = $product->getData($productAttribute);
                    }
                } else {
                    $attributeText = '';
                }
                if ($productAttribute == 'price') {
                    $attributeText = number_format($attributeText, 2);
                }
                $html = str_replace($productAttributeTemplate, $attributeText, $html);
            }
        }
        return $html;
    }
} ?>