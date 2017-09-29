<?php
/**
 * @author @???
 * @copyright 2015
 * @???????��????????????????
 */

/**
 * Url rewrite request model
 *
 * @category Mage
 * @package Mage_Core
 * @author Magento Core Team <core@magentocommerce.com>
 */
class Ado_SEO_Model_System_Rewrite_Request extends Enterprise_UrlRewrite_Model_Url_Rewrite_Request
{
    const SHOPBY_ROUTING_SUFFIX = 'filter';
    //const SHOPBY_ROUTING_SUFFIX_SHOPBY = 'shopby';
    const MULTIPLE_FILTERS_DELIMITER = ',';
    const MULTIPLE_FILTERS_SIBLING_DELIMITER = '_';
    const FILTERS_WORDS_DELIMITER = '-';
    const FILTERS_TEMP_DELIMITER = '|';
    const NOFOLLOW_FILTER_PREFIX = 'n/';
    protected $_filterTags = null;
    protected $storeCodes = null;
    protected $_params = null;
    protected $_adminFrontName = null;
    protected $_targetPath = null;
    protected $_resolved = null;
    protected $_query_params = null;
    protected $_nrewriteTags = null;

    /**
     *
     */
    protected function _getRequestPath()
    {
        $resolved = $this->resolvePathInfo($this->_request);
        if ($resolved) {
            list($cata, $catafull, $ishome) = $this->getALlRequestParams($this->_request);

            if (!empty($cata)) $this->_request->setPathInfo($cata);
            if ($ishome) $this->_request->setPathInfo('/');
        }
        $requestPath = $this->_request->getPathInfo();
        $requestPath = trim($requestPath, '/');
        // $requestPath = trim($requestPath, '.');
        return $requestPath;
    }

    /**
     *
     */
    protected function _getRequestCases()
    {

        $pathInfo = $this->_request->getPathInfo();
        $requestCases = array();

        $resolved = $this->resolvePathInfo($this->_request);
        $cata = '';
        $catafull = '';
        $ishome = false;
        if ($resolved) {
            list($cata, $catafull, $ishome) = $this->getALlRequestParams($this->_request);
        }

        if ($ishome) {
            $requestCases[] = '/';
            $requestCases[] = '';
        } else {
            //======by ado end =======//
            $requestPath = trim($pathInfo, '/');
            $origSlash = (substr($pathInfo, -1) == '/') ? '/' : '';
            // If there were final slash - add nothing to less priority paths. And vice versa.
            $altSlash = $origSlash ? '' : '/';

            // Query params in request, matching "path + query" has more priority
            $queryString = $this->_getQueryString();

            if ($queryString) {
                $requestCases[] = $requestPath . $origSlash . '?' . $queryString;
                $requestCases[] = $requestPath . $altSlash . '?' . $queryString;
                if ($cata != $requestPath && !empty($cata)) {
                    if ($catafull) {
                        $requestCases[] = $catafull . '?' . $queryString;
                    } else {
                        $requestCases[] = $cata . $origSlash . '?' . $queryString;
                        $requestCases[] = $cata . $altSlash . '?' . $queryString;
                    }
                }
            }
            $requestCases[] = $requestPath . $origSlash;
            $requestCases[] = $requestPath . $altSlash;
            if ($cata != $requestPath && !empty($cata)) {
                if ($catafull) {
                    $requestCases[] = $catafull;
                }
                $requestCases[] = $cata . $origSlash;
                $requestCases[] = $cata . $altSlash;
            }
            $requestCases = array_unique($requestCases);
        }

        return $requestCases;
    }

    /**
     *
     */
    protected function _getQueryString()
    {
        if (!empty($_SERVER['QUERY_STRING'])) {
            $queryParams = array();
            parse_str($_SERVER['QUERY_STRING'], $queryParams);
            $hasChanges = false;
            foreach ($queryParams as $key => $value) {
                if (substr($key, 0, 3) === '___') {
                    unset($queryParams[$key]);
                    $hasChanges = true;
                }
            }
            if ($hasChanges) {
                return http_build_query($queryParams);
            } else {
                return $_SERVER['QUERY_STRING'];
            }
        }
        return false;
    }

    /**
     * @
     */
    private function resolvePathInfo($request)
    {
        if ($this->_resolved === null) {
            $pathInfo = $request->getPathInfo();

            //======by ado =======//
            $notNeedProcess = $this->notNeedProcessFlag($pathInfo);
           // if (!$notNeedProcess) {
               // $this->getALlRequestParams($request);
                //  list($cata,$catafull,$ishome) = $this->getALlRequestParams($request);
           // }
            $this->_resolved = !$notNeedProcess;
        }
        return $this->_resolved;

    }

