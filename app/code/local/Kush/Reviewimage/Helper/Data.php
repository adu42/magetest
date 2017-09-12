<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Community
 * @package     Clarion_Bannerresponsive
 * @copyright   Copyright magento@clariontechnologies.co.in
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */


/**
 * helper data file
 *
 * @category   Community
 * @package    Clarion_Bannerresponsive
 * @author     magento@clariontechnologies.co.in
 */
class Kush_Reviewimage_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $product_url = array();
    protected $productUrlSuffix = '';

    /**
     * 小图的缩放尺寸
     *  normal|small|thumbnail
     * @return array
     */
    public function getResolutionType($sizeType = 'normal')
    {
        $_sizes = array('width' => 200, 'height' => 200);
        if ($sizeType == 'normal') {
            $size = Mage::getStoreConfig("reviewimage/reimage/imageresolutionnormal");
        } elseif ($sizeType == 'small') {
            $size = Mage::getStoreConfig("reviewimage/reimage/imageresolution");
        } elseif ($sizeType == 'video') {
            $size = Mage::getStoreConfig("reviewimage/reimage/imageresolutionvideo");
        } else {
            $size = Mage::getStoreConfig("reviewimage/reimage/imageresolutionsmall");
        }
        if ($size) {
            $size = str_replace(array('x', 'X', ':'), ',', $size);
            $size = trim($size);
            $sizes = explode(',', $size);
            if (count($sizes) >= 2) {
                $_sizes['width'] = is_numeric($sizes[0]) ? $sizes[0] : $_sizes['width'];
                $_sizes['height'] = is_numeric($sizes[1]) ? $sizes[1] : $_sizes['height'];
            } elseif ($sizes[0] > 0) {
                $_sizes['width'] = $_sizes['height'] = $sizes[0];
            }
        }
        return $_sizes;
    }

    public function getResolution()
    {
        return $this->getResolutionType('small');
    }

    public function getResolutionSmall()
    {
        return $this->getResolutionType('thumbnail');
    }

    public function getResolutionNormal()
    {
        return $this->getResolutionType('normal');
    }

    public function getResolutionVideo()
    {
        return $this->getResolutionType('video');
    }

    /**
     * 插件是否激活了
     * @return mixed
     */
    public function getActive()
    {
        return Mage::getStoreConfig("reviewimage/reimage/reimagefield");
    }

    /**
     * 最小的分类id
     * @return mixed
     */
    public function getMinCatalogId()
    {
        return Mage::getStoreConfig("reviewimage/reimage/mincatalog");
    }

    /**
     * 是否要标题输入框
     * @return mixed
     */
    public function useTitle()
    {
        return Mage::getStoreConfig("reviewimage/reimage/reimagetitle");
    }

    /**
     * 是否可上传图片
     * @return mixed
     */
    public function useUpload()
    {
        return Mage::getStoreConfig("reviewimage/reimage/reimageupload");
    }

    /**
     * 提交了就展示，不用审核
     * @return mixed
     */
    public function usePostEnable()
    {
        return Mage::getStoreConfig("reviewimage/reimage/reimageshow");
    }

    /**
     * 分享的固定标题是什么?
     * @return string
     */
    public function shareTitle()
    {
        return Mage::getStoreConfig("reviewimage/reimage/imagesharetitle");
    }


    public function shareTitleDescription()
    {
        return Mage::getStoreConfig("reviewimage/reimage/imagesharedescription");
    }


    public function homeTitle()
    {
        return Mage::getStoreConfig("reviewimage/reimage/imagehometitle");
    }

    public function homeDescription()
    {
        return Mage::getStoreConfig("reviewimage/reimage/imagehomedescription");
    }


    public function listTitle()
    {
        return Mage::getStoreConfig("reviewimage/reimage/imagelisttitle");
    }

    public function listKeywords()
    {
        return Mage::getStoreConfig("reviewimage/reimage/imagelistkeyword");
    }

    public function listDescription()
    {
        return Mage::getStoreConfig("reviewimage/reimage/imagelistdescription");
    }

    public function formTitle()
    {
        return Mage::getStoreConfig("reviewimage/reimage/imageformtitle");
    }

    public function formKeywords()
    {
        return Mage::getStoreConfig("reviewimage/reimage/imageformkeyword");
    }

    public function formDescription()
    {
        return Mage::getStoreConfig("reviewimage/reimage/imageformdescription");
    }

    /**
     * like路径
     * @return string url
     */
    public function likeUrl()
    {
        return Mage::getUrl('review/product/like');
    }

    /**
     * 是否可用sku输入框
     * @return bool
     */
    public function enableSku()
    {
        return Mage::getStoreConfig("reviewimage/reimage/reimageshowsku");
    }

    public function imageRequired()
    {
        return Mage::getStoreConfig("reviewimage/reimage/reimagemust");
    }

    public function getRatingCode()
    {
        return Mage::getStoreConfig("reviewimage/reimage/ratingcode");
    }


    /**
     * 获得可上传的图片大小
     * 2M
     * @return int
     */
    public function maxFileSize()
    {
        // $size = Mage::getStoreConfig("reviewimage/reimage/maxfilesize");
        return 2097152;
    }


    /**
     * 获得可上传的图片个数
     * @return array
     */
    public function maxImages()
    {
        $maxImages = Mage::getStoreConfig("reviewimage/reimage/maximages");
        $num = array('a', 'b', 'c', 'd', 'e', 'f', 'g');
        if (!$maxImages) $maxImages = 3;
        if ($maxImages > 7) $maxImages = 7;
        if ($maxImages < 3) $maxImages = 3;
        return array_slice($num, 0, $maxImages);
    }

    /**
     * 获得图片存储目录
     * @param $fileName
     */
    public function getImagePath()
    {
        $imagePath = Mage::getStoreConfig("reviewimage/reimage/imagepath");
        if (empty($imagePath)) $imagePath = 'reviewimages';
        return $imagePath;
    }

    /**
     * 删除图片
     * @param $fileName
     */
    public function removeFile($fileName)
    {
        $serverPath = Mage::getBaseDir('media') . DS . $this->getImagePath() . DS . $fileName;
        if (file_exists($serverPath)) @unlink($serverPath);
    }

    /**
     * 根据商品获得比较接近的分类
     * @param $productId
     * @return array
     */
    public function getCatalogId($productId)
    {
        $catalogId = 2;

        $_catalog = Mage::registry('current_category');

        if ($_catalog && $_catalog->getId() && $_catalog->getShowInReviews()) {
            $catalogId = $_catalog->getId();
            $storeId = Mage::app()->getStore()->getStoreId();
        } else {
            if (is_numeric($productId)) {
                $product = Mage::getModel('catalog/product')->load($productId);
            } elseif (is_object($productId)) {
                $product = $productId;
            }

            if ($product && $product->getId()) {
                $minCatalogId = $this->getMinCatalogId();
                $catalogIds = $product->getCategoryIds();
                if (is_array($catalogIds)) {
                    sort($catalogIds);
                    foreach ($catalogIds as $_catalogId) {
                        if ($_catalogId > $minCatalogId) {
                            $catalogId = $_catalogId;
                            break;
                        }
                    }
                }
                $storeId = $product->getStoreId();
            }
        }
        return array($catalogId, $storeId);
    }

    /**
     * 获得小图,中图，大图
     * image
     */
    public function getImage($columnValue)
    {
        $_thumbnail_image = '';
        $_small_image = '';
        $_normal_image = '';
        $_video_image = '';

        if (!empty($columnValue)) {
            $imageName = $columnValue;
            $imageName = str_replace($this->getImagePath(), '', $imageName);
            $imageName = str_replace('//', '/', $imageName);
            // $imageUrl = Mage::getBaseUrl("media") . $this->getImagePath() . $imageName;
            $imagePath = Mage::getBaseDir('media') . DS . $this->getImagePath() . DS . $imageName;

            $imageNormalResized = Mage::getBaseDir('media') . DS . $this->getImagePath() . "-normal" . DS . $imageName;
            $_normal_image = Mage::getBaseUrl("media") . $this->getImagePath() . '-normal' . $imageName;

            $imageResized = Mage::getBaseDir('media') . DS . $this->getImagePath() . "-resize" . DS . $imageName;
            $_small_image = Mage::getBaseUrl("media") . $this->getImagePath() . '-resize' . $imageName;
            $imageResizedThumbnail = Mage::getBaseDir('media') . DS . $this->getImagePath() . "-resize-thumbnail" . DS . $imageName;
            $_thumbnail_image = Mage::getBaseUrl("media") . $this->getImagePath() . '-resize-thumbnail' . $imageName;

            $imageVideoResized = Mage::getBaseDir('media') . DS . $this->getImagePath() . "-resize-video-thumb" . DS . $imageName;
            $_video_image = Mage::getBaseUrl("media") . $this->getImagePath() . '-resize-video-thumb' . $imageName;


            if (!file_exists($imageResized) && file_exists($imagePath)) {
                $imageObj = new Varien_Image($imagePath);
                $imageObj->constrainOnly(true);
                $imageObj->keepAspectRatio(true);
                $imageObj->keepFrame(false);
                $imageObj->backgroundColor(array(255, 255, 255));
                $Resolution = $this->getResolution();
                $imageObj->resize($Resolution['width'], $Resolution['height']);
                $imageObj->save($imageResized);
            }
            if (!file_exists($imageResizedThumbnail) && file_exists($imagePath)) {
                $imageObj = new Varien_Image($imagePath);
                $imageObj->constrainOnly(true);
                $imageObj->keepAspectRatio(true);
                $imageObj->keepFrame(true);
                $imageObj->backgroundColor(array(255, 255, 255));
                $Resolution = $this->getResolutionSmall();
                $imageObj->resize($Resolution['width'], $Resolution['height']);
                $imageObj->save($imageResizedThumbnail);
            }
            if (!file_exists($imageNormalResized) && file_exists($imagePath)) {
                $imageObj = new Varien_Image($imagePath);
                $imageObj->constrainOnly(true);
                $imageObj->keepAspectRatio(true);
                $imageObj->keepFrame(true);
                $imageObj->backgroundColor(array(255, 255, 255));
                $Resolution = $this->getResolutionNormal();
                $imageObj->resize($Resolution['width'], $Resolution['height']);
                $imageObj->save($imageNormalResized);
            }

            // 新增播放视频
            if (!file_exists($imageVideoResized) && file_exists($imagePath)) {
                $imageObj = new Varien_Image($imagePath);
                $imageObj->constrainOnly(true);
                $imageObj->keepAspectRatio(false);
                $imageObj->keepFrame(false);
                $imageObj->backgroundColor(array(255, 255, 255));
                $Resolution = $this->getResolutionVideo();
                $imageObj->resize($Resolution['width'], $Resolution['height']);
                $imageObj->save($imageVideoResized);
            }

        }
        return array('thumbnail' => $_thumbnail_image, 'small' => $_small_image, 'big' => $_normal_image, 'video' => $_video_image);
    }

    /**
     * 获得评论里的所有图片
     * @param $review
     * @return array
     */
    public function getImages($review)
    {
        $images = $review->getImages();
        if (!$images) {
            $nums = $this->maxImages();
            $_data = array();
            foreach ($nums as $n) {
                $field = 'review_image_' . $n;
                $img = $review->getData($field);
                if (!empty($img)) {
                    $_data[] = $this->getImage($img);
                }
            }
            $images = $_data;
            $review->setImages($images);
        }
        return $images;
    }

    /**
     * 获得Video Thumb
     * @param $review
     * @return string
     */
    public function getVideoThumb($review)
    {
        $field = 'review_video_thumb';
        $img = $review->getData($field);
        if (!empty($img)) {
            $_data = $this->getImage($img);
            $review->setVideoThumb($_data);
            return $images;
        } else {
            return false;
        }
    }


    /**
     * 获得Video Thumb (数组)
     * @param $review
     * @return string
     */
    public function _getVideoThumb($img)
    {
        if (!empty($img)) {
            $_data = $this->getImage($img);
        }
        $images = $_data;
        return $images;
    }


    /**
     * 创建样本商品，如果存在的话就取样本商品
     * sku=sample
     *
     */
    public function createSampleProduct()
    {
        $sku = 'sample';
        $productId = Mage::getModel('catalog/product')->getIdBySku($sku);
        if (!$productId) {
            $localStoreCode = Mage::app()->getStore()->getCode();
            Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
            $product = Mage::getModel('catalog/product');
            try {
                $product
//    ->setStoreId(1) //you can set data in store scope
                    ->setWebsiteIds(array(1))//website ID the product is assigned to, as an array
                    ->setAttributeSetId(4)//ID of a attribute set named 'default'
                    ->setTypeId('simple')//product type
                    ->setCreatedAt(strtotime('now'))//product creation time
//    ->setUpdatedAt(strtotime('now')) //product update time

                    ->setSku($sku)//SKU
                    ->setName($sku)//product name
                    ->setWeight(4.0000)
                    ->setStatus(2)//product status (1 - enabled, 2 - disabled)
                    ->setTaxClassId(0)//tax class (0 - none, 1 - default, 2 - taxable, 4 - shipping)
                    ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)//catalog and search visibility
                    ->setManufacturer(28)//manufacturer id
                    ->setColor(24)
                    ->setNewsFromDate('06/26/2014')//product set as new from
                    ->setNewsToDate('06/30/2014')//product set as new to
                    ->setCountryOfManufacture('AF')//country of manufacture (2-letter country code)
                    ->setPrice(1000.00)//price in form 11.22
                    ->setCost(2000.00)//price in form 11.22
                    ->setSpecialPrice(1000.00)//special price in form 11.22
                    ->setSpecialFromDate('06/1/2014')//special price from (MM-DD-YYYY)
                    ->setSpecialToDate('')//special price to (MM-DD-YYYY)
                    ->setMsrpEnabled(1)//enable MAP
                    ->setMsrpDisplayActualPriceType(4)//display actual price (1 - on gesture, 2 - in cart, 3 - before order confirmation, 4 - use config)
                    ->setMsrp(1000.00)//Manufacturer's Suggested Retail Price
                    ->setMetaTitle($sku)
                    ->setMetaKeyword($sku)
                    ->setMetaDescription($sku)
                    ->setDescription($sku)
                    ->setShortDescription($sku)
                    ->setMediaGallery(array('images' => array(), 'values' => array()))//media gallery initialization
                    ->addImageToMediaGallery('media/catalog/product/s/a/sample.png', array('image', 'thumbnail', 'small_image'), false, false)//assigning image, thumb and small image to media gallery
                    ->setStockData(array(
                            'use_config_manage_stock' => 0, //'Use config settings' checkbox
                            'manage_stock' => 1, //manage stock
                            'min_sale_qty' => 1, //Minimum Qty Allowed in Shopping Cart
                            'max_sale_qty' => 2, //Maximum Qty Allowed in Shopping Cart
                            'is_in_stock' => 1, //Stock Availability
                            'qty' => 1000 //qty
                        )
                    )
                    ->setCategoryIds(array(2, 3, 4)); //assign product to categories
                $product->save();
                Mage::app()->setCurrentStore($localStoreCode);
                $productId = $product->getId();
//endif;
            } catch (Exception $e) {
                Mage::log($e->getMessage(), null, "reviewimage.log");
            }
        }
        return $productId;
    }

    /**
     * product Url
     */
    public function getProductUrl($review)
    {
        $url = '';
        if ($review instanceof Mage_Review_Model_Review) {
            $productId = $review->getEntityPkValue();
            if ($productId) {
                if (isset($this->product_url[$productId])) return $this->product_url[$productId];
                $_product = Mage::getModel('catalog/product')->load($productId);
                if ($_product && $_product->getId()) {
                    // $url = $_product->getProductUrl();
                    //  if(stripos($url,'product/')!==false){
                    $url = Mage::getBaseUrl() . $_product->getUrlKey();
                    $url .= $this->getProductUrlSuffix();
                    $this->product_url[$productId] = $url;
                    //  }
                }
                //  $url = Mage::helper('catalog/product')->getProductUrl($productId);
            }
        }
        return $url;
    }

    protected function getProductUrlSuffix()
    {
        if (empty($this->productUrlSuffix)) {
            $this->productUrlSuffix = Mage::helper('catalog/product')->getProductUrlSuffix();
            if (substr($this->productUrlSuffix, 0, 1) != '.') $this->productUrlSuffix = '.' . $this->productUrlSuffix;
        }
        return $this->productUrlSuffix;
    }

    /***
     * 评论图列表继续往下加载的链接地址，有参数就装配参数
     * @return string
     */
    public function loadUrl()
    {
        $cid = (int)$this->_getRequest()->getParam('cid', false);
        $params = array();
        if ($cid) $params['cid'] = $cid;
        return Mage::getUrl('review/gallery/load', $params);
    }

    /**
     * 普通列表url
     * @return string
     */
    public function getReviewGalleryUrl()
    {
        return Mage::getUrl('review/gallery');
    }

    /**
     * 分类列表url
     * @param array $category
     * @return string
     */
    public function getCatalogReviewsUrl($category = array())
    {
        $param = array();
        if (!empty($category)) {
            if ($category instanceof OnePica_ImageCdn_Model_Catalog_Category) {
                $param['cid'] = $category->getId();
            } else if (is_array($category)) {
                $param['cid'] = $category['entity_id'];
            } elseif (is_numeric($category)) {
                $param['cid'] = $category;
            }
        } else {
            $_catalog = Mage::registry('current_category');
            if ($_catalog && $_catalog->getId() && $_catalog->getShowInReviews()) {
                $param['cid'] = $_catalog->getId();
            }
        }
        if (!empty($param))
            return Mage::getUrl('review/gallery/index', $param);
        return Mage::getUrl('review/gallery');
    }

    /**
     * 写评论url
     */
    public function getWriteUrl()
    {
        $param = array();
        $product = Mage::registry('current_product');
        if ($product && $product->getId()) {
            $param['id'] = $product->getId();
        }
        return Mage::getUrl('review/product/form', $param);
    }


    public function getProductUrlByProduct($_product)
    {
        $url = '';

        if ($_product && $_product->getId()) {
            if (isset($this->product_url[$_product->getId()])) return $this->product_url[$_product->getId()];
            // $url = $_product->getProductUrl();
            //  if(stripos($url,'product/')!==false){
            $url = Mage::getBaseUrl() . $_product->getUrlKey();
            $url .= $this->getProductUrlSuffix();
            $this->product_url[$_product->getId()] = $url;
            //   @file_put_contents(dirname(__FILE__).'/aa.txt',print_r($url,true)."\n",FILE_APPEND);
            //   }
        }
        //  $url = Mage::helper('catalog/product')->getProductUrl($productId);

        return $url;
    }

    /**
     * 后台选择分类
     */
    public function getReviewCatalogOptionArray()
    {
        $options = array();
        $_catagories = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('name')
            //  ->addAttributeToSelect('show_in_reviews')
            //   ->addAttributeToFilter('show_in_reviews',1)
            ->setOrder('level')
            ->setOrder('position')
            ->setOrder('entity_id');
        if ($_catagories && $_catagories->count()) {
            foreach ($_catagories as $_catagory) {
                $options[] = array(
                    'value' => $_catagory->getId(),
                    'label' => $_catagory->getName(),
                );
            }
        }
        return $options;
    }


    /**
     * Get review statuses with their codes
     *
     * @return array
     */
    public function getReviewStatuses()
    {
        return array(
            Mage_Review_Model_Review::STATUS_APPROVED => $this->__('Approved'),
            Mage_Review_Model_Review::STATUS_PENDING => $this->__('Pending'),
            Mage_Review_Model_Review::STATUS_NOT_APPROVED => $this->__('Not Approved'),
        );
    }

    /**
     * Get review statuses as option array
     *
     * @return array
     */
    public function getReviewStatusesOptionArray()
    {
        $result = array();
        foreach ($this->getReviewStatuses() as $k => $v) {
            $result[] = array('value' => $k, 'label' => $v);
        }
        return $result;
    }


    /**
     * 获取自定义rating结构
     */
    public function getCustomRatingCollection()
    {
        $ratingCollections = new Varien_Data_Collection();
        $rating = new Varien_Object();
        $options = new Varien_Data_Collection();
        $rating->setId(1);
        $rating->setRatingCode($this->getRatingCode());
        for ($i = 1; $i <= 5; $i++) {
            $option = new Varien_Object();
            $option->setId($i);
            $option->setValue($i);
            $options->addItem($option);
        }
        $rating->setOptions($options);
        $ratingCollections->addItem($rating);
        return $ratingCollections;
    }

    /**
     * 保留0店铺，为了分类前端写的还是后台写的评论
     * 所有评论这两个店铺的统计数据进行汇总
     * review_entity_summary 表
     * review_store 表中每个review_id 对应两条store_id数据
     * review_detail 表中每个review_id 对应一条store_id数据
     * @return array
     */
    public function getUsedStoreIds()
    {
        return array(0, 1);
    }


    // 获取指定reviewId 图片和链接
    public function getReviewVideoInfo($reviewId)
    {
        $info = Mage::getModel('review/review')->getReviewInfo($reviewId);
        return $info;
    }


    // 更新ReviewUrl
    public function saveReviewUrl($data)
    {
        Mage::getModel('review/review')->_saveReviewUrl($data);
    }


    // 产品展示页的评论 url
    public function getProductReviewUrl($product_name, $reviewId)
    {
        $keyword = strtolower($product_name);
        $keyword = str_replace(array(' ', '+'), '_', $keyword);
        $url = Mage::getModel('core/url')->getUrl('product-review') . $reviewId . '-' . $keyword . '.html';
        return $url;
    }

}