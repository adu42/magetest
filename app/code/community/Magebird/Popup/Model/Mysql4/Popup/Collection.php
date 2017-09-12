<?php class Magebird_Popup_Model_Mysql4_Popup_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('magebird_popup/popup');
    }

    public function addStoreFilter($store, $use_admin_store = true)
    {
        $stores = array();
        /*
        $extension_key = Mage::getStoreConfig('magebird_popup/general/extension_key');
        $config = Mage::getModel('core/config_data');
        $trial_start = $config->load('magebird_popup/general/trial_start', 'path')->getData('value');
        if (empty($extension_key) && ($trial_start < strtotime('-7 days'))) {
            $stores[] = 10;
        } else {
        */
            if ($store instanceof Mage_Core_Model_Store) {
                $stores[] = $store->getId();
            } elseif ($store) {
                $stores[] = $store;
            }
            $stores[] = 0;
       // }
        if ($use_admin_store) {
            $stores[] = Mage_Core_Model_App::ADMIN_STORE_ID;
            $stores = array_unique($stores);
        }
        $table_magebird_popup_store = Mage::getSingleton('core/resource')->getTableName('magebird_popup_store');
        $this->getSelect()->join(array('stores' => $table_magebird_popup_store), 'main_table.popup_id = stores.popup_id', array())->where('stores.store_id in (?)', $stores)->group('main_table.popup_id');
        return $this;
    }

    public function addPageFilter($page)
    {
        if (Mage::app()->getRequest()->getParam('url')) {
            $url = str_replace(array("index.php/", "http://", "https://"), "", urldecode(Mage::app()->getRequest()->getParam('url')));
        } else {
            $requestUri = Mage::app()->getRequest()->getOriginalRequest()->getRequestUri();
            $url = str_replace(array("index.php/", "http://", "https://"), "", $_SERVER['HTTP_HOST'] . $requestUri);
        }
        $table_magebird_popup_page = Mage::getSingleton('core/resource')->getTableName('magebird_popup_page');
        $this->getSelect()->join(array('pages' => $table_magebird_popup_page), 'main_table.popup_id = pages.popup_id', array());
        if ($page) {
            $this->getSelect()->where("pages.page_id = " . intval($page) . " OR pages.page_id = 0 OR (pages.page_id = 6)");
        }
        return $this;
    }

    public function specifiedUrlFilter($url, $check = false)
    {
        if ($check) {
            if (!$url) return false;
        } else {
            if (!$url) return true;
        }
        $url_parts = explode(",,", $url);
        if (Mage::app()->getRequest()->getParam('url')) {
            $param_url = str_replace(array("index.php/", "http://", "https://"), "", urldecode(Mage::app()->getRequest()->getParam('url')));
        } else {
            $requestUri = Mage::app()->getRequest()->getOriginalRequest()->getRequestUri();
            $param_url = str_replace(array("index.php/", "http://", "https://"), "", $_SERVER['HTTP_HOST'] . $requestUri);
        }
        foreach ($url_parts as $url) {
            if (substr($url, -1) == "%" && substr($url, 0, 1) == "%") {
                if (strpos($param_url, trim($url, '%')) !== false) return true;
            } elseif (substr($url, 0, 1) == "%") {
                if (ltrim($url, '%') == substr($param_url, -(strlen($url) - 1))) return true;
            } elseif (substr($url, -1) == "%") {
                if (rtrim($url, '%') == substr($param_url, 0, strlen($url) - 1)) return true;
            } else {
                if ($url == $param_url) return true;
            }
        }
        return false;
    }

    public function addIfRefferalFilter()
    {
        if (Mage::app()->getRequest()->getParam('ref')) {
            $url = str_replace(array("index.php/", "http://", "https://"), "", urldecode(Mage::app()->getRequest()->getParam('ref')));
        } else {
            $ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            $url = str_replace(array("index.php/", "http://", "https://"), "", $ref);
        }
        if (!Mage::getSingleton('core/session')->getPopupReferer()) {
            Mage::getSingleton('core/session')->setPopupReferer($url);
        } else {
            $url = Mage::getSingleton('core/session')->getPopupReferer();
        }
        $table_magebird_popup_referral = Mage::getSingleton('core/resource')->getTableName('magebird_popup_referral');
        $this->getSelect()->joinLeft(array('referrals' => $table_magebird_popup_referral), 'main_table.popup_id = referrals.popup_id', array())->where("referrals.referral = '' OR referrals.referral IS NULL OR ? LIKE referrals.referral", $url);
        $table_magebird_popup_notreferral = Mage::getSingleton('core/resource')->getTableName('magebird_popup_notreferral');
        // $core_read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $this->getSelect()->joinLeft(array('notreferrals' => $table_magebird_popup_notreferral), $this->getConnection()->quoteInto("main_table.popup_id = notreferrals.popup_id AND ? LIKE notreferrals.not_referral", $url), array())->where("notreferrals.not_referral IS null");
        return $this;
    }

    public function addProductFilter($productId, $pageId)
    {
        $product_ids = array(intval($productId), 0);
        $table_magebird_popup_product = Mage::getSingleton('core/resource')->getTableName('magebird_popup_product');
        $this->getSelect()->join(array('products' => $table_magebird_popup_product), 'main_table.popup_id = products.popup_id', array());
        if ($pageId == 2) {
            $this->getSelect()->where('products.product_id in (?)', $product_ids);
        }
        return $this;
    }

    public function addIpFilter()
    {
        $table_salesrule_coupon = Mage::getSingleton('core/resource')->getTableName('salesrule_coupon');
        $this->getSelect()->joinLeft(array('coupons' => $table_salesrule_coupon), $this->getConnection()->quoteInto("main_table.cookie_id = coupons.popup_cookie_id AND ? = coupons.user_ip", $_SERVER['REMOTE_ADDR']), array())->where("coupons.user_ip IS null OR coupons.user_ip=''");
        return $this;
    }

    public function addCategoryFilter($categoryId, $pageId)
    {
        $category_ids = array(intval($categoryId), 0);
        $table_magebird_popup_category = Mage::getSingleton('core/resource')->getTableName('magebird_popup_category');
        $this->getSelect()->join(array('categories' => $table_magebird_popup_category), 'main_table.popup_id = categories.popup_id', array());
        if ($pageId == 3) {
            $this->getSelect()->where('categories.category_id in (?)', $category_ids);
        }
        return $this;
    }

    public function addCustomerGroupsFilter()
    {
        $customer_group_id = intval($this->getCustomerGroupId());
        $table_magebird_popup_customer_group = Mage::getSingleton('core/resource')->getTableName('magebird_popup_customer_group');
        $this->getSelect()->joinLeft(array('groups' => $table_magebird_popup_customer_group), 'main_table.popup_id = groups.popup_id', array())->where("groups.customer_group_id =$customer_group_id OR groups.customer_group_id IS NULL");
        return $this;
    }

    public function addDaysFilter()
    {
        $day = Mage::getSingleton('core/date')->date('w');
        $table_magebird_popup_day = Mage::getSingleton('core/resource')->getTableName('magebird_popup_day');
        $this->getSelect()->joinLeft(array('days' => $table_magebird_popup_day), 'main_table.popup_id = days.popup_id', array())->where("days.day = $day OR days.day IS NULL");
        return $this;
    }

    public function addCountryFilter($countryId)
    {
        $country_ids = array($countryId, '');
        $table_magebird_popup_country = Mage::getSingleton('core/resource')->getTableName('magebird_popup_country');
        $this->getSelect()->joinLeft(array('countries' => $table_magebird_popup_country), 'main_table.popup_id = countries.popup_id', array())->where('countries.country_id IS NULL OR countries.country_id in (?)', $country_ids);
        return $this;
    }

    public function addNotCountryFilter($countryId)
    {
        $countryId = substr($countryId, 0, 5);
        $table_magebird_popup_country = Mage::getSingleton('core/resource')->getTableName('magebird_popup_notcountry');
        $this->getSelect()->joinLeft(array('notcountries' => $table_magebird_popup_country), "main_table.popup_id = notcountries.popup_id AND notcountries.country_id='$countryId'", array())->where("notcountries.country_id IS null");
        return $this;
    }

    public function getSelectCountSql()
    {
        $countSql = parent::getSelectCountSql();
        $countSql->reset(Zend_Db_Select::GROUP);
        return $countSql;
    }

    public function getCustomerGroupId()
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer_group_id = Mage::getSingleton('customer/session')->getCustomerGroupId();
            return $customer_group_id;
        } else {
            return 0;
        }
    }

    public function addNowFilter()
    {
        $date = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));
        $condition = "((from_date < '" . $date . "') OR (from_date IS NULL)) AND ((to_date > '" . $date . "') OR (to_date IS NULL))";
        $this->getSelect()->where($condition);
        return $this;
    }

    /**
     * $and 绝对
     * @param $productId
     * @param $attribute
     * @return bool
     */
    public function checkAttributes($productId, $attribute)
    {
        $and = false;
        if (!$productId) return false;
        $product = Mage::helper('magebird_popup')->getProduct($productId);
        $attributes = explode(",,", $attribute);
        foreach ($attributes as $attr) {
            $or = false;
            if (strpos($attr, "OR ") !== false) {
                $attr = str_replace("OR ", '', $attr);
                $or = true;
            }
            $relations = array('!=EMPTY', '=EMPTY', '<', '>', '=', '!=', '>=', '<=');
            foreach ($relations as $relation) {
                if (strpos($attr, $relation) !== false) {
                    if ($relation == "=" && (strpos($attr, "!=") !== false || strpos($attr, ">=") !== false || strpos($attr, "!=EMPTY") !== false || strpos($attr, "=EMPTY") !== false || strpos($attr, "<=") !== false)) continue;
                    if ($relation == "!=" && strpos($attr, "!=EMPTY") !== false) continue;
                    if ($relation == "=EMPTY" && strpos($attr, "!=EMPTY") !== false) continue;
                    if ($relation == "<" && strpos($attr, "<=") !== false) continue;
                    if ($relation == ">" && strpos($attr, ">=") !== false) continue;
                    $_attr = explode($relation, $attr);
                    $key = $_attr[0];
                    $value = $_attr[1];
                    if ($productId && $product->getData($key)) {
                        if ($product->getAttributeText($key)) {
                            $attributeText = $product->getAttributeText($key);
                        } else {
                            $attributeText = $product->getData($key);
                        }
                    } else {
                        $attributeText = '';
                    }
                    $obscure = false;
                    switch ($relation) {
                        case '!=EMPTY':
                            if (empty($attributeText)) {
                                if (!$or) $and = true;
                                $obscure = true;
                            }
                            break;
                        case '=EMPTY':
                            if (!empty($attributeText)) {
                                if (!$or) $and = true;
                                $obscure = true;
                            }
                            break;
                        case '<':
                            if ($attributeText >= $value) {
                                if (!$or) $and = true;
                                $obscure = true;
                            }
                            break;
                        case '>':
                            if ($attributeText <= $value) {
                                if (!$or) $and = true;
                                $obscure = true;
                            }
                            break;
                        case '=':
                            $value = explode(",", $value);
                            $like = false;
                            foreach ($value as $val) {
                                if (substr_count($val, "%") == 2) {
                                    $val = trim($val, "%");
                                    if (strpos($attributeText, $val) !== false) {
                                        $like = true;
                                    }
                                } else {
                                    if ($attributeText == $val) {
                                        $like = true;
                                    }
                                }
                            }
                            if (!$like) {
                                if (!$or) $and = true;
                                $obscure = true;
                            }
                            break;
                        case '!=':
                            if ($attributeText == $value) {
                                if (!$or) $and = true;
                                $obscure = true;
                            }
                            break;
                        case '>=':
                            if ($attributeText < $value) {
                                if (!$or) $and = true;
                                $obscure = true;
                            }
                            break;
                        case '<=':
                            if ($attributeText > $value) {
                                if (!$or) $and = true;
                                $obscure = true;
                            }
                            break;
                    }
                    if ($or && !$obscure) {
                        return true;
                    }
                }
            }
        }
        if ($and) {
            return false;
        }
        return true;
    }

    public function productCartAttrFilter($attr)
    {
        if (empty($attr)) return true;
        if (Mage::helper('magebird_popup')->getPopupCookie('magentoSessionId') == $_COOKIE['frontend']) {
            $cartProductIds = Mage::helper('magebird_popup')->getPopupCookie('cartProductIds');
            $cartProductIds = explode(",", $cartProductIds);
            foreach ($cartProductIds as $productId) {
                if ($this->checkAttributes($productId, $attr)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function productCatFilter($category_ids)
    {
        if (empty($category_ids)) return true;
        $product = Mage::helper('magebird_popup')->getProduct();
        if (!$product) return false;
        $productCategoryIds = $product->getResource()->getCategoryIds($product);
        $category_ids = explode(",", $category_ids);
        foreach ($category_ids as $cat) {
            if (in_array($cat, $productCategoryIds)) {
                return true;
            }
        }
        return false;
    }

    public function cartProductCatFilter($category_ids)
    {
        if (empty($category_ids)) return true;
        if (Mage::helper('magebird_popup')->getPopupCookie('magentoSessionId') == $_COOKIE['frontend']) {
            $cartProductIds = Mage::helper('magebird_popup')->getPopupCookie('cartProductIds');
            $cartProductIds = explode(",", $cartProductIds);
            $productCategoryIds = array();
            foreach ($cartProductIds as $productId) {
                $productCats = $this->getProductCategories($productId);
                $productCategoryIds = array_merge($productCategoryIds, $productCats);
            }
            $categoryIds = explode(",", $category_ids);
            foreach ($categoryIds as $cat) {
                if (in_array($cat, $productCategoryIds)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function notCartProductsFilter($attribute)
    {
        if (empty($attribute)) return true;
        if (Mage::helper('magebird_popup')->getPopupCookie('magentoSessionId') == $_COOKIE['frontend']) {
            $cartProductIds = Mage::helper('magebird_popup')->getPopupCookie('cartProductIds');
            $cartProductIds = explode(",", $cartProductIds);
            foreach ($cartProductIds as $productId) {
                if ($this->checkAttributes($productId, $attribute)) {
                    return false;
                }
            }
        }
        return true;
    }

    public function addProductAttrFilter($attribute, $productId)
    {
        if ($attribute) {
            if (!$this->checkAttributes($productId, $attribute)) {
                return false;
            }
        }
        return true;
    }

    public function getProduct2($productId = null)
    {
        if (isset($this->_product[$productId])) return $this->_product[$productId];
        if (Mage::app()->getRequest()->getParam('url') && strpos(Mage::app()->getRequest()->getParam('url'), 'popupProductId') !== false) {
            $url = Mage::app()->getRequest()->getParam('url');
            $query_string = parse_url($url, PHP_URL_QUERY);
            parse_str($query_string, $query_param);
            $productId = $query_param['popupProductId'];
        } elseif (!$productId) {
            if ($this->getTargetPageId() == 2) {
                $productId = Mage::helper('magebird_popup')->getFilterId();
            }
        }
        if (!$productId) return false;
        $this->_product[$productId] = Mage::getModel('catalog/product')->load($productId);
        return $this->_product[$productId];
    }

    public function getProductCategories($productId)
    {
        $table_catalog_category_product = Mage::getSingleton("core/resource")->getTableName('catalog_category_product');
        $core_read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $query = "SELECT DISTINCT category_id FROM " . $table_catalog_category_product . " WHERE product_id = " . intval($productId);
        $categoryIds = $core_read->fetchCol($query);
        return $categoryIds;
    }
} ?>