    /**
     * @分类页工具栏固定的标识，遇到这些标识，就认为后面带参数
     *
     */
    public function getFilterTags()
    {
        if ($this->_filterTags == null) {
            $toolarFlags = array(
                'p',
                'order',
                'dir',
                'mode',
                'limit',
                'price',
                'tag',
                'tags',
                'page',
                'query',
                self::SHOPBY_ROUTING_SUFFIX,
                // self::SHOPBY_ROUTING_SUFFIX_SHOPBY,
            );
            $toolarFlags[] = Mage_CatalogSearch_Helper_Data::QUERY_VAR_NAME;
            $this->_filterTags = $toolarFlags;
        }
        return $this->_filterTags;
    }


    /**
     * 分割url参数
     * 拆分成 分类或控制器路由 参数对
     * step 1、指定的目标路由，不需要处理
     * step 2、如果有特殊的判断，如tags内聚的垃圾功能，返回找到的目标路由，并获取参数
     * step 3、切分正常的分类和产品，处理扩展/ html .html，参数来源有getQuery|post|layer分类左侧属性筛选/工具栏的分页排序排版，处理各类参数
     *  取消nofollow标识 /n/ 在url中存在，则robots写规则不许收录
     *
     */
    private function getALlRequestParams($request)
    {

        if ($this->_targetPath) return $this->_targetPath;
        //tag a-z
        list($catalog, $params) = $this->checkIsTags($request->getPathInfo());

        if (!empty($catalog)) {
            $request->setParams($params);

            return $this->_targetPath = array($catalog, $request->getPathInfo(), false);
        }
        // end tag a-z

        // catalog
        $suffix = Mage::getStoreConfig('catalog/seo/category_url_suffix');
        if ($suffix == 'html') $suffix = '.html';
        list($catalog, $params, $layerParams, $subStore) = $this->getUrlParams($request->getPathInfo());

        $queryParams = $this->getQueryParams($request);

        $layerParams += $request->getPost();

        $params = array_merge($params, $layerParams, $queryParams);

        $request->setParams($params);

        Mage::register('layer_params', $layerParams);
        Mage::register('query_params', $params);

        $catalog = trim($catalog, '/');
        $fullCatalog = (!empty($suffix) && !empty($catalog)) ? $catalog . $suffix : false;
        $ishome = (empty($catalog) && empty($params) && $subStore);
        return $this->_targetPath = array($catalog, $fullCatalog, $ishome);

    }

    /**
     * @获取url中的参数
     *  根据 "_-/,|/n/" 符号去分割，需要这种规则的url，装配的时候装配起来就可以在这里被解码
     */
    public function getUrlParams($urlpath)
    {
        $suffix = Mage::getStoreConfig('catalog/seo/category_url_suffix');
        if ($suffix == 'html') $suffix = '.html';
        $_urlpathArr = explode('?', $urlpath);
        $urlpath = $_urlpathArr[0];
        $urlpath = trim($urlpath, '/');

        $len = strlen($suffix);
        $len = -$len;
        if (!empty($suffix) && substr($urlpath, $len) === $suffix)
            $urlpath = substr($urlpath, 0, strlen($urlpath) - strlen($suffix));


        if (substr($urlpath, 0, 2) === self::NOFOLLOW_FILTER_PREFIX) {
            $urlpath = substr($urlpath, 2);
        }

        if (stripos($urlpath, '_order_') && stripos($urlpath, '_dir_')) {
            $subUrlPath = substr($urlpath, stripos($urlpath, 'order_') + 6, (stripos($urlpath, '_dir_') - stripos($urlpath, 'order_') - 6));
            $urlpath = str_replace($subUrlPath, '|||', $urlpath);
            $urlpath = str_replace(self::MULTIPLE_FILTERS_SIBLING_DELIMITER, '/', $urlpath);
            $urlpath = str_replace('|||', $subUrlPath, $urlpath);
        } else {
            $urlpath = str_replace(self::MULTIPLE_FILTERS_SIBLING_DELIMITER, '/', $urlpath);
        }


        $filterTags = $this->getFilterTags();
        $urlparams = $layerParams = $params = array();
        $catalog = '';
        $urlparams = explode('/', $urlpath);

        $subStore = false;

        if (!empty($urlparams)) {
            $iscat = true;
            $isFilter = false;
            $nofollow = false;
            $total = count($urlparams);

            for ($i = 0; $i < $total; $i++) {
                $urlparam = $urlparams[$i];

                if ($urlparam . '/' === self::NOFOLLOW_FILTER_PREFIX && !$nofollow) {
                    $nofollow = true;
                    continue;
                }

                if ($iscat && !in_array($urlparam, $filterTags)) {

                    if ($storeId = $this->isStoreCode($urlparam)) {

                        if (Mage::app()->getStore()->getId() != $storeId) {
                            // $this->setStoreId($storeId);
                            Mage::app()->setCurrentStore($urlparam);
                            Mage::app()->getCookie()->set(Mage_Core_Model_Store::COOKIE_NAME, $urlparam, true);
                        }
                        $subStore = true;
                        continue;
                    }

                    $catalog .= '/' . $urlparam;
                } elseif (in_array($urlparam, $filterTags)) {

                    $iscat = false;

                    if ($urlparam == self::SHOPBY_ROUTING_SUFFIX) {
                        $isFilter = true;
                        $i++;
                    } else {
                        $isFilter = false;
                    }
                    //$params[$urlparam] = isset($urlparams[$i+1])?urldecode($urlparams[$i+1]):'';
                }
                //????????????url????????????
                if ($isFilter) {
                    if (isset($urlparams[$i + 1])) {
                        $urlparam = $urlparams[$i];
                        // $urlparam=str_replace(self::MULTIPLE_FILTERS_SIBLING_DELIMITER,self::FILTERS_WORDS_DELIMITER,$urlparam);
                        $params[$urlparam] = $layerParams[$urlparam] = urldecode($urlparams[$i + 1]);
                        $i++;
                    }
                } else if (!$iscat) {

                    if (isset($urlparams[$i + 1])) {
                        $params[$urlparam] = urldecode($urlparams[$i + 1]);
                        $i++;
                    }
                }
            }
        } else {
            $this->setMyDefaultStore();
        }

        return array($catalog, $params, $layerParams, $subStore);
    }

