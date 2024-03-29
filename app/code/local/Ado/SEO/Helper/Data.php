<?php

/**
 * Ado Ciobanu
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @package     Ado_Seo
 * @copyright   Copyright (c) 2013 Ado Ciobanu
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Ado_SEO_Helper_Data extends Mage_Core_Helper_Data
{
    protected $_layerParamKeys = array();
    public $_isMobile = null;
    /**
     * Delimiter for multiple filters
     */

    //const MULTIPLE_FILTERS_DELIMITER = ',';

    /**
     * Check if module is enabled or not
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag('ado_seo/catalog/enabled');
    }

    /**
     * Check if ajax is enabled
     *
     * @return boolean
     */
    public function isAjaxEnabled()
    {
        if (!$this->isEnabled()) {
            return false;
        }
        return Mage::getStoreConfigFlag('ado_seo/catalog/ajax_enabled');
    }

    public function getDescTemplate()
    {
        return Mage::getStoreConfig('ado_seo/catalog/desc_template');
    }

    public function getKeywordsTemplate()
    {
        return Mage::getStoreConfig('ado_seo/catalog/keyword_template');
    }

    public function getTitleTemplate()
    {
        return Mage::getStoreConfig('ado_seo/catalog/title_template');
    }

    public function getOverWriteTemplate()
    {
        return Mage::getStoreConfigFlag('ado_seo/catalog/over_template');
    }

    /**
     * Check if multipe choice filters is enabled
     *
     * @return boolean
     */
    public function isMultipleChoiceFiltersEnabled()
    {
        if (!$this->isEnabled()) {
            return false;
        }
        return Mage::getStoreConfigFlag('ado_seo/catalog/multiple_choise_filters');
    }

    /**
     * Check if price slider is enabled
     *
     * @return boolean
     */
    public function isPriceSliderEnabled()
    {
        if (!$this->isEnabled()) {
            return false;
        }
        return Mage::getStoreConfigFlag('ado_seo/catalog/price_slider');
    }

    /**
     * Retrieve price slider delay in seconds.
     *
     * @return integer
     */
    public function getPriceSliderDelay()
    {
        return Mage::getStoreConfig('ado_seo/catalog/price_slider_delay');
    }

    /**
     * Retrieve how price slider will be submitted (button or delayed auto submit)
     *
     * @return int
     */
    public function getPriceSliderSubmitType()
    {
        return (int)Mage::getStoreConfig('ado_seo/catalog/price_slider_submit_type');
    }


    /**
     * Retrieve routing suffix
     *
     * @return string
     */
    public function getRoutingSuffix()
    {
        return Ado_SEO_Model_System_Rewrite::MULTIPLE_FILTERS_SIBLING_DELIMITER . Ado_SEO_Model_System_Rewrite::SHOPBY_ROUTING_SUFFIX;
        //return '/' . Mage::getStoreConfig('ado_seo/catalog/routing_suffix');
    }

    /**
     * Getter for layered navigation params
     * If $params are provided then it overrides the ones from registry
     *
     * @param array $params
     * @return array|null
     */
    public function getCurrentLayerParams(array $params = null)
    {
        $layerParams = Mage::registry('layer_params');

        if (!is_array($layerParams)) {
            $layerParams = array();
        }

        $this->_layerParamKeys = array_keys($layerParams);

        if (!empty($params)) {
            foreach ($params as $key => $value) {
                if ($value === null) {
                    unset($layerParams[$key]);
                    // }else if($key=='cat'){
                    //    unset($layerParams[$key]);
                } else {
                    $key = str_replace(Ado_SEO_Model_System_Rewrite::MULTIPLE_FILTERS_SIBLING_DELIMITER, Ado_SEO_Model_System_Rewrite::FILTERS_WORDS_DELIMITER, $key);
                    $layerParams[$key] = $value;
                }
            }
        }

        // Sort by key - small SEO improvement
        ksort($layerParams);
        return $layerParams;
    }

    public function getFilterENVarname($requestVar)
    {
        return str_replace(Ado_SEO_Model_System_Rewrite::FILTERS_WORDS_DELIMITER, Ado_SEO_Model_System_Rewrite::MULTIPLE_FILTERS_SIBLING_DELIMITER, $requestVar);
    }

    public function getFilterDEVarname($requestVar)
    {
        return str_replace(Ado_SEO_Model_System_Rewrite::MULTIPLE_FILTERS_SIBLING_DELIMITER, Ado_SEO_Model_System_Rewrite::FILTERS_WORDS_DELIMITER, $requestVar);
    }

    /**
     * Method to get url for layered navigation
     *
     * @param array $filters array with new filter values
     * @param integer $escape to autoescape or not
     * @param boolean $noFilters to add filters to the url or not
     * @param array $q array with values to add to query string
     * @return string
     * +P limit
     */
    public function getFilterUrl(array $filters, $noFilters = false, array $q = array())
    {
        $storeId = Mage::app()->getStore()->getId();
        $query = array(
            'isLayerAjax' => null, // this needs to be removed because of ajax request
            // Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls by ado
        );

        $haveFilter = false;
        $noFollow = false;

        $queryParams = Mage::registry('query_params');
        if (is_array($queryParams)) $query = array_merge($query, $queryParams);
        $query = array_merge($query, $q);

        $newQuery = array();
        $filterKeys = array_keys($filters);
        foreach ($query as $key => $value) {
            if (!in_array($key, $filterKeys)) {
                $key = str_replace(array('amp;'), '', $key);
                $newQuery[$key] = $value;
            }
        }
        $query = $newQuery;


        if (!empty($filters)) {
            foreach ($filters as $key => $val) {
                if ($val && $key == 'cat') {
                    if (is_numeric($val)) {
                        $category = Mage::getModel('catalog/category')->setStoreId($storeId)->load($val);
                    } else {
                        $category = Mage::getModel('catalog/category')->setStoreId($storeId)->loadByAttribute('url_key', $val);
                    }
                    return $category->getUrl();
                }
            }
        }


        $suffix = Mage::getStoreConfig('catalog/seo/category_url_suffix');
        if ($suffix == 'html') $suffix = '.html';
        $params = array(
            '_current' => true,
            '_use_rewrite' => true,
            '_query' => $query,
            '_escape' => true,
        );
        $url = Mage::getUrl('*/*/*', $params);
        $url = rtrim($url, '.');
        $url = str_replace('./', '/', $url);
        //@file_put_contents(dirname(__FILE__).'/aa.txt',print_r($url,true)." url2\n",FILE_APPEND);
        $isSearch = 'catalogsearch' == Mage::app()->getRequest()->getModuleName();
        if ($isSearch) {
            $_url = Mage::getUrl('*/*/*');
            $qarr = $this->getSerchQbase($url);
            if (!empty($qarr)) {
                $url = rtrim($_url, '/') . '_' . $qarr[0] . '_' . $qarr[1];
            }
        }
        $isReview = 'review' == Mage::app()->getRequest()->getModuleName();
        if ($isReview) return $url;
        // @file_put_contents(dirname(__FILE__).'/aa.txt',print_r(Mage::app()->getRequest()->getModuleName(),true)." model\n",FILE_APPEND);

        // @file_put_contents(dirname(__FILE__).'/aa.txt',print_r($url,true)." url\n",FILE_APPEND);
        $urlPath = '';

        $urlParts = explode('?', $url);

        // print_r($query);
        $len = strlen($suffix);
        $len = -$len;
        if (!empty($suffix) && substr($urlParts[0], $len) === $suffix)
            $urlParts[0] = substr($urlParts[0], 0, strlen($urlParts[0]) - strlen($suffix));


        if (!$noFilters) {
            // $filters=array_merge($filters,$query);
            // Add filters
            $layerParams = $this->getCurrentLayerParams($filters);
            if (!empty($layerParams)) {
                $haveFilter = true;
                if (count($layerParams) >= 2) $noFollow = true;
                foreach ($layerParams as $key => $value) {
                    if (!$noFollow && stripos($value, ',') !== false) $noFollow = true;
                    //if(isset($urlPartsQueryParams[$key]))
                    //    unset($urlPartsQueryParams[$key]);
                    // Encode and replace escaped delimiter with the delimiter itself
                    $value = str_replace(urlencode(Ado_SEO_Model_System_Rewrite::MULTIPLE_FILTERS_DELIMITER), Ado_SEO_Model_System_Rewrite::MULTIPLE_FILTERS_DELIMITER, urlencode($value));
                    $urlPath .= Ado_SEO_Model_System_Rewrite::MULTIPLE_FILTERS_SIBLING_DELIMITER . "{$key}" . Ado_SEO_Model_System_Rewrite::MULTIPLE_FILTERS_SIBLING_DELIMITER . "{$value}";
                }
            }
            foreach ($query as $key => $value) {
                if (!in_array($key, $this->_layerParamKeys)) {
                    if ($value !== null) {
                        //if(isset($urlPartsQueryParams[$key]))
                        //    unset($urlPartsQueryParams[$key]);
                        // Encode and replace escaped delimiter with the delimiter itself
                        $value = str_replace(urlencode(Ado_SEO_Model_System_Rewrite::MULTIPLE_FILTERS_DELIMITER), Ado_SEO_Model_System_Rewrite::MULTIPLE_FILTERS_DELIMITER, urlencode($value));
                        $urlPath .= Ado_SEO_Model_System_Rewrite::MULTIPLE_FILTERS_SIBLING_DELIMITER . "{$key}" . Ado_SEO_Model_System_Rewrite::MULTIPLE_FILTERS_SIBLING_DELIMITER . "{$value}";
                    }
                }
            }

        }


        // Skip adding routing suffix for links with no filters
        if (empty($urlPath)) {
            return $url;
        }

        // Add the suffix to the url - fixes when comming from non suffixed pages
        // It should always be the last bits in the URL
        $urlParts[0] = rtrim($urlParts[0], '/');
        $urlParts[0] = rtrim($urlParts[0], '.');
        if ($haveFilter) {
            $urlParts[0] .= $this->getRoutingSuffix();
        }
        if ($noFollow) {
            $baseUrl = Mage::getBaseUrl();
            $urlParts[0] = substr_replace($urlParts[0], '/' . Ado_SEO_Model_System_Rewrite::NOFOLLOW_FILTER_PREFIX, stripos($urlParts[0], '/', strlen($baseUrl) - 1), 1);
            // $urlPath=Ado_SEO_Model_System_Rewrite::NOFOLLOW_FILTER_PREFIX.$urlPath;
        }
        if ($suffix == 'html') $suffix = '.html';
        $url = $urlParts[0] . $urlPath . $suffix;

        return $url;
    }

    public function getSerchQbase($url)
    {
        $url = str_replace(array('?', '&amp;', '&', '=', '_'), '/', $url);
        $arr = explode('/', $url);
        $return = array();
        for ($i = 0; $i < count($arr); $i++) {
            if ($arr[$i] == Mage_CatalogSearch_Helper_Data::QUERY_VAR_NAME) {
                $return = array($arr[$i], isset($arr[$i + 1]) ? $arr[$i + 1] : '');
                break;
            }
        }
        return $return;
    }

    /**
     * Get the url to clear all layered navigation filters
     *
     * @return string
     */
    public function getClearFiltersUrl()
    {
        return $this->getFilterUrl(array(), true);
    }

    /**
     * Get url for layered navigation pagination
     *
     * @param array $query
     * @return string
     */
    public function getPagerUrl(array $query)
    {
        return $this->getFilterUrl(array(), false, $query);
    }

    /**
     * Check if we are in the catalog search
     *
     * @return boolean
     */
    public function isCatalogSearch()
    {
        $pathInfo = $this->_getRequest()->getPathInfo();
        if (stripos($pathInfo, '/catalogsearch/result') !== false || stripos($pathInfo, '/search/') !== false) {
            return true;
        }
        return false;
    }

    /**
     * Check if a string has utf8 characters in it
     *
     * @param  string $string
     * @return boolean $bool
     */
    public function seemsUtf8($string)
    {
        for ($i = 0; $i < strlen($string); $i++) {
            if (ord($string[$i]) < 0x80) {
                continue; # 0bbbbbbb
            } elseif ((ord($string[$i]) & 0xE0) == 0xC0) {
                $n = 1; # 110bbbbb
            } elseif ((ord($string[$i]) & 0xF0) == 0xE0) {
                $n = 2; # 1110bbbb
            } elseif ((ord($string[$i]) & 0xF8) == 0xF0) {
                $n = 3; # 11110bbb
            } elseif ((ord($string[$i]) & 0xFC) == 0xF8) {
                $n = 4; # 111110bb
            } elseif ((ord($string[$i]) & 0xFE) == 0xFC) {
                $n = 5; # 1111110b
            } else {
                return false; # Does not match any model
            }
            for ($j = 0; $j < $n; $j++) { # n bytes matching 10bbbbbb follow ?
                if ((++$i == strlen($string)) || ((ord($string[$i]) & 0xC0) != 0x80)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Remove any illegal characters, accents, etc.
     *
     * @param  string $string String to unaccent
     * @return string $string  Unaccented string
     */
    public function unaccent($string)
    {
        if (!preg_match('/[\x80-\xff]/', $string)) {
            return $string;
        }

        if ($this->seemsUtf8($string)) {
            $chars = array(
                // Decompositions for Latin-1 Supplement
                chr(195) . chr(128) => 'A', chr(195) . chr(129) => 'A',
                chr(195) . chr(130) => 'A', chr(195) . chr(131) => 'A',
                chr(195) . chr(132) => 'A', chr(195) . chr(133) => 'A',
                chr(195) . chr(135) => 'C', chr(195) . chr(136) => 'E',
                chr(195) . chr(137) => 'E', chr(195) . chr(138) => 'E',
                chr(195) . chr(139) => 'E', chr(195) . chr(140) => 'I',
                chr(195) . chr(141) => 'I', chr(195) . chr(142) => 'I',
                chr(195) . chr(143) => 'I', chr(195) . chr(145) => 'N',
                chr(195) . chr(146) => 'O', chr(195) . chr(147) => 'O',
                chr(195) . chr(148) => 'O', chr(195) . chr(149) => 'O',
                chr(195) . chr(150) => 'O', chr(195) . chr(153) => 'U',
                chr(195) . chr(154) => 'U', chr(195) . chr(155) => 'U',
                chr(195) . chr(156) => 'U', chr(195) . chr(157) => 'Y',
                chr(195) . chr(159) => 's', chr(195) . chr(160) => 'a',
                chr(195) . chr(161) => 'a', chr(195) . chr(162) => 'a',
                chr(195) . chr(163) => 'a', chr(195) . chr(164) => 'a',
                chr(195) . chr(165) => 'a', chr(195) . chr(167) => 'c',
                chr(195) . chr(168) => 'e', chr(195) . chr(169) => 'e',
                chr(195) . chr(170) => 'e', chr(195) . chr(171) => 'e',
                chr(195) . chr(172) => 'i', chr(195) . chr(173) => 'i',
                chr(195) . chr(174) => 'i', chr(195) . chr(175) => 'i',
                chr(195) . chr(177) => 'n', chr(195) . chr(178) => 'o',
                chr(195) . chr(179) => 'o', chr(195) . chr(180) => 'o',
                chr(195) . chr(181) => 'o', chr(195) . chr(182) => 'o',
                chr(195) . chr(182) => 'o', chr(195) . chr(185) => 'u',
                chr(195) . chr(186) => 'u', chr(195) . chr(187) => 'u',
                chr(195) . chr(188) => 'u', chr(195) . chr(189) => 'y',
                chr(195) . chr(191) => 'y',
                // Decompositions for Latin Extended-A
                chr(196) . chr(128) => 'A', chr(196) . chr(129) => 'a',
                chr(196) . chr(130) => 'A', chr(196) . chr(131) => 'a',
                chr(196) . chr(132) => 'A', chr(196) . chr(133) => 'a',
                chr(196) . chr(134) => 'C', chr(196) . chr(135) => 'c',
                chr(196) . chr(136) => 'C', chr(196) . chr(137) => 'c',
                chr(196) . chr(138) => 'C', chr(196) . chr(139) => 'c',
                chr(196) . chr(140) => 'C', chr(196) . chr(141) => 'c',
                chr(196) . chr(142) => 'D', chr(196) . chr(143) => 'd',
                chr(196) . chr(144) => 'D', chr(196) . chr(145) => 'd',
                chr(196) . chr(146) => 'E', chr(196) . chr(147) => 'e',
                chr(196) . chr(148) => 'E', chr(196) . chr(149) => 'e',
                chr(196) . chr(150) => 'E', chr(196) . chr(151) => 'e',
                chr(196) . chr(152) => 'E', chr(196) . chr(153) => 'e',
                chr(196) . chr(154) => 'E', chr(196) . chr(155) => 'e',
                chr(196) . chr(156) => 'G', chr(196) . chr(157) => 'g',
                chr(196) . chr(158) => 'G', chr(196) . chr(159) => 'g',
                chr(196) . chr(160) => 'G', chr(196) . chr(161) => 'g',
                chr(196) . chr(162) => 'G', chr(196) . chr(163) => 'g',
                chr(196) . chr(164) => 'H', chr(196) . chr(165) => 'h',
                chr(196) . chr(166) => 'H', chr(196) . chr(167) => 'h',
                chr(196) . chr(168) => 'I', chr(196) . chr(169) => 'i',
                chr(196) . chr(170) => 'I', chr(196) . chr(171) => 'i',
                chr(196) . chr(172) => 'I', chr(196) . chr(173) => 'i',
                chr(196) . chr(174) => 'I', chr(196) . chr(175) => 'i',
                chr(196) . chr(176) => 'I', chr(196) . chr(177) => 'i',
                chr(196) . chr(178) => 'IJ', chr(196) . chr(179) => 'ij',
                chr(196) . chr(180) => 'J', chr(196) . chr(181) => 'j',
                chr(196) . chr(182) => 'K', chr(196) . chr(183) => 'k',
                chr(196) . chr(184) => 'k', chr(196) . chr(185) => 'L',
                chr(196) . chr(186) => 'l', chr(196) . chr(187) => 'L',
                chr(196) . chr(188) => 'l', chr(196) . chr(189) => 'L',
                chr(196) . chr(190) => 'l', chr(196) . chr(191) => 'L',
                chr(197) . chr(128) => 'l', chr(197) . chr(129) => 'L',
                chr(197) . chr(130) => 'l', chr(197) . chr(131) => 'N',
                chr(197) . chr(132) => 'n', chr(197) . chr(133) => 'N',
                chr(197) . chr(134) => 'n', chr(197) . chr(135) => 'N',
                chr(197) . chr(136) => 'n', chr(197) . chr(137) => 'N',
                chr(197) . chr(138) => 'n', chr(197) . chr(139) => 'N',
                chr(197) . chr(140) => 'O', chr(197) . chr(141) => 'o',
                chr(197) . chr(142) => 'O', chr(197) . chr(143) => 'o',
                chr(197) . chr(144) => 'O', chr(197) . chr(145) => 'o',
                chr(197) . chr(146) => 'OE', chr(197) . chr(147) => 'oe',
                chr(197) . chr(148) => 'R', chr(197) . chr(149) => 'r',
                chr(197) . chr(150) => 'R', chr(197) . chr(151) => 'r',
                chr(197) . chr(152) => 'R', chr(197) . chr(153) => 'r',
                chr(197) . chr(154) => 'S', chr(197) . chr(155) => 's',
                chr(197) . chr(156) => 'S', chr(197) . chr(157) => 's',
                chr(197) . chr(158) => 'S', chr(197) . chr(159) => 's',
                chr(197) . chr(160) => 'S', chr(197) . chr(161) => 's',
                chr(197) . chr(162) => 'T', chr(197) . chr(163) => 't',
                chr(197) . chr(164) => 'T', chr(197) . chr(165) => 't',
                chr(197) . chr(166) => 'T', chr(197) . chr(167) => 't',
                chr(197) . chr(168) => 'U', chr(197) . chr(169) => 'u',
                chr(197) . chr(170) => 'U', chr(197) . chr(171) => 'u',
                chr(197) . chr(172) => 'U', chr(197) . chr(173) => 'u',
                chr(197) . chr(174) => 'U', chr(197) . chr(175) => 'u',
                chr(197) . chr(176) => 'U', chr(197) . chr(177) => 'u',
                chr(197) . chr(178) => 'U', chr(197) . chr(179) => 'u',
                chr(197) . chr(180) => 'W', chr(197) . chr(181) => 'w',
                chr(197) . chr(182) => 'Y', chr(197) . chr(183) => 'y',
                chr(197) . chr(184) => 'Y', chr(197) . chr(185) => 'Z',
                chr(197) . chr(186) => 'z', chr(197) . chr(187) => 'Z',
                chr(197) . chr(188) => 'z', chr(197) . chr(189) => 'Z',
                chr(197) . chr(190) => 'z', chr(197) . chr(191) => 's',
                // Euro Sign
                chr(226) . chr(130) . chr(172) => 'E',
                // GBP (Pound) Sign
                chr(194) . chr(163) => '',
                'Ä' => 'Ae', 'ä' => 'ae', 'Ü' => 'Ue', 'ü' => 'ue',
                'Ö' => 'Oe', 'ö' => 'oe', 'ß' => 'ss',
                // Norwegian characters
                'Å' => 'Aa', 'Æ' => 'Ae', 'Ø' => 'O', 'æ' => 'a', 'ø' => 'o', 'å' => 'aa'
            );

            $string = strtr($string, $chars);
        } else {
            // Assume ISO-8859-1 if not UTF-8
            $chars['in'] = chr(128) . chr(131) . chr(138) . chr(142) . chr(154) . chr(158)
                . chr(159) . chr(162) . chr(165) . chr(181) . chr(192) . chr(193) . chr(194)
                . chr(195) . chr(196) . chr(197) . chr(199) . chr(200) . chr(201) . chr(202)
                . chr(203) . chr(204) . chr(205) . chr(206) . chr(207) . chr(209) . chr(210)
                . chr(211) . chr(212) . chr(213) . chr(214) . chr(216) . chr(217) . chr(218)
                . chr(219) . chr(220) . chr(221) . chr(224) . chr(225) . chr(226) . chr(227)
                . chr(228) . chr(229) . chr(231) . chr(232) . chr(233) . chr(234) . chr(235)
                . chr(236) . chr(237) . chr(238) . chr(239) . chr(241) . chr(242) . chr(243)
                . chr(244) . chr(245) . chr(246) . chr(248) . chr(249) . chr(250) . chr(251)
                . chr(252) . chr(253) . chr(255);

            $chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";

            $string = strtr($string, $chars['in'], $chars['out']);
            $doubleChars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
            $doubleChars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
            $string = str_replace($doubleChars['in'], $doubleChars['out'], $string);
        }
        return $string;
    }

    /**
     * US-ASCII transliterations of Unicode text
     * Warning: you should only pass this well formed UTF-8!
     * Be aware it works by making a copy of the input string which it appends transliterated
     * characters to - it uses a PHP output buffer to do this - it means, memory use will increase,
     * requiring up to the same amount again as the input string
     *
     * @param string UTF-8 string to convert
     * @param string (default = ?) Character use if character unknown
     * @return string US-ASCII string
     */
    public function utf8ToAscii($str, $unknown = '?')
    {
        static $UTF8_TO_ASCII;

        if (strlen($str) == 0) {
            return;
        }

        preg_match_all('/.{1}|[^\x00]{1,1}$/us', $str, $ar);
        $chars = $ar[0];

        foreach ($chars as $i => $c) {
            $ud = 0;
            if (ord($c{0}) >= 0 && ord($c{0}) <= 127) {
                continue;
            } // ASCII - next please
            if (ord($c{0}) >= 192 && ord($c{0}) <= 223) {
                $ord = (ord($c{0}) - 192) * 64 + (ord($c{1}) - 128);
            }
            if (ord($c{0}) >= 224 && ord($c{0}) <= 239) {
                $ord = (ord($c{0}) - 224) * 4096 + (ord($c{1}) - 128) * 64 + (ord($c{2}) - 128);
            }
            if (ord($c{0}) >= 240 && ord($c{0}) <= 247) {
                $ord = (ord($c{0}) - 240) * 262144 + (ord($c{1}) - 128) * 4096 + (ord($c{2}) - 128) * 64 + (ord($c{3}) - 128);
            }
            if (ord($c{0}) >= 248 && ord($c{0}) <= 251) {
                $ord = (ord($c{0}) - 248) * 16777216 + (ord($c{1}) - 128) * 262144 + (ord($c{2}) - 128) * 4096 + (ord($c{3}) - 128) * 64 + (ord($c{4}) - 128);
            }
            if (ord($c{0}) >= 252 && ord($c{0}) <= 253) {
                $ord = (ord($c{0}) - 252) * 1073741824 + (ord($c{1}) - 128) * 16777216 + (ord($c{2}) - 128) * 262144 + (ord($c{3}) - 128) * 4096 + (ord($c{4}) - 128) * 64 + (ord($c{5}) - 128);
            }
            if (ord($c{0}) >= 254 && ord($c{0}) <= 255) {
                $chars{$i} = $unknown;
                continue;
            } //error

            $bank = $ord >> 8;

            if (!array_key_exists($bank, (array)$UTF8_TO_ASCII)) {
                $bankfile = __DIR__ . '/data/' . sprintf("x%02x", $bank) . '.php';
                if (file_exists($bankfile)) {
                    include $bankfile;
                } else {
                    $UTF8_TO_ASCII[$bank] = array();
                }
            }

            $newchar = $ord & 255;
            if (array_key_exists($newchar, $UTF8_TO_ASCII[$bank])) {
                $chars{$i} = $UTF8_TO_ASCII[$bank][$newchar];
            } else {
                $chars{$i} = $unknown;
            }
        }
        return implode('', $chars);
    }

    /**
     * Does not transliterate correctly eastern languages
     *
     * @param string $text
     * @param string $separator
     * @return string
     */
    public function urlize($text, $separator = '-')
    {
        $text = $this->unaccent($text);
        return $this->postProcessText($text, $separator);
    }

    /**
     * Uses transliteration tables to convert any kind of utf8 character
     *
     * @param string $text
     * @param string $separator
     * @return string $text
     */
    public function transliterate($text, $separator = '-')
    {
        return $this->urlize($text, $separator);
        if (preg_match('/[\x80-\xff]/', $text) && $this->validUtf8($text)) {
            $text = $this->utf8ToAscii($text);
        }
        return $this->postProcessText($text, $separator);
    }

    /**
     * Tests a string as to whether it's valid UTF-8 and supported by the
     * Unicode standard
     *
     * @param string UTF-8 encoded string
     * @return boolean true if valid
     */
    public function validUtf8($str)
    {
        $mState = 0;     // cached expected number of octets after the current octet
        // until the beginning of the next UTF8 character sequence
        $mUcs4 = 0;     // cached Unicode character
        $mBytes = 1;     // cached expected number of octets in the current sequence

        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $in = ord($str{$i});
            if ($mState == 0) {
                // When mState is zero we expect either a US-ASCII character or a
                // multi-octet sequence.
                if (0 == (0x80 & ($in))) {
                    // US-ASCII, pass straight through.
                    $mBytes = 1;
                } elseif (0xC0 == (0xE0 & ($in))) {
                    // First octet of 2 octet sequence
                    $mUcs4 = ($in);
                    $mUcs4 = ($mUcs4 & 0x1F) << 6;
                    $mState = 1;
                    $mBytes = 2;
                } elseif (0xE0 == (0xF0 & ($in))) {
                    // First octet of 3 octet sequence
                    $mUcs4 = ($in);
                    $mUcs4 = ($mUcs4 & 0x0F) << 12;
                    $mState = 2;
                    $mBytes = 3;
                } elseif (0xF0 == (0xF8 & ($in))) {
                    // First octet of 4 octet sequence
                    $mUcs4 = ($in);
                    $mUcs4 = ($mUcs4 & 0x07) << 18;
                    $mState = 3;
                    $mBytes = 4;
                } elseif (0xF8 == (0xFC & ($in))) {
                    /* First octet of 5 octet sequence.
                     *
                     * This is illegal because the encoded codepoint must be either
                     * (a) not the shortest form or
                     * (b) outside the Unicode range of 0-0x10FFFF.
                     * Rather than trying to resynchronize, we will carry on until the end
                     * of the sequence and let the later error handling code catch it.
                     */
                    $mUcs4 = ($in);
                    $mUcs4 = ($mUcs4 & 0x03) << 24;
                    $mState = 4;
                    $mBytes = 5;
                } elseif (0xFC == (0xFE & ($in))) {
                    // First octet of 6 octet sequence, see comments for 5 octet sequence.
                    $mUcs4 = ($in);
                    $mUcs4 = ($mUcs4 & 1) << 30;
                    $mState = 5;
                    $mBytes = 6;
                } else {
                    /* Current octet is neither in the US-ASCII range nor a legal first
                     * octet of a multi-octet sequence.
                     */
                    return false;
                }
            } else {
                // When mState is non-zero, we expect a continuation of the multi-octet
                // sequence
                if (0x80 == (0xC0 & ($in))) {
                    // Legal continuation.
                    $shift = ($mState - 1) * 6;
                    $tmp = $in;
                    $tmp = ($tmp & 0x0000003F) << $shift;
                    $mUcs4 |= $tmp;
                    /**
                     * End of the multi-octet sequence. mUcs4 now contains the final
                     * Unicode codepoint to be output
                     */
                    if (0 == --$mState) {
                        /*
                         * Check for illegal sequences and codepoints.
                         */
                        // From Unicode 3.1, non-shortest form is illegal
                        if (((2 == $mBytes) && ($mUcs4 < 0x0080)) ||
                            ((3 == $mBytes) && ($mUcs4 < 0x0800)) ||
                            ((4 == $mBytes) && ($mUcs4 < 0x10000)) ||
                            (4 < $mBytes) ||
                            // From Unicode 3.2, surrogate characters are illegal
                            (($mUcs4 & 0xFFFFF800) == 0xD800) ||
                            // Codepoints outside the Unicode range are illegal
                            ($mUcs4 > 0x10FFFF)
                        ) {
                            return false;
                        }
                        //initialize UTF8 cache
                        $mState = 0;
                        $mUcs4 = 0;
                        $mBytes = 1;
                    }
                } else {
                    /**
                     * ((0xC0 & (*in) != 0x80) && (mState != 0))
                     * Incomplete multi-octet sequence.
                     */
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Cleans up the text and adds separator
     *
     * @param string $text
     * @param string $separator
     * @return string
     */
    protected function postProcessText($text, $separator)
    {
        if (function_exists('mb_strtolower')) {
            $text = mb_strtolower($text);
        } else {
            $text = strtolower($text);
        }

        // Remove all none word characters
        $text = preg_replace('/\W/', ' ', $text);

        // More stripping. Replace spaces with dashes
        $text = strtolower(preg_replace('/[^A-Z^a-z^0-9^\/]+/', $separator, preg_replace('/([a-z\d])([A-Z])/', '\1_\2', preg_replace('/([A-Z]+)([A-Z][a-z])/', '\1_\2', preg_replace('/::/', '/', $text)))));

        return trim($text, $separator);
    }

    /**
     * @conversion code
     */
    public function showConversionCode($total)
    {
        $result = '';
        $mName = Mage::app()->getRequest()->getModuleName();
        $cName = Mage::app()->getRequest()->getControllerName();
        $aName = Mage::app()->getRequest()->getActionName();

        $path = "$cName/$aName";
        $path = strtolower($path);
        $paths = array(
            'cart/index' => 1,
            'onepage/index' => 2,
            'onestepcheckout/index' => 2,
            'express/review' => 3,
            'onepage/success' => 4,
            'onepage/failure' => 5,
        );


        if (isset($paths[$path])) {
            $conversionPage1 = Mage::getStoreConfig('ado_seo/conversion/conversion_code_pages');
            $conversionPage2 = Mage::getStoreConfig('ado_seo/conversion/conversion_code_other_pages');
            $conversionPage3 = Mage::getStoreConfig('ado_seo/conversion/conversion_code_third_pages');
            $conversionPage1s = explode(',', $conversionPage1);
            $conversionPage2s = explode(',', $conversionPage2);
            $conversionPage3s = explode(',', $conversionPage3);
            if (in_array($paths[$path], $conversionPage1s)) {
                $conversionCode1 = Mage::getStoreConfig('ado_seo/conversion/conversion_code');
                if (!empty($conversionCode1)) {
                    $result .= str_replace('#total#', $total, $conversionCode1);
                }
            }

            if (in_array($paths[$path], $conversionPage2s)) {
                $conversionCode2 = Mage::getStoreConfig('ado_seo/conversion/conversion_code_other');
                if (!empty($conversionCode2)) {
                    $result .= str_replace('#total#', $total, $conversionCode2);
                }
            }

            if (in_array($paths[$path], $conversionPage3s)) {
                $conversionCode3 = Mage::getStoreConfig('ado_seo/conversion/conversion_code_third');
                if (!empty($conversionCode3)) {
                    $result .= str_replace('#total#', $total, $conversionCode3);
                }
            }

        }
        return $result;
    }

    /**
     * 目前存在的问题有：
     * 1、有的店有分类有的店没有分类，那么分类不能切换到各语言
     * 2、分类里的链接，shopby的伪静态化的链接，没有固定网址，也不能切换到各种语言
     * 只有商品的信息可以做切换。
     */

    public function showAlternates()
    {
        // return '';  

        $html = '';
        $product = Mage::registry('product');

        // $switchBlock =  Mage::app()->getLayout()->createBlock('page/switch');
        $stores = Mage::app()->getStores(); //$switchBlock->getStores();

        $storeMap = array(
            'en' => 'www',
            'fr' => 'fr',
            'de' => 'de',
            'es' => 'es',
            'pt' => 'pt',
            'fi' => 'fi',
            'it' => 'it',
            'se' => 'se',
            'no' => 'no',
            'nl' => 'nl',
            'dk' => 'dk',
        );


        $storeCode = Mage::app()->getStore()->getCode();
        $storeCode = strtolower($storeCode);
        if ($storeCode == 'en') $storeCode = 'www';


        if ($product && $product->getId()) {
            if ($stores) {
                foreach ($stores as $store) {
                    $code = strtolower($store->getCode());
                    if (!isset($storeMap[$code])) continue;
                    $url = Mage::getModel('catalog/product')->setStoreId($store->getId())->load($product->getId())->getProductUrl();
                    // http(s)://fr.dylanqueen.com/xxx -- http(s)://($code).dylanqueen.com/xxx
                    $url = str_replace('://' . $storeCode . '.', '://' . $storeMap[$code] . '.', $url);
                    $urlArr = explode('?', $url);
                    $url = $urlArr[0];
                    $html .= '<link rel="alternate" hreflang="' . $code . '" href="' . $url . '" />';
                }
            }
        }

        return $html;
    }


    /**
     * @by ado
     * get Size by type
     */
    public function getSizeType($_title, $value)
    {
        $_title = strtolower($_title);
        $_title = trim($_title);
        if ($_title !== 'size') return $value;
        if (!is_string($value) || stripos($value, '-') === false) return $value;
        $value_array = explode('-', $value);
        if (count($value_array) < 3) return $value;
        $flag = $flag2 = '';
        $sizeType = Mage::getStoreConfig('ado_seo/product/sizetype');
        $hidePrex = Mage::getStoreConfigFlag('ado_seo/product/sizetypeprex');
        if (!$sizeType) return $value;
        if ($sizeType == 1) {
            $flag = 'US';
        } else if ($sizeType == 2) {
            $flag = 'AU';
        } else if ($sizeType == 3) {
            $flag = 'UK';
        } else if ($sizeType == 4) {
            $flag = 'EUR';
        } else if ($sizeType == 5) {
            $flag = 'AU';
            $flag2 = 'UK';
        }
        if (empty($flag) && empty($flag2)) return $value;

        foreach ($value_array as $val) {
            if (stripos($val, $flag) !== false) {
                if (!empty($flag2) && stripos($val, $flag) !== false) {
                    $value = $val;
                } else if (!empty($flag2)) {
                    $val = str_replace(array('/', $flag, $flag2), '', $val);
                    $value = "$flag/$flag2" . $val;
                } else {
                    $value = $val;
                }
                if ($hidePrex) $value = str_replace(array('/', $flag, $flag2), '', $value);
                break;
            }
        }
        return $value;
    }

    /**
     * 返回历史浏览记录url，只存10个商品
     * @param $title
     * @return string
     */
    public function getViewedHistoryUrl($title)
    {
        $url = Mage::getUrl('catalog/viewed');
        $num = 0;
        $data = Mage::getSingleton('customer/session')->getViewdProductData();
        $data = unserialize($data);
        if (is_array($data)) $num = count($data);
        if ($num >= 10) $num = 10;
        //'.$url.'
        return '<a href="#myhistory" rel="nofollow" class="can-open">' . (Mage::helper('page')->__($title)) . ' <font color="red">(' . $num . ')</font></a> ';
    }

    //获取当前分类商品的手机端导航
    public function getSubCatalogById($_crumbName)
    {
        $html = array();
        $str = '';
        $num = 12;
        if (!empty($_crumbName)) {  //category63
            $_crumbName = str_replace('category', '', $_crumbName);
            if (is_numeric($_crumbName)) {
                $storeId = Mage::app()->getStore()->getId();
                $catalog = Mage::getModel('catalog/category')->setStoreId($storeId)->load($_crumbName);
                if ($catalog && $catalog->getId()) {
                    $subs = $catalog->getChildrenCategories();
                    if ($subs) {
                        $i = 0;
                        foreach ($subs as $sub) {
                            $sub->load($sub->getId());
                            if ($sub->getCatalogShowInBreadcrumbs()) {
                                $title = $sub->getName();
                                $url = $sub->getUrl();
                                $html[] = '<a href="' . $url . '">' . $title . '</a> ';
                                if ($i++ > $num) break;
                            }
                        }
                    }
                }
            }
        }
        if (!empty($html)) {
            $str = '<ul><li>' . implode('</li><li>', $html) . '</li></ul>';
        }
        return $str;
    }

    //根据填写的标识来判断是不是使用手机模版
    public function isMobile()
    {
        if ($this->_isMobile === null) {
            $mobileCheck = Mage::app()->getStore()->getConfig('checkout/options/mobile_check');
            if (!empty($mobileCheck)) {
                $_packName = Mage::getDesign()->getTheme('template');
                if (strtolower($mobileCheck) == 'ismobile') {
                    $this->_isMobile = true;
                } else if ($_packName == $mobileCheck) {
                    $this->_isMobile = true;
                }
            } else {
                $this->_isMobile = false;
            }
        }
        return $this->_isMobile;
    }

    //获取到货时间描述
    public function getDeliveryDays()
    {
        $html = '';
        if ($this->isWeddingDresses()) {
            $totalTime = Mage::app()->getStore()->getConfig('ado_seo/catalog/shipping_days_total_template');
            $tailTime = Mage::app()->getStore()->getConfig('ado_seo/catalog/shipping_days_tailoring_template');
            $shipTime = Mage::app()->getStore()->getConfig('ado_seo/catalog/shipping_days_shipping_template');
            $note = Mage::app()->getStore()->getConfig('ado_seo/catalog/shipping_days_note_template');
            $totalTime = $this->getFormatTime($totalTime);
            $tailTime = $this->getFormatTime($tailTime);
            $shipTime = $this->getFormatTime($shipTime);
            $note = $this->getFormatTime($note);
            $html = '<div class="delivery-description"><em></em><div class="delivery-days">';
            $html .= '<div class="delivery-tailoring-time">' . $this->__('Talloring time') . '<br><span>(' . $tailTime . ')</span><i></i></div>';
            $html .= '<div class="delivery-shipping-time">' . $this->__('Shipping') . '<br><span>(' . $shipTime . ')</span><i></i></div>';
            $html .= '<div class="delivery-total-time">' . $this->__('Delivery Time') . '<br><span>(' . $totalTime . ')</span></div>';
            $html .= '</div>';
            $html .= '<div class="delivery-note">' . $note . '</div>';
            $html .= '</div>';
        }
        return $html;
    }

    //格式化时间描述
    protected function getFormatTime($timeStr)
    {
        $regex = '/\[(.*)\]/i';
        $timeStr = Mage::helper('catalog')->__($timeStr);
        if (preg_match($regex, $timeStr, $matches)) {
            $days = $matches[1];
            if (is_numeric($days)) {
                if (stripos($days, '+') === false && (int)$days > 0) $days = '+' . $days;
                $date = date('Y-m-d H:i:s', strtotime("$days days"));
                $days = Mage::helper('core')->formatDate($date, Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM, false);
                $timeStr = str_replace($matches[0], $days, $timeStr);
            }
        }
        $timeStr = str_replace("\n", '<br/>', $timeStr);
        return $timeStr;
    }

    /**
     * 判断是否是婚纱商品
     * @param $product
     * @return bool
     */
    public function isWeddingDresses($product = null)
    {
        if (!$product) $product = Mage::registry('current_product');
        if ($product && $product->getId()) {
            $attributeSetModel = Mage::getModel("eav/entity_attribute_set");
            $attributeSetModel->load($product->getAttributeSetId());
            $attributeSetName = $attributeSetModel->getAttributeSetName();
            $attributeSetName = strtolower($attributeSetName);
            if (stripos($attributeSetName, 'wedding dress') !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * 是否开启js debug,开启=关闭js缓存
     * @return boolen
     */
    public function jsCached()
    {
        return Mage::getStoreConfigFlag('ado_seo/catalog/jscached');
    }


}
