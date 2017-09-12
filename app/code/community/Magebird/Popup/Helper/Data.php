<?php class Magebird_Popup_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $K6T3iG7zYYN = null;
    var $oAGdUb2hcoK;

    public function __construct()
    {
        $this->popupCookie = false;
        if (!$this->getPopupCookie('magentoSessionId') && isset($_COOKIE['frontend'])) {
            $this->setPopupCookie('magentoSessionId', $_COOKIE['frontend']);
        }
    }

    public function getIsCrawler()
    {
        $http_user_agent = $_SERVER['HTTP_USER_AGENT'];
        $deny = 'robot|spider|crawler|curl|Bloglines subscriber|Dumbot|Sosoimagespider|QihooBot|FAST-WebCrawler|Superdownloads Spiderman|LinkWalker|msnbot|ASPSeek|WebAlta Crawler|Lycos|FeedFetcher-Google|Yahoo|YoudaoBot|AdsBot-Google|Googlebot|Scooter|Gigabot|Charlotte|eStyle|AcioRobot|GeonaBot|msnbot-media|Baidu|CocoCrawler|Google|Charlotte t|Yahoo! Slurp China|Sogou web spider|YodaoBot|MSRBOT|AbachoBOT|Sogou head spider|AltaVista|IDBot|Sosospider|Yahoo! Slurp|Java VM|DotBot|LiteFinder|Yeti|Rambler|Scrubby|Baiduspider|accoona';
        $isCrawler = (preg_match("/$deny/i", $http_user_agent) > 0);
        return $isCrawler;
    }

    public function getTargetPageId()
    {
        if (Mage::app()->getRequest()->getParam('popup_page_id')) return Mage::app()->getRequest()->getParam('popup_page_id');
        $request = Mage::app()->getRequest();
        $moduleName = $request->getModuleName();
        $controllerName = $request->getControllerName();
        $actionName = $request->getActionName();
        if ($actionName == "template" || $actionName == "preview") return '';
        if ((Mage::getSingleton('cms/page')->getIdentifier() == 'home' && Mage::app()->getFrontController()->getRequest()->getRouteName() == 'cms') || Mage::getUrl('') == Mage::getUrl('*/*/*', array('_current' => true, '_use_rewrite' => true))) {
            $pageId = 1;
        } elseif (Mage::registry('current_product')) {
            $pageId = 2;
        } elseif (Mage::registry('current_category')) {
            $pageId = 3;
        } elseif ($moduleName == 'checkout' && $controllerName == 'cart' && $actionName == 'index') {
            $pageId = 5;
        } elseif ($moduleName == 'onestepcheckout' || ($moduleName == 'checkout' && $controllerName == 'onepage' && $actionName == 'index')) {
            $pageId = 4;
        } else {
            $pageId = 7;
        }
        return $pageId;
    }

    public function getFilterId()
    {
        if (Mage::app()->getRequest()->getParam('filterId')) return Mage::app()->getRequest()->getParam('filterId');
        $filterId = null;
        if (Mage::registry('current_product')) {
            $filterId = Mage::registry('current_product')->getId();
        } elseif (Mage::registry('current_category')) {
            $filterId = Mage::registry('current_category')->getId();
        }
        return $filterId;
    }

    public function getRandString()
    {
        return substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 7);
    }

    public function getTrialStart()
    {
        $config = Mage::getModel('core/config_data');
        $trial_start = $config->load('magebird_popup/general/trial_start', 'path')->getData('value');
        return $trial_start;
    }

    public function showPopup()
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

    public function getLicenceKeys()
    {
        $licenceKeys = Mage::getModel('core/config_data')->getCollection()->addFieldToFilter('path', array('like' => '%magebird_popup/licence%'))->addFieldToFilter('value', array('neq' => ''))->getColumnValues('path');
        return $licenceKeys;
    }

    public function addOnActivated($key)
    {
        $config = Mage::getModel('core/config_data');
        $configValue = $config->load('magebird_popup/licence/' . $key, 'path')->getData('value');
        if (!$configValue) return false;
        return true;
    }

    public function getWidgetData($template, $widget_id)
    {
        $widget_id = 'widget_id="' . $widget_id . '"';
        $template_slice = explode($widget_id, $template);
        $widget = end($template_slice);
        $widget = explode('}}', $widget);
        $template_widget = $widget[0];
        $petten = '/(\\w+)\s*=\\s*("[^"]*"|\'[^\']*\'|[^"\'\\s>]*)/';
        preg_match_all($petten, $template_widget, $matchs, PREG_SET_ORDER);
        $widgetData = array();
        foreach ($matchs as $match) {
            if (($match[2][0] == '"' || $match[2][0] == "'") && $match[2][0] == $match[2][strlen($match[2]) - 1]) {
                $match[2] = substr($match[2], 1, -1);
            }
            $key = strtolower($match[1]);
            $value = html_entity_decode($match[2]);
            $widgetData[$key] = $value;
        }
        return $widgetData;
    }

    function getPopupCookie($cookieKey, $checked_expire = false)
    {
        if ($this->popupCookie) {
            $cookie = $this->popupCookie;
        } else {
            $cookie = isset($_COOKIE['popupData']) ? $_COOKIE['popupData'] : '';
            $this->popupCookie = $cookie;
        }
        $cookieKey = explode($cookieKey . ":", $cookie);
        if (!isset($cookieKey[1])) {
            if ($cookieKey == 'lastSession' && isset($_COOKIE['lastPopupSession'])) {
                $value = $_COOKIE['lastPopupSession'];
            } elseif ($cookieKey == 'lastRandId' && isset($_COOKIE['lastRandId'])) {
                $value = $_COOKIE['lastRandId'];
            } else {
                return false;
            }
        } else {
            $value = explode("|", $cookieKey[1]);
            $value = $value[0];
        }
        if (!$checked_expire) {
            $value = explode("=", $value);
            if (isset($value[1])) {
                $expire = $value[1];
                if ($expire < time()) return false;
            }
            $value = $value[0];
        }
        return $value;
    }

    public function setPopupMultiCookie($cookies)
    {
        foreach ($cookies as $cookie) {
            if ($cookie['expired']) {
                $cookie['value'] .= "=" . $cookie['expired'];
            }
            if ($this->popupCookie) {
                $popupCookie = $this->popupCookie;
                $cookieName = $this->getPopupCookie($cookie['cookieName'], true);
                if (strpos($popupCookie, $cookie['cookieName']) !== false) {
                    $popupCookie = str_replace($cookie['cookieName'] . ":" . $cookieName, $cookie['cookieName'] . ":" . $cookie['value'], $popupCookie);
                } else {
                    $popupCookie .= "|" . $cookie['cookieName'] . ":" . $cookie['value'];
                }
            } else {
                $popupCookie = $cookie['cookieName'] . ":" . $cookie['value'];
            }
            $this->popupCookie = $popupCookie;
        }
        setcookie('popupData', $this->popupCookie, time() + (3600 * 24 * 365), '/');
    }

    public function setPopupCookie($cookieKey, $value, $append = false)
    {
        if ($append) {
            $value .= "=" . $append;
        }
        if ($this->popupCookie) {
            $cookie = $this->popupCookie;
            $cookieName = $this->getPopupCookie($cookieKey, true);
            if (strpos($cookie, $cookieKey) !== false) {
                $cookie = str_replace($cookieKey . ":" . $cookieName, $cookieKey . ":" . $value, $cookie);
            } else {
                $cookie .= "|" . $cookieKey . ":" . $value;
            }
        } else {
            $cookie = $cookieKey . ":" . $value;
        }
        setcookie('popupData', $cookie, time() + (3600 * 24 * 365), '/');
        $this->popupCookie = $cookie;
    }

    public function getProduct($popupProductId = null)
    {
        if (Mage::app()->getRequest()->getParam('url') && strpos(Mage::app()->getRequest()->getParam('url'), 'popupProductId') !== false) {
            $url = Mage::app()->getRequest()->getParam('url');
            $query_str = parse_url($url, PHP_URL_QUERY);
            parse_str($query_str, $query);
            $popupProductId = $query['popupProductId'];
        } elseif (!$popupProductId) {
            if ($this->getTargetPageId() == 2) {
                $popupProductId = $this->getFilterId();
            }
        }
        if (!$popupProductId) return false;
        if (isset($this->_product[$popupProductId])) return $this->_product[$popupProductId];
        $this->_product[$popupProductId] = Mage::getModel('catalog/product')->load($popupProductId);
        return $this->_product[$popupProductId];
    }

    function getBaseSubtotal()
    {
        if (Mage::getStoreConfig('tax/cart_display/subtotal') == 2) {
            $total = Mage::helper('checkout/cart')->getQuote()->getTotals();
            $base_to_quote_rate = Mage::helper('checkout/cart')->getQuote()->getData('base_to_quote_rate');
            if (!$base_to_quote_rate || $base_to_quote_rate == 0) $base_to_quote_rate = 1;
            $baseSubtotal = $total["subtotal"]->getValue() / $base_to_quote_rate;
            return round($baseSubtotal, 2);
        } else {
            return Mage::helper('checkout/cart')->getQuote()->getBaseSubtotal();
        }
    }
} ?>