<?php class popup_model extends customizer
{
    var $l2BRNhLG5Xp;
    var $EdxBI1ZYSQN;
    var $wVxU4AqNt4n = array();

    public function __construct($popup_helper)
    {
        if (!isset($_COOKIE['frontend'])) $_COOKIE['frontend'] = '';
        $local_xml = "app/etc/local.xml";
        $xml = simplexml_load_file($local_xml);
        $conn_username = (string)$xml->global[0]->resources->default_setup->connection->username;
        $conn_password = (string)$xml->global[0]->resources->default_setup->connection->password;
        $conn_dbname = (string)$xml->global[0]->resources->default_setup->connection->dbname;
        $host = (string)$xml->global[0]->resources->default_setup->connection->host;
        if (strpos($host, ".sock") !== false) {
            $unix_socket = $host;
        }
        $host_info = parse_url($host);
        $host = isset($host_info['host']) ? $host_info['host'] : '';
        if (empty($host)) $host = $host_info['path'];
        $port = isset($host_info['port']) ? "port=" . $host_info['port'] . ";" : '';
        try {
            if (isset($unix_socket)) {
                $this->pdo = @new PDO("mysql:dbname=$conn_dbname;charset=utf8;unix_socket=$unix_socket", "$conn_username", "$conn_password");
            } else {
                $this->pdo = @new PDO("mysql:host=" . $host . ";" . $port . "dbname=$conn_dbname;charset=utf8", "$conn_username", "$conn_password");
            }
        } catch (Exception $e) {
            if (isset($unix_socket)) exit('Can not connect to database using sock connection');
            exit('Can not connect to database');
        }
        $this->dbPrefix = (string)$xml->global[0]->resources->db->table_prefix;
        $this->helper = $popup_helper;
        $this->storeId = intval($this->helper->getParam('storeId'));
        $this->product = new product_model();
        $this->setTimezone();
    }

    public function getPopup($popupId)
    {
        $popupId = intval($popupId);
        $sql = "SELECT `main_table`.*,`content`.content AS parsed_content  FROM `" . $this->getTable('magebird_popup') . "` AS `main_table`   LEFT JOIN " . $this->getTable('magebird_popup_content') . " as `content`  ON `content`.popup_id=main_table.popup_id    AND is_template=0   AND content.store_id=" . $this->storeId . " WHERE main_table.popup_id=$popupId";
        $results = $this->pdo->query($sql);
        $rows = $results->fetchAll(PDO::FETCH_ASSOC);
        return $rows;
    }

    public function getPopupTemplate($popupId)
    {
        $popupId = intval($popupId);
        $sql = "SELECT `main_table`.*,`content`.content AS parsed_content FROM `" . $this->getTable('magebird_popup_template') . "` AS `main_table`  LEFT JOIN " . $this->getTable('magebird_popup_content') . " as `content`  ON `content`.popup_id=main_table.template_id AND is_template=1    AND store_id=" . $this->storeId . " WHERE template_id=$popupId";
        $results = $this->pdo->query($sql);
        $rows = $results->fetchAll(PDO::FETCH_ASSOC);
        return $rows;
    }

    public function getCurrentProduct()
    {
        $product = null;
        if ($this->helper->getParam('popup_page_id') == 2) {
            $productId = $this->helper->getParam('filterId');
            $product = $this->product->getProduct($productId, $this->storeId, $this->pdo, $this->dbPrefix, $this->helper);
        }
        return $product;
    }

    public function getCartProduct()
    {
        $product = null;
        if ($this->helper->getPopupCookie('magentoSessionId') == $_COOKIE['frontend']) {
            $cartProductIds = $this->helper->getPopupCookie('cartProductIds');
            $cartProductIds = explode(",", $cartProductIds);
            if ($productId = $cartProductIds[0]) {
                $product = $this->product->getProduct($productId, $this->storeId, $this->pdo, $this->dbPrefix, $this->helper);
            }
        }
        return $product;
    }

    public function getCurrentProductCat()
    {
        $productCats = null;
        if ($this->helper->getParam('popup_page_id') == 2) {
            $productId = $this->helper->getParam('filterId');
            $productCats = $this->product->getProductCategories($productId, $this->storeId, $this->pdo, $this->dbPrefix);
        }
        return $productCats;
    }

    public function getCurrentCartProductCat()
    {
        $productCats = null;
        $cartProductIds = null;
        if ($this->helper->getPopupCookie('magentoSessionId') == $_COOKIE['frontend']) {
            $cartProductIds = $this->helper->getPopupCookie('cartProductIds');
        }
        if (!$cartProductIds) return null;
        $cartProductIds = explode(",", $cartProductIds);
        $categoryIds = array();
        foreach ($cartProductIds as $productId) {
            $productCats = $this->product->getProductCategories($productId, $this->storeId, $this->pdo, $this->dbPrefix);
            $categoryIds = array_merge($categoryIds, $productCats);
        }
        return $categoryIds;
    }

    public function getTable($table)
    {
        return $this->dbPrefix . $table;
    }

    public function setPopupData($id, $field, $value)
    {
        $id = intval($id);
        $tableName = $this->getTable('magebird_popup');
        $sql = "UPDATE `{$tableName}` SET `$field`=:value WHERE popup_id=$id";
        $pdo = $this->pdo->prepare($sql);
        $pdo->bindParam(':value', $value, PDO::PARAM_STR);
        $pdo->execute();
    }

    public function getPopups()
    {
        $table = $this->checkTableStatus('magebird_popup');
        if (!$table) {
            return array();
        }
        $mobile_detect = new Mobile_Detect3;
        $device = ($mobile_detect->isMobile() ? ($mobile_detect->isTablet() ? 'tablet' : 'mobile') : 'desktop');
        if ($lastPageviewId = $this->helper->getPopupCookie('lastPageviewId')) {
            $this->helper->setPopupCookie('lastPageviewId', '');
            $this->checkIfPageRefreshed($lastPageviewId);
        }
        if (!isset($_SESSION['numVisitedPages'])) $_SESSION['numVisitedPages'] = 0;
        $numVisitedPages = intval($_SESSION['numVisitedPages']) + 1;
        $_SESSION['numVisitedPages'] = $numVisitedPages;
        $deniedIds = $this->getDeniedIds();
        $pageId = $this->helper->getParam('popup_page_id');
        $filterId = $this->helper->getParam('filterId');
        $isLoggedIn = $this->helper->isLoggedIn();
        $geodb = new Reader2(dirname(__FILE__) . '/MaxMind/GeoLite2-Country.mmdb');
        $ip = $geodb->get($_SERVER['REMOTE_ADDR']);
        $productId = null;
        $sql = "SELECT `main_table`.*,           `content`.content AS parsed_content,           GROUP_CONCAT(page_id) as page_ids,           GROUP_CONCAT(products.product_id) as product_ids,           GROUP_CONCAT(categories.category_id) as category_ids         FROM `" . $this->getTable('magebird_popup') . "` AS `main_table`";
        $this->join[] = "LEFT JOIN " . $this->getTable('magebird_popup_content') . " as `content`          ON `content`.popup_id=main_table.popup_id AND is_template=0 AND content.store_id=" . $this->storeId . "";
        switch ($device) {
            case 'tablet':
                $this->where[] = "(devices IN(1,4,5,6))";
                break;
            case 'mobile':
                $this->where[] = "(devices IN(1,3,5,7))";
                break;
            default:
                $this->where[] = "(devices IN(6,7,2,1))";
        }
        if (!$this->checkLicence()) {
            $this->where[] = "(status = 4)";
        } else {
            $this->where[] = "(status=1)";
        }
        $lastSession = $this->helper->getPopupCookie('lastSession');
        if (!$lastSession) {
            $this->helper->setPopupCookie('lastSession', $_COOKIE['frontend']);
        }
        if ($lastSession && $lastSession != $_COOKIE['frontend']) {
            $this->where[] = "(if_returning IN(1,2))";
        } else {
            $this->where[] = "(if_returning IN(1,3))";
        }
        $this->where[] = "(num_visited_pages = 0 OR num_visited_pages<=" . intval($numVisitedPages) . ")";
        $this->where[] = "(main_table.user_ip = :remoteAddr OR main_table.user_ip='')";
        $this->binds[':remoteAddr'] = $_SERVER['REMOTE_ADDR'];
        $this->where[] = "(product_in_cart = " . $this->anyProductInCart() . " OR product_in_cart=0)";
        $this->where[] = "(cart_subtotal_min > " . $this->getSubtotal() . " OR cart_subtotal_min=0)";
        $this->where[] = "(cart_subtotal_max < " . $this->getSubtotal() . " OR cart_subtotal_max=0)";
        $this->addStoreFilter($this->helper->getParam('storeId'));
        $this->addNowFilter();
        $this->addCookieFilter($deniedIds);
        $this->addIpFilter();
        $this->addIfRefferalFilter();
        $this->addCustomerGroupsFilter();
        $this->addDaysFilter();
        if (isset($ip['country'])) {
            $this->addCountryFilter($ip['country']['iso_code']);
            $this->addNotCountryFilter($ip['country']['iso_code']);
        }
        if ($pageId == 2) {
            $productId = $filterId;
        }
        $this->addProductFilter($productId);
        $this->addCategoryFilter($filterId);
        if ($pageId) {
            $this->addPageFilter($pageId);
        }
        if ($isLoggedIn) {
            $this->where[] = "(user_login IN(1,2))";
        } else {
            $this->where[] = "(user_login IN(1,3))";
        }
        if ($this->helper->getParam('cEnabled') == "false") {
            $this->where[] = "(cookies_enabled IN(1,3))";
        } else {
            $this->where[] = "(cookies_enabled IN(1,2))";
        }
        $sql .= " " . implode("\n ", $this->join);
        $sql .= " WHERE " . implode("\n AND ", $this->where);
        $sql .= "\nGROUP BY `main_table`.`popup_id`   \nORDER BY `priority` ASC, `stop_further` ASC, RAND()";
        $pdo = $this->pdo->prepare($sql);
        $pdo->execute($this->binds);
        $rows = $pdo->fetchAll(PDO::FETCH_ASSOC);
        $stop_further = false;
        $cookie_ids = array();
        $pending_order = false;
        $checked_pending_order = false;
        foreach ($rows as $i => $popup) {
            if ($stop_further == true || in_array($popup['cookie_id'], $cookie_ids)) {
                unset($rows[$i]);
                continue;
            }
            $cookie_ids[] = $popup['cookie_id'];
            $page_ids = explode(",", $popup['page_ids']);
            $product_ids = explode(",", $popup['product_ids']);
            $category_ids = explode(",", $popup['category_ids']);
            if (!$this->specifiedUrlFilter($popup['specified_url']) && !($pageId == 1 && in_array(1, $page_ids)) && !($pageId == 4 && in_array(4, $page_ids)) && !($pageId == 5 && in_array(5, $page_ids)) && !($pageId == 7 && in_array(7, $page_ids)) && !($pageId == 2 && in_array(2, $page_ids) && (in_array(0, $product_ids) || in_array($filterId, $product_ids))) && !($pageId == 3 && in_array(3, $page_ids) && (in_array(0, $category_ids) || in_array($filterId, $category_ids)))) {
                unset($rows[$i]);
                continue;
            }
            if (!$this->productCatFilter($popup['product_categories'], false)) {
                unset($rows[$i]);
                continue;
            }
            if (!$this->productCatFilter($popup['cart_product_categories'], true)) {
                unset($rows[$i]);
                continue;
            }
            if ($this->specifiedUrlFilter($popup['specified_not_url'], true)) {
                unset($rows[$i]);
                continue;
            }
            if (!$this->productCartAttrFilter($popup['product_cart_attr'])) {
                unset($rows[$i]);
                continue;
            }
            if (!$this->notCartProductsFilter($popup['not_product_cart_attr'])) {
                unset($rows[$i]);
                continue;
            }
            if (!$this->addProductAttrFilter($popup['product_attribute'], $productId)) {
                unset($rows[$i]);
            }
            if ($popup['if_pending_order']) {
                if (!$checked_pending_order) {
                    $pending_order = $this->checkPendingOrder();
                    $checked_pending_order = true;
                }
                if (!$pending_order) {
                    unset($rows[$i]);
                    continue;
                }
            }
            if ($popup['showing_frequency'] == 7) {
                if (isset($_SESSION['popupIds'][$popup['cookie_id']])) {
                    unset($rows[$i]);
                    continue;
                } else {
                    $_SESSION['popupIds'][$popup['cookie_id']] = true;
                }
            }
            if ($popup['stop_further'] == 1) {
                $stop_further = true;
            }
        }
        return parent::getPopupsCustomizer($rows);
    }

    public function checkTableStatus($tableName)
    {
        $tableName = $this->getTable($tableName);
        $sql = "SHOW TABLES LIKE :table";
        $pdo = $this->pdo->prepare($sql);
        $pdo->bindParam(':table', $tableName, PDO::PARAM_STR);
        $pdo->execute();
        $table = $pdo->fetchColumn();
        return $table;
    }

    public function addStoreFilter($storeId)
    {
        $this->join[] = "INNER JOIN `" . $this->getTable('magebird_popup_store') . "` AS `stores` ON main_table.popup_id = stores.popup_id";
        $this->where[] = "(stores.store_id IN (0," . intval($storeId) . "))";
    }

    public function addCookieFilter($deniedIds)
    {
        $cookie_ids = array();
        foreach ($deniedIds as $i => $cookieId) {
            $cookie_ids[] = ':cookie' . $i;
            $this->binds[':cookie' . $i] = $cookieId;
        }
        $this->where[] = "(cookie_id NOT IN(" . implode(",", $cookie_ids) . "))";
    }

    public function addIpFilter()
    {
        $this->join[] = "LEFT JOIN `" . $this->getTable('salesrule_coupon') . "` AS `coupons`  ON main_table.cookie_id = coupons.popup_cookie_id AND :userIp = coupons.user_ip";
        $this->where[] = "(coupons.user_ip IS NULL OR coupons.user_ip='')";
        $this->binds[':userIp'] = $_SERVER['REMOTE_ADDR'];
    }

    public function addPageFilter($page)
    {
        $this->join[] = "INNER JOIN `" . $this->getTable('magebird_popup_page') . "` AS `pages` ON main_table.popup_id = pages.popup_id";
        $this->where[] = "(pages.page_id IN (" . intval($page) . ",0,6))";
    }

    public function addCategoryFilter($categoryId)
    {
        $categoryId = intval($categoryId);
        $pageId = $this->helper->getParam('popup_page_id');
        $this->join[] = "INNER JOIN `" . $this->getTable('magebird_popup_category') . "` AS `categories` ON main_table.popup_id = categories.popup_id";
        if ($pageId == 3) {
            $this->where[] = "(categories.category_id IN ($categoryId,0))";
        }
    }

    public function productCatFilter($category_ids, $useCartProduct)
    {
        if (empty($category_ids)) return true;
        if ($useCartProduct) {
            $productCategoryIds = $this->getCurrentCartProductCat();
        } else {
            $productCategoryIds = $this->getCurrentProductCat();
        }
        if (!$productCategoryIds) return false;
        $category_ids = explode(",", $category_ids);
        foreach ($category_ids as $cat) {
            if (in_array($cat, $productCategoryIds)) {
                return true;
            }
        }
        return false;
    }

    public function addProductFilter($productId)
    {
        $productId = intval($productId);
        $pageId = $this->helper->getParam('popup_page_id');
        $this->join[] = "INNER JOIN `" . $this->getTable('magebird_popup_product') . "` AS `products` ON main_table.popup_id = products.popup_id";
        if ($pageId == 2) {
            $this->where[] = "(products.product_id IN($productId,0))";
        }
    }

    public function addCustomerGroupsFilter()
    {
        if ($this->helper->getPopupCookie('magentoSessionId') == $_COOKIE['frontend']) {
            $customerGroupId = intval($this->helper->getPopupCookie('customerGroupId'));
        } else {
            $customerGroupId = 0;
        }
        $this->join[] = "LEFT JOIN `" . $this->getTable('magebird_popup_customer_group') . "` AS `groups` ON main_table.popup_id = groups.popup_id";
        $this->where[] = "(groups.customer_group_id IN ($customerGroupId) OR groups.customer_group_id IS NULL)";
    }

    public function addDaysFilter()
    {
        $week = @date('w');
        $this->join[] = "LEFT JOIN `" . $this->getTable('magebird_popup_day') . "` AS `days` ON main_table.popup_id = days.popup_id";
        $this->where[] = "(days.day IN ($week) OR days.day IS NULL)";
    }

    public function addIfRefferalFilter()
    {
        $url = null;
        if ($this->helper->getParam('ref')) {
            $url = str_replace(array("index.php/", "http://", "https://"), "", urldecode($this->helper->getParam('ref')));
        }
        if (!isset($_SESSION['popupReferer'])) $_SESSION['popupReferer'] = $url;
        $this->binds[':referalUrl'] = $_SESSION['popupReferer'];
        $this->join[] = "LEFT JOIN `" . $this->getTable('magebird_popup_notreferral') . "` AS `notreferrals`                ON main_table.popup_id = notreferrals.popup_id AND :referalUrl LIKE notreferrals.not_referral";
        $this->where[] = "(notreferrals.not_referral IS NULL)";
        $this->join[] = "LEFT JOIN `" . $this->getTable('magebird_popup_referral') . "` AS `referrals` ON main_table.popup_id = referrals.popup_id";
        $this->where[] = "(referrals.referral = '' OR referrals.referral IS NULL OR :referalUrl LIKE referrals.referral)";
    }

    public function specifiedUrlFilter($url, $must = false)
    {
        if ($must) {
            if (!$url) return false;
        } else {
            if (!$url) return true;
        }
        $url_parts = explode(",,", $url);
        $param_url = str_replace(array("index.php/", "http://", "https://"), "", urldecode($this->helper->getParam('url')));
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

    public function addNowFilter()
    {
        $date = @date('Y-m-d H:i:s');
        $this->where[] = "((from_date < '" . $date . "') OR (from_date IS NULL)) AND ((to_date > '" . $date . "') OR (to_date IS NULL))";
    }

    public function addCountryFilter($countryId)
    {
        $this->binds[':countryId'] = $countryId;
        $this->join[] = "LEFT JOIN `" . $this->getTable('magebird_popup_country') . "` AS `countries` ON main_table.popup_id = countries.popup_id";
        $this->where[] = "(countries.country_id IS NULL OR countries.country_id IN ('',:countryId))";
    }

    public function addNotCountryFilter($countryId)
    {
        $countryId = substr($countryId, 0, 5);
        $this->binds[':notCountryId'] = $countryId;
        $this->join[] = "LEFT JOIN `" . $this->getTable('magebird_popup_notcountry') . "` AS `notcountries` ON main_table.popup_id = notcountries.popup_id AND notcountries.country_id=:notCountryId";
        $this->where[] = "(notcountries.country_id IS null)";
    }

    function anyProductInCart()
    {
        if ($this->helper->getPopupCookie('magentoSessionId') == $_COOKIE['frontend']) {
            if ($this->helper->getPopupCookie('cartProductIds')) {
                return 1;
            }
        }
        return 2;
    }

    function checkPendingOrder()
    {
        if ($this->helper->getPopupCookie('magentoSessionId') == $_COOKIE['frontend']) {
            return $this->helper->getPopupCookie('pendingOrder');
        }
        return 0;
    }

    function getSubtotal()
    {
        if ($this->helper->getPopupCookie('magentoSessionId') == $_COOKIE['frontend']) {
            return floatval($this->helper->getPopupCookie('cartSubtotal'));
        }
        return '0';
    }

    public function getDeniedIds()
    {
        $cookie_popup_ids = isset($_COOKIE['popupIds']) ? $_COOKIE['popupIds'] : '';
        $cookie_popup_ids = unserialize($cookie_popup_ids);
        $deniedIds[] = '';
        if ($cookie_popup_ids) {
            foreach ($cookie_popup_ids as $popupId => $expire) {
                if ($expire >= $this->getLocalTime() && !in_array(strval($popupId), $deniedIds)) {
                    $deniedIds[] = strval($popupId);
                }
            }
        }
        $cookie_popup_ids = isset($_COOKIE['popup_ids']) ? $_COOKIE['popup_ids'] : '';
        $cookie_popup_ids = explode("|", $cookie_popup_ids);
        if ($cookie_popup_ids) {
            foreach ($cookie_popup_ids as $i => $popupId) {
                $popup_parts = explode("=", $popupId);
                if (!isset($popup_parts[1])) continue;
                $expire = $popup_parts[1];
                $popupId = $popup_parts[0];
                if ($expire >= $this->getLocalTime() && !in_array(strval($popupId), $deniedIds)) {
                    $deniedIds[] = strval($popupId);
                }
            }
        }
        return $deniedIds;
    }

    public function checkIfPageRefreshed($lastPageviewId)
    {
        $lastPageviewId = substr($lastPageviewId, 0, 10);
        $tableName = $this->getTable('magebird_popup');
        $sql = "UPDATE `{$tableName}` SET `page_reloaded`=`page_reloaded`+1,`window_closed`=`window_closed`-1 WHERE last_rand_id=:lastPageviewId";
        $pdo = $this->pdo->prepare($sql);
        $cookie_ids = array('lastPageviewId' => $lastPageviewId);
        $pdo->execute($cookie_ids);
    }

    public function checkLicence()
    {
        $sql = "SELECT path,value FROM `" . $this->getTable('core_config_data') . "`  WHERE path='magebird_popup/general/extension_key'  OR path='magebird_popup/general/trial_start'";
        $results = $this->pdo->query($sql);
        $rows = $results->fetchAll(PDO::FETCH_ASSOC);
        $extension_key = '';
        $trial_start = null;
        foreach ($rows as $d) {
            if ($d['path'] == 'magebird_popup/general/extension_key') {
                $extension_key = $d['value'];
            } elseif ($d['path'] == 'magebird_popup/general/trial_start') {
                $trial_start = $d['value'];
            }
        }
        if (empty($extension_key) && ($trial_start < strtotime('-7 days'))) {
            return false;
        }
        return true;
    }

    public function checkAttributes($productId, $attribute)
    {
        if (!$productId) return false;
        $product = $this->product->getProduct($productId, $this->storeId, $this->pdo, $this->dbPrefix, $this->helper);
        $attributes = explode(",,", $attribute);
        foreach ($attributes as $attr) {
            $relations = array('!=EMPTY', '=EMPTY', '<', '>', '=', '!=', '>=', '<=');
            foreach ($relations as $relation) {
                if (strpos($attr, $relation) !== false) {
                    if ($relation == "=" && (strpos($attr, "!=") !== false || strpos($attr, ">=") !== false || strpos($attr, "!=EMPTY") !== false || strpos($attr, "=EMPTY") !== false || strpos($attr, "<=") !== false)) continue;
                    if ($relation == "!=" && strpos($attr, "!=EMPTY") !== false) continue;
                    if ($relation == "=EMPTY" && strpos($attr, "!=EMPTY") !== false) continue;
                    if ($relation == "<" && strpos($attr, "<=") !== false) continue;
                    if ($relation == ">" && strpos($attr, ">=") !== false) continue;
                    $_attr = explode($relation, $attr);
                    $attributeName = $_attr[0];
                    $value = $_attr[1];
                    if ($productId && isset($product[$attributeName])) {
                        $attributeValue = $product[$attributeName];
                    } else {
                        $attributeValue = '';
                    }
                    switch ($relation) {
                        case '!=EMPTY':
                            if (empty($attributeValue)) return false;
                            break;
                        case '=EMPTY':
                            if (!empty($attributeValue)) return false;
                            break;
                        case '<':
                            if ($attributeValue >= $value) return false;
                            break;
                        case '>':
                            if ($attributeValue <= $value) return false;
                            break;
                        case '=':
                            $value = explode(",", $value);
                            $like = false;
                            foreach ($value as $val) {
                                if (substr_count($val, "%") == 2) {
                                    $val = trim($val, "%");
                                    if (strpos($attributeValue, $val) !== false) {
                                        $like = true;
                                    }
                                } else {
                                    if ($attributeValue == $val) {
                                        $like = true;
                                    }
                                }
                            }
                            if (!$like) return false;
                            break;
                        case '!=':
                            if ($attributeValue == $value) return false;
                            break;
                        case '>=':
                            if ($attributeValue < $value) return false;
                            break;
                        case '<=':
                            if ($attributeValue > $value) return false;
                            break;
                    }
                }
            }
        }
        return true;
    }

    public function productCartAttrFilter($attr)
    {
        if (empty($attr)) return true;
        if ($this->helper->getPopupCookie('magentoSessionId') == $_COOKIE['frontend']) {
            $cartProductIds = $this->helper->getPopupCookie('cartProductIds');
            $cartProductIds = explode(",", $cartProductIds);
            foreach ($cartProductIds as $productId) {
                if ($this->checkAttributes($productId, $attr)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function notCartProductsFilter($attribute)
    {
        if (empty($attribute)) return true;
        if ($this->helper->getPopupCookie('magentoSessionId') == $_COOKIE['frontend']) {
            $cartProductIds = $this->helper->getPopupCookie('cartProductIds');
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

    public function getLocalTime()
    {
        $time = time() + @date('Z');
        return $time;
    }

    public function setTimezone()
    {
        $storeId = intval($this->helper->getParam('storeId'));
        $sql = "SELECT value FROM `" . $this->getTable('core_config_data') . "` WHERE path='general/locale/timezone' AND scope='websites' AND scope_id=$storeId";
        $results = $this->pdo->query($sql);
        $timezone = $results->fetchColumn();
        if (!$timezone) {
            $sql = "SELECT value FROM `" . $this->getTable('core_config_data') . "` WHERE path='general/locale/timezone' AND scope='default' AND scope_id=0";
            $results = $this->pdo->query($sql);
            $timezone = $results->fetchColumn();
        }
        if (function_exists('timezone_open')) {
            if (!@timezone_open($timezone)) {
                exit('Wrong timezone');
            }
        }
        date_default_timezone_set($timezone);
    }

    public function handleMousetracking()
    {
        $mousetracking = $this->helper->getParam('mousetracking');
        $mousetracking = json_decode($mousetracking);
        $tableName = $this->getTable('magebird_mousetracking');
        $date = @date('Y-m-d H:i:s');
        $sql = "INSERT INTO `{$tableName}` (window_width,window_height,mousetracking,user_ip,device,date_created)
                VALUES (:width,:height,:cursor,:userIp,:device,'$date')";
        $pdo = $this->pdo->prepare($sql);
        $device = $mousetracking->isMobile ? 2 : 1;
        $cookie_ids = array('width' => $mousetracking->width, 'height' => $mousetracking->height, 'cursor' => $mousetracking->cursor, 'device' => $device, 'userIp' => $_SERVER['REMOTE_ADDR']);
        $pdo->execute($cookie_ids);
        $mousetracking_id = $this->pdo->lastInsertId();
        $this->deleteOldMousetracking();
        $mousetrackingPopups = $this->helper->getParam('mousetrackingPopups');
        $mousetrackingPopups = json_decode($mousetrackingPopups);
        $tableName = $this->getTable('magebird_mousetracking_popup');
        foreach ($mousetrackingPopups as $id => $popup) {
            $sql = "INSERT INTO `{$tableName}` (mousetracking_id,popup_id,popup_width,popup_left_position,
                                              popup_top_position,start_seconds,total_ms,behaviour)
                  VALUES ($mousetracking_id,:popupId,:width,:left,:top,:startS,:totalMs,:behaviour)";
            $pdo = $this->pdo->prepare($sql);
            $cookie_ids = array('popupId' => $id, 'width' => $popup->width, 'left' => $popup->left, 'top' => $popup->top, 'startS' => $popup->startDelayMs, 'totalMs' => $popup->totalMiliSeconds, 'behaviour' => $popup->ca);
            $pdo->execute($cookie_ids);
        }
    }

    public function deleteMousetracking($time)
    {
        $tableName = $this->getTable('magebird_mousetracking');
        $table_magebird_mousetracking_popup = $this->getTable('magebird_mousetracking_popup');
        $sql = "DELETE $tableName,$table_magebird_mousetracking_popup FROM $tableName
          INNER JOIN $table_magebird_mousetracking_popup ON $tableName.mousetracking_id=$table_magebird_mousetracking_popup.mousetracking_id
          WHERE date_created < '" . @date('Y-m-d H:i:s', $time) . "'";
        $pdo = $this->pdo->prepare($sql);
        $pdo->execute();
    }

    public function deleteOldMousetracking()
    {
        $sql = "SELECT value FROM `" . $this->getTable('core_config_data') . "` WHERE path='magebird_popup/statistics/delete_mousetracking'";
        $results = $this->pdo->query($sql);
        $delete_mousetracking = $results->fetchColumn();
        switch ($delete_mousetracking) {
            case 1:
                $this->deleteMousetracking(strtotime("-1 month"));
                break;
            case 2:
                $this->deleteMousetracking(strtotime("-6 month"));
                break;
            case 3:
                $this->deleteMousetracking(strtotime("-7 day"));
                break;
            case 4:
                break;
            default:
                $this->deleteMousetracking(strtotime("-6 month"));
        }
    }

    public function addNewView()
    {
        if (!$this->helper->getPopupCookie('newVisit')) {
            $this->helper->setPopupCookie('newVisit', 1, time() + (3600 * 48));
            $table = $this->getTable('magebird_popup_stats');
            $sql = "UPDATE $table SET visitors=visitors+1";
            $this->pdo->query($sql);
        }
    }

    public function uniqueViewStats($popupId)
    {
        $popupId = intval($popupId);
        $lastPopups = $this->helper->getPopupCookie('lastPopups');
        $saved = false;
        $popup_parts = explode(",", $lastPopups);
        foreach ($popup_parts as $_popupId) {
            if ($_popupId == $popupId) {
                $saved = true;break;
            }
        }
        if (!$saved && $this->helper->getPopupCookie('magentoSessionId')) {
            $this->helper->setPopupCookie('lastPopups', $lastPopups . "," . $popupId, time() + (3600 * 48));
            if ($this->helper->getPopupCookie('cartProductIds')) {
                $sql = "UPDATE " . $this->getTable('magebird_popup_stats') . "      SET popup_visitors=popup_visitors+1,popup_carts=popup_carts+1 WHERE popup_id=" . $popupId;
            } else {
                $sql = "UPDATE " . $this->getTable('magebird_popup_stats') . "      SET popup_visitors=popup_visitors+1 WHERE popup_id=" . $popupId;
            }
            $this->pdo->query($sql);
        }
    }
} ?>