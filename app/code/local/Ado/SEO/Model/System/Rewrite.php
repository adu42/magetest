<?php
/**
 * Magento
 * @category    Mage
 * @package     Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Ado_SEO_Model_System_Rewrite extends Enterprise_UrlRewrite_Model_Url_Rewrite
{
    const TYPE_CATEGORY = 1;
    const TYPE_PRODUCT  = 2;
    const TYPE_CUSTOM   = 3;
    const REWRITE_REQUEST_PATH_ALIAS = 'rewrite_request_path';
    
    
    
    const SHOPBY_ROUTING_SUFFIX = 'filter'; //url中的过滤标记
    //const SHOPBY_ROUTING_SUFFIX_SHOPBY = 'shopby';
    const MULTIPLE_FILTERS_DELIMITER = ','; //过滤值分割符
    const MULTIPLE_FILTERS_SIBLING_DELIMITER = '_'; //过滤词间分割符
    const FILTERS_WORDS_DELIMITER = '-'; //单词间分割符
    const FILTERS_TEMP_DELIMITER = '|'; //处理字符变量分割符
    const NOFOLLOW_FILTER_PREFIX = 'n/'; //nofollow分割符
    
    
    
    protected $_filterTags = null;
    
    protected $storeCodes=null;
    
    protected $_params=null;
    
    protected $_adminFrontName=null;
    protected $_nrewriteTags = null;

    protected $suffix=null;

    public function loadByRequestPath($paths)
    {
        $this->setId(null);
        if(empty($paths['request']))return $this;
        $rewriteRows = $this->_getResource()->getRewrites($paths);
        $matchers = $this->_factory->getSingleton('enterprise_urlrewrite/system_config_source_matcherPriority')
            ->getRewriteMatchers();

        $paths['requestAs'] = $paths['request'].$this->getCatalogSuffix();
        foreach ($matchers as $matcherIndex) {
            $matcher = $this->_factory->getSingleton($this->_factory->getConfig()->getNode(
                sprintf(self::REWRITE_MATCHERS_MODEL_PATH, $matcherIndex), 'default'
            ));
            foreach ($rewriteRows as $row) {
                if ($matcher->match($row, $paths['request']) || $matcher->match($row, $paths['requestAs'])) {
                    $this->setData($row);
                    break(2);
                }
            }
        }
        $this->_afterLoad();
        $this->setOrigData();
        $this->_hasDataChanges = false;
        return $this;
    }

    public function getCatalogSuffix(){
       if($this->suffix===null)$this->suffix= Mage::getStoreConfig('catalog/seo/category_url_suffix');
       if($this->suffix == 'html')$this->suffix='.html';
       return  $this->suffix;
    }



    /*
     * url中常用常量判断
     */
    public function getFilterTags(){
        if($this->_filterTags==null){
            $toolarFlags=array(
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
            $toolarFlags[]=Mage_CatalogSearch_Helper_Data::QUERY_VAR_NAME;
            $this->_filterTags=$toolarFlags;
        }
        return $this->_filterTags;
    }
    
    
    /**
     * Implement logic of custom rewrites
     *
     * @param   Zend_Controller_Request_Http $request
     * @param   Zend_Controller_Response_Http $response
     * @return  Mage_Core_Model_Url
     */
    public function rewrite(Zend_Controller_Request_Http $request=null, Zend_Controller_Response_Http $response=null)
    {
        unset($_GET['___store']);
        if (!Mage::isInstalled()) {
            return false;
        }
        if (is_null($request)) {
            $request = Mage::app()->getFrontController()->getRequest();
        }
        if (is_null($response)) {
            $response = Mage::app()->getFrontController()->getResponse();
        }
        if (is_null($this->getStoreId()) || false===$this->getStoreId()) {
            $this->setStoreId(Mage::app()->getStore()->getId());
        }
        $pathInfo = $request->getPathInfo();
        
        
        $notNeedProcess=$this->notNeedProcessFlag($pathInfo);

        /**
         * We have two cases of incoming paths - with and without slashes at the end ("/somepath/" and "/somepath").
         * Each of them matches two url rewrite request paths - with and without slashes at the end ("/somepath/" and "/somepath").
         * Choose any matched rewrite, but in priority order that depends on same presence of slash and query params.
         */
        
        
        //$queryString = $this->_getQueryString(); // Query params in request, matching "path + query" has more priority
		$cata='';
        $catafull='';
        $ishome=false;
        if(!$notNeedProcess){
            list($cata,$catafull,$ishome)= $this->getALlRequestParams($request);
        }       
        $requestCases = array();
        if($ishome){
            $requestCases[] = '/';
            $requestCases[] = ''; 
            $request->setPathInfo('/');
        }else{
        $origSlash = (substr($pathInfo, -1) == '/') ? '/' : '';
        $requestPath = trim($pathInfo, '/');
        // If there were final slash - add nothing to less priority paths. And vice versa.
        $altSlash = $origSlash ? '' : '/';

        $queryString = $this->_getQueryString(); // Query params in request, matching "path + query" has more priority
       
        if ($queryString) {
            $requestCases[] = $requestPath . $origSlash . '?' . $queryString;
            $requestCases[] = $requestPath . $altSlash . '?' . $queryString;
            if($cata!=$requestPath && !empty($cata)){
                if($catafull){
                    
                    $requestCases[] = $catafull.'?' . $queryString;
                }else{
                    $requestCases[] = $cata . $origSlash . '?' . $queryString;
                    $requestCases[] = $cata . $altSlash . '?' . $queryString;
                }
            }
        }
        $requestCases[] = $requestPath . $origSlash;
        $requestCases[] = $requestPath . $altSlash;
        if($cata!=$requestPath && !empty($cata)){
            if($catafull){
                $requestCases[] = $catafull;
            }
            $requestCases[] = $cata . $origSlash;
            $requestCases[] = $cata . $altSlash;
        }

        $requestCases=array_unique($requestCases);

        }
        print_r($requestCases);
        die('=====');
        $this->loadByRequestPath($requestCases);

        if (!$this->getId() && !empty($cata)) {
            if (!empty($catafull)) {
                $request->setPathInfo($catafull);
            } else {
                $request->setPathInfo($cata);
            }
        }

   		$getRequestPath = $this->getRequestPath();
		$getRequestPath = str_replace('./','/',$getRequestPath);
		$getRequestPath = rtrim($getRequestPath,'.');
        /**
         * Try to find rewrite by request path at first, if no luck - try to find by id_path
         */
        if (!$this->getId() && isset($_GET['___from_store'])) {
            try {
                $fromStoreId = Mage::app()->getStore($_GET['___from_store'])->getId();
            }
            catch (Exception $e) {
                return false;
            }

            $this->setStoreId($fromStoreId)->loadByRequestPath($requestCases);
            if (!$this->getId()) {
                return false;
            }
            $currentStore = Mage::app()->getStore();
            $this->setStoreId($currentStore->getId())->loadByIdPath($this->getIdPath());

            Mage::app()->getCookie()->set(Mage_Core_Model_Store::COOKIE_NAME, $currentStore->getCode(), true);
            $targetUrl = $request->getBaseUrl(). '/' . $getRequestPath;

            $this->_sendRedirectHeaders($targetUrl, true);
        }


        if (!$this->getId()) {
            return false;
        }



       // echo "\n==11=="; echo $getRequestPath;
        $request->setAlias(self::REWRITE_REQUEST_PATH_ALIAS, $getRequestPath);
        $external = substr($this->getTargetPath(), 0, 6);
        $isPermanentRedirectOption = $this->hasOption('RP');
        if ($external === 'http:/' || $external === 'https:') {
            $destinationStoreCode = Mage::app()->getStore($this->getStoreId())->getCode();
            Mage::app()->getCookie()->set(Mage_Core_Model_Store::COOKIE_NAME, $destinationStoreCode, true);

            $this->_sendRedirectHeaders($this->getTargetPath(), $isPermanentRedirectOption);
        } else {
            $targetUrl = $request->getBaseUrl(). '/' . $this->getTargetPath();
        }
        $isRedirectOption = $this->hasOption('R');
        if ($isRedirectOption || $isPermanentRedirectOption) {
            if (Mage::getStoreConfig('web/url/use_store') && $storeCode = Mage::app()->getStore()->getCode()) {
                if($this->getStoreId()!=1)
                $targetUrl = $request->getBaseUrl(). '/' . $storeCode . '/' .$this->getTargetPath();
            }
            
            $this->_sendRedirectHeaders($targetUrl, $isPermanentRedirectOption);
        }

        if (Mage::getStoreConfig('web/url/use_store') && $storeCode = Mage::app()->getStore()->getCode()) {
             if($this->getStoreId()!=1)
                $targetUrl = $request->getBaseUrl(). '/' . $storeCode . '/' .$this->getTargetPath();
            }

        $queryString = $this->_getQueryString();
        if ($queryString) {
            $targetUrl .= '?'.$queryString;
        }
        
        $request->setRequestUri($targetUrl);
        
        $request->setPathInfo($this->getTargetPath());
        return true;
    }

    /**
     * 获取query参数
     * @return bool|string
     */

    protected function _getQueryString()
    {
        if (!empty($_SERVER['QUERY_STRING'])) {
            $queryParams = array();
            parse_str($_SERVER['QUERY_STRING'], $queryParams);
            $hasChanges = false;
            foreach ($queryParams as $key=>$value) {
                if (substr($key, 0, 3) === '___') {
                    unset($queryParams[$key]);
                    $hasChanges = true;
                }
            }
            if ($hasChanges) {
                return http_build_query($queryParams);
            }
            else {
                return $_SERVER['QUERY_STRING'];
            }
        }
        return false;
    }

    /**
     * 处理请求的url，分析url中的真实路径和参数
     * @param $request
     * @return array
     *
     */

    public function getALlRequestParams($request){
         
         //tag a-z 
         list($catalog,$params)=$this->checkIsTags($request->getPathInfo());
         
         if(!empty($catalog)){
             $request->setParams($params);
             return array($catalog,$request->getPathInfo(),false);
         }
         // end tag a-z
        
         // catalog
        $suffix = $this->getCatalogSuffix();
        //分解url为分类、参数、过滤参数，是否是子店铺
        list($catalog,$params,$layerParams,$subStore)=$this->getUrlParams($request->getPathInfo());
         
         //获取query查询参数
         $queryParams=$this->getQueryParams();
         
         //获取post参数
         $layerParams += $request->getPost();
         //参数合并
         $params=array_merge($params,$layerParams,$queryParams);
         //参数重新装载进request
         $request->setParams($params);
         //url分解的过滤参数注册为系统过滤参数
         Mage::register('layer_params', $layerParams);
		 Mage::register('query_params', $params);
         // ----至此，变量分解完毕，且交给系统 ----- //
         $catalog=trim($catalog,'/');
         $fullCatalog=(!empty($suffix)&&!empty($catalog))?$catalog.$suffix:false;
         $ishome=(empty($catalog)&&empty($params)&&$subStore);
         //当前还需要处理url重写表匹配，所以返回路径，如果没有路径，就是首页
         return array($catalog,$fullCatalog,$ishome);
    }
    /**
     * 解析url各参数
     */
    public function getUrlParams($urlpath){
        $suffix = $this->getCatalogSuffix();

         $_urlpathArr=explode('?',$urlpath); //query变量分成两部分，处理非query部分的url
         $urlpath=$_urlpathArr[0];
         $urlpath=trim($urlpath,'/');

         //去掉扩展名
         $len=strlen($suffix);
         $len=-$len;
         if(!empty($suffix)&& substr($urlpath,$len)===$suffix)
         $urlpath = substr($urlpath, 0, strlen($urlpath) - strlen($suffix));

         //去掉第一个参数的nofollow标识
         if(substr($urlpath,0,2)===self::NOFOLLOW_FILTER_PREFIX){
            $urlpath=substr($urlpath,2);
         }

        //针对有些_order_变量里面带着 与分割符 相同的符号，先截取出来，替换完分割符后再加进去
         if(stripos($urlpath,'_order_')&&stripos($urlpath,'_dir_')){
            $subUrlPath=substr($urlpath,stripos($urlpath,'order_')+6,(stripos($urlpath,'_dir_')-stripos($urlpath,'order_')-6));
            $urlpath=str_replace($subUrlPath,'|||',$urlpath);
            $urlpath=str_replace(self::MULTIPLE_FILTERS_SIBLING_DELIMITER,'/',$urlpath);
            $urlpath=str_replace('|||',$subUrlPath,$urlpath);
         }else{
            $urlpath=str_replace(self::MULTIPLE_FILTERS_SIBLING_DELIMITER,'/',$urlpath);
         }
         

         $filterTags=$this->getFilterTags();
         $urlparams=$layerParams=$params=array();
         $catalog='';
         $urlparams=explode('/',$urlpath); //所有参数都分割成数组
         
         $subStore=false;
         
         if(!empty($urlparams)){
            $iscat=true;
            $isFilter=false;
            $nofollow = false;
            $total = count($urlparams);
            for ($i = 0; $i < $total; $i++) {
                $urlparam = $urlparams[$i];
                //如果是nofollow标识，本参数没有意义，继续下一个参数审核
                if($urlparam.'/'===self::NOFOLLOW_FILTER_PREFIX && !$nofollow){
                    $nofollow = true;
                    continue;
                }
                //如果url参数像分类，且没有常用变量在里面，做进一步判断参数
                if($iscat && !in_array($urlparam,$filterTags)){
                    //如果参数是店铺代码，切换店铺，并跳出到下一个参数审核
                    if($storeId = $this->isStoreCode($urlparam)){
                        //如果店铺id不是当前的店铺id，就切换店铺
                        if(Mage::app()->getStore()->getId()!= $storeId){
                            $this->setStoreId($storeId);
                            Mage::app()->setCurrentStore($urlparam);
                            Mage::app()->getCookie()->set(Mage_Core_Model_Store::COOKIE_NAME, $urlparam, true);
                        }
                        $subStore=true;
                        continue;
                    }
                    //默认是分类分类
                    $catalog.='/'.$urlparam;
                }elseif(in_array($urlparam,$filterTags)){
                    //如果url参数存在于常用变量中，则是分类或商品
                    $iscat=false;
                    //判断是否是过滤参数，如果是，就做标识，准备把下一个参数列为query变量
                    if($urlparam == self::SHOPBY_ROUTING_SUFFIX){
                        $isFilter=true;
                        $i++;                        
                    }else{
                        $isFilter=false;
                    }
                    //$params[$urlparam] = isset($urlparams[$i+1])?urldecode($urlparams[$i+1]):'';
                }
                //如果参数是过滤url，整理过滤参数
                if($isFilter){
                  if(isset($urlparams[$i+1])){
                    $urlparam=$urlparams[$i];
                   // $urlparam=str_replace(self::MULTIPLE_FILTERS_SIBLING_DELIMITER,self::FILTERS_WORDS_DELIMITER,$urlparam);
                    $params[$urlparam] = $layerParams[$urlparam] = urldecode($urlparams[$i+1]); 
                    $i++;
                  }
                }else if(!$iscat){
                //如果参数不是过滤，也不是分类，就以普通的参数传递
                    if(isset($urlparams[$i+1])){
                        $params[$urlparam] = urldecode($urlparams[$i+1]);
                        $i++;
                    }
                }                
            }
         }else{
             //如果没有参数，就切换到默认店铺
            $this->setMyDefaultStore();
         }

         return array($catalog,$params,$layerParams,$subStore);
    }
    
    /**
     * Get query params part of url
     *
     * @param bool $escape "&" escape flag
     * @return string
     */
    public function getQueryParams($returnStr=false)
    {
        if (!$this->hasData('query_params')) {
            $params = array();
            if ($this->_getData('query')) {
              parse_str($this->_getData('query'),$params);
            }else{
                 if(isset($_GET)) {
                    $params = array();
                }                       
            }



			// $exclude = array('utm_',
			// 	  'glcid',
			// 	  'gclid',
			// 	  'ref');
			
			// foreach($params as $key=>$param){
			// 	foreach($exclude as $k){
			// 	   if(stripos($key,$k)!==false)unset($params[$key]);
			//     }
			// }


            $this->setData('query_params', $params);
        }
        if($returnStr){
            return http_build_query($params);
        }
        return $this->_getData('query_params');
    }
    
    public function isStoreCode($storeCode){
        $stores = Mage::app()->getStores(true, true);
        if ($storeCode!='' && isset($stores[$storeCode])) {
            return $stores[$storeCode];
        }
        return false;
    }
    
    public function getAdminfrontName(){
        if($this->_adminFrontName===null){
            $this->_adminFrontName=(string)Mage::app()->getConfig()->getNode('admin/routers/adminhtml/args/frontName');
        }
       return $this->_adminFrontName;
    }
    
    public function setMyDefaultStore(){
        Mage::app()->getCookie()->delete(Mage_Core_Model_Store::COOKIE_NAME);
    }
    
    /**
	 * 启用本方法条件
	 * 1、url重写
	 * 2、没有atoz插件
	 * 3、search url重写
     * //tags
     */   
    public function checkIsTags($uri){
        
         $path='';
         $params=array();

		 $search_pattern = "/^\/[S|s]earch\/([A-Z0-9a-z_-]+).html$/";
         $search_pattern_as = "/^\/[S|s]earch\/([A-Z0-9a-z_-]+)$/";
		 preg_match($search_pattern, $uri, $matches); 			
		 if(is_array($matches) && count($matches) == 2)
		 {
			$path="/catalogsearch/result/index";
			$params['q']=$matches[1];
		 }else{
             preg_match($search_pattern_as, $uri, $matches);
             if(is_array($matches) && count($matches) == 2) {
                 $path = "/catalogsearch/result/index";
                 $params['q'] = $matches[1];
             }
         }
         if(isset($params['q']))$params['q']=str_replace('_',' ',$params['q']);
		 return array($path,$params);
		 
		 /*======以下内容是atoz插件的，不再使用=======*/
		 $atozlist_With_Page_Pattern = "/^\/[C|c]ommodity\/([A-Z0-9])\/([0-9]+).html$/";
         $atozlist_Without_Page_Pattern = "/^\/[C|c]ommodity\/([A-Z0-9])$/";
         $atozdetail_Pattern = "/^\/[C|c]ommodity\/([A-Z0-9a-z_]+).html$/";

         $productvote_With_Page_Pattern = "/^\/[C|c]ontrast\/([0-9]+).html$/";
         $productvote_Without_Page_Pattern = "/^\/[C|c]ontrast\/$/";
		 $productvote_Without_Page_Pattern_as = "/^\/[C|c]ontrast$/";
		 
         $productvote_Pattern = "/^\/[C|c]ontrast\/([0-9]+)-([0-9]+).html$/";

        $review_With_Page_Pattern = "/^\/[R|r]eview\/([0-9]+).html$/";
        $review_Without_Page_Pattern = "/^\/[R|r]eview\/$/";
		$review_Without_Page_Pattern_as = "/^\/[R|r]eview$/";
        $review_Pattern = "/^\/[R|r]eview\/([\w\W][^\/]+).html$/";
        $reviews_List_Without_Page_Pattern = "/^\/[R|r]eview\/([\w\W][^\/]+)$/";
        $reviews_List_With_Page_Pattern = "/^\/[R|r]eview\/([\w\W]+)\/([0-9]+).html$/";



        $allcategory_without_page_Pattern = "/^\/see-all.html$/";

        if(!empty($uri)){
            	$matches = array();
            	
            	preg_match($atozlist_Without_Page_Pattern, $uri, $matches);
            	
            	if(is_array($matches) && count($matches) == 2)
            	{
            	   $path="/atozlist/Index/index";
                   $params['tag']=$matches[1];
            	}
            	else{
            	   preg_match($atozlist_With_Page_Pattern, $uri, $matches);
            	   if(is_array($matches) && count($matches) == 3)
            		{
            		   $path="/atozlist/Index/index";
                       $params['tag']=$matches[1];
                       $params['page']=$matches[2];
            		}
            		else{
            			preg_match($atozdetail_Pattern, $uri, $matches);
            			
            			if(is_array($matches) && count($matches) == 2)
            			{
            			    $path="/atozdetail/Index/index";
                            $params['query']=$matches[1];
            			}
            		}
            	}

            //product vote
            $matches1 = array();

            preg_match($productvote_Without_Page_Pattern, $uri, $matches1);
			preg_match($productvote_Without_Page_Pattern_as, $uri, $matches1as);

            if(is_array($matches1as) && count($matches1as) == 1)
            {
                $path="/productvote/Index/index";
            }else if(is_array($matches1) && count($matches1) == 1)
            {
                $path="/productvote/Index/index";
            }
            else{
                preg_match($productvote_With_Page_Pattern, $uri, $matches1);
                if(is_array($matches1) && count($matches1) == 2)
                {
                    $path="/productvote/Index/index";
                    $params['p']=$matches1[1];
                }
                else{
                    preg_match($productvote_Pattern, $uri, $matches1);

                    if(is_array($matches1) && count($matches1) == 3)
                    {
                        $path="/productvote/index/getvote";
                        $params['pid1']=$matches1[1];
                        $params['pid2']=$matches1[2];
                    }
                }
            }

			//reviews
            $matches2 = array();

            preg_match($review_Without_Page_Pattern, $uri, $matches2);
            preg_match($review_Without_Page_Pattern_as, $uri, $matches_as);

			if(is_array($matches_as) && count($matches_as) == 1){
				 $path = "/reviews/Index/index";
			}else if (is_array($matches2) && count($matches2) == 1) {
                $path = "/reviews/Index/index";
            } else {
                preg_match($review_With_Page_Pattern, $uri, $matches2);
                if (is_array($matches2) && count($matches2) == 2) {
                    $path = "/reviews/Index/index";
                    $params['p'] = $matches2[1];
                } else {
                    preg_match($review_Pattern, $uri, $matches2);

                    if (is_array($matches2) && count($matches2) == 2) {
                        $path = "/reviews/Index/view";
                        $params['q'] = $matches2[1];
                    }
                    else {

                        preg_match($reviews_List_With_Page_Pattern, $uri, $matches2);
                        if (is_array($matches2) && count($matches2) == 3) {
                            $path = "/reviews/Index/list";
                            $params['q'] = $matches2[1];
                            $params['p'] = $matches2[2];
                        }else {
                            preg_match($reviews_List_Without_Page_Pattern, $uri, $matches2);
                            if (is_array($matches2) && count($matches2) == 2) {
                                $path = "/reviews/Index/list";
                                $params['q'] = $matches2[1];
                            }
                        }

                    }
                }
            }

            //all category
            $matches3 = array();
            preg_match($allcategory_without_page_Pattern, $uri, $matches3);
            if (is_array($matches3) && count($matches3) == 1){
                $path = "/allcategory/Index/index";
            }
        }
        return array($path,$params);
    }
    
     public function notNeedProcessFlag($uri){  
		if(empty($this->_nrewriteTags)){
			$_tags = array();
			$tags = Mage::getStoreConfig('ado_seo/catalog/url_nrewrite_flag');
			if(!empty($tags)){
				$tags = str_replace(array("\n",';','|'),",",$tags);
				$_tags = explode(',',$tags);
				foreach($_tags as &$_tag){
					$_tag=trim($_tag);
				}
			}
			$_tags[]=$this->getAdminfrontName();
			$this->_nrewriteTags = $_tags;
		}
		if(!empty($this->_nrewriteTags)){
			foreach($this->_nrewriteTags as $flag){
				if(stripos($uri,$flag)!==false)return true;
			}
		}
        return false;
    }
    
}