    /**
     * 获取查询参数
     * Get query params part of url
     *
     * @param bool $escape "&" escape flag
     * @return string
     */
    public function getQueryParams($request, $returnStr = false)
    {

        if ($this->_query_params === null) {
            $params = array();
            if ($request->getQuery()) {
                $params = $request->getQuery();
                // parse_str($request->getData('query'),$params);
            } else {
                $params = $_GET;
            }
            $this->_query_params = $params;
        }
        if ($returnStr) {
            return http_build_query($params);
        }
        return $this->_query_params;
    }

    /**
     * @判断传递的参数是不是店铺代码
     */
    public function isStoreCode($storeCode)
    {
        $stores = Mage::app()->getStores(true, true);
        if ($storeCode !== '' && isset($stores[$storeCode])) {
            return $stores[$storeCode];
        }
        return false;
    }

    /**
     * 获取后台路径名
     */
    public function getAdminfrontName()
    {
        if ($this->_adminFrontName === null) {
            $this->_adminFrontName = (string)Mage::app()->getConfig()->getNode('admin/routers/adminhtml/args/frontName');
        }
        return $this->_adminFrontName;
    }

    /**
     * @切换店铺用到
     */
    public function setMyDefaultStore()
    {
        Mage::app()->getCookie()->delete(Mage_Core_Model_Store::COOKIE_NAME);
    }

    /**
     * AtoZ???????????
     */
    public function checkIsTags($uri)
    {
        $path = '';
        $params = array();
        $allcategory_without_page_Pattern = "/^\/see-all.html$/";
        if (!empty($uri)) {
            $matches = array();
            //all category
            $matches3 = array();
            preg_match($allcategory_without_page_Pattern, $uri, $matches3);
            if (is_array($matches3) && count($matches3) == 1) {
                $path = "/allcategory/Index/index";
            }
        }
        return array($path, $params);
    }

    /**
     * @直接的控制器路由，不需要拆分出参数的页面，可以放进这里
     */
    public function notNeedProcessFlag($uri)
    {
        if (empty($this->_nrewriteTags)) {
            $_tags = array();
            $tags = Mage::getStoreConfig('ado_seo/catalog/url_nrewrite_flag');
            if (!empty($tags)) {
                $tags = str_replace(array("\n", ';', '|'), ",", $tags);
                $_tags = explode(',', $tags);
                foreach ($_tags as &$_tag) {
                    $_tag = trim($_tag);
                }
            }
            $_tags[] = $this->getAdminfrontName();
            $this->_nrewriteTags = $_tags;
        }
        if (!empty($this->_nrewriteTags)) {
            foreach ($this->_nrewriteTags as $flag) {
                if (stripos($uri, $flag) !== false) return true;
            }
        }
        return false;
    }

}
