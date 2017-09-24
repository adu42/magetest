<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 * @category    Mage
 * @package     Mage_Review
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Review controller
 *
 * @category   Mage
 * @package    Mage_Review
 * @author     Magento Core Team <core@magentocommerce.com>
 */
require_once("Mage/Review/controllers/ProductController.php");

class Kush_Reviewimage_ProductController extends Mage_Review_ProductController
{
    /**
     * *  有商品关联的数据提交,商品页的评论表单提交到这里。
     *  /review/product/post
     */
    public function postAction()
    {
        if (!$this->_validateFormKey()) {
            // returns to the product item page
            $this->_redirectReferer();
            return;
        }
        $session = Mage::getSingleton('core/session');
        if ($data = Mage::getSingleton('review/session')->getFormData(true)) {
            $rating = array();
            if (isset($data['ratings']) && is_array($data['ratings'])) {
                $rating = $data['ratings'];
            }
        } else {
            $data = $this->getRequest()->getPost();
            $rating = $this->getRequest()->getParam('ratings', array());
        }
        $data['review_rating'] = isset($rating[1]) ? $rating[1] : 4;

        foreach (array('title', 'nickname', 'detail') as $param) {
            if (isset($data[$param])) {
                if (stripos($data[$param], 'http') !== false || stripos($data[$param], '<script>') !== false) {
                    $session->addError($this->__('Unable to post the review.'));
                    $this->_redirectReferer();
                    return;
                }
            }
        }
        /*
         * 上传图片A
         */
        $helper = Mage::helper("reviewimage");
        $nums = $helper->maxImages();
        $path = $helper->getImagePath();
        $_data = array();
        foreach ($nums as $n) {
            $field = 'review_image_' . $n;
            $data[$field] = '';
            if (isset($_FILES[$field]['name']) && $_FILES[$field]['name'] != '') {
                try {
                    /* Starting upload */
                    $uploader = new Varien_File_Uploader($field);
                    // Any extention would work
                    $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                    $uploader->addValidateCallback('catalog_product_image', Mage::helper('catalog/image'), 'validateUploadFile');
                    $uploader->addValidateCallback('size', $this, 'validateMaxSize');
                    $uploader->setAllowRenameFiles(true);
                    // Set the file upload mode
                    // false -> get the file directly in the specified folder
                    // true -> get the file in the product like folders
                    //	(file.jpg will go in something like /media/f/i/file.jpg)
                    $uploader->setFilesDispersion(false);
                    // We set media as the upload dir
                    $_path = Mage::getBaseDir('media') . DS . $path . DS;
                    $result = $uploader->save($_path, $_FILES['reviewimage']['name']);
                    $filename = $result['file'];
                    //this way the name is saved in DB
                    $_data[] = "$path/" . $filename;
                } catch (Exception $e) {
                    Mage::log($e->getMessage(), null, "reviewimage.log");
                }
            }
        }
        /**
         * 整理成abc先后顺序存入数据库
         */
        if (!empty($_data)) {
            foreach ($nums as $n) {
                $field = 'review_image_' . $n;
                if ($_data) {
                    $data[$field] = array_shift($_data);
                } else {
                    break;
                }
            }
        }

        if ($helper->usePostEnable()) {
            $data['enable'] = 1;
        }

        if (($product = $this->_initProduct()) && !empty($data)) {
            $catalogId = 2;
            $minCatalogId = $helper->getMinCatalogId();
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
            $data['review_catalog'] = $catalogId;

            $data['review_likes'] = 1;


            /* @var $session Mage_Core_Model_Session */
            $review = Mage::getModel('review/review')->setData($data);
            /* @var $review Mage_Review_Model_Review */

            $validate = $review->validate();
            if ($validate === true) {
                try {
                    $review->setEntityId($review->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE))
                        ->setEntityPkValue($product->getId())
                        ->setStatusId(Mage_Review_Model_Review::STATUS_PENDING)
                        ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                        ->setStoreId(1)
                        ->setStores(array(0, 1))
                        ->save();
                    /*
                    foreach ($rating as $ratingId => $optionId) {
                        Mage::getModel('rating/rating')
                        ->setRatingId($ratingId)
                        ->setReviewId($review->getId())
                        ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                        ->addOptionVote($optionId, $product->getId());
                    }
                    */
                    $review->aggregate();
                    $session->addSuccess($this->__('Your review has been accepted for moderation.'));
                } catch (Exception $e) {
                    Mage::log($e->getLine() . '-' . $e->getMessage(), null, 'reviewimage.log');
                    $session->setFormData($data);
                    $session->addError($this->__('Unable to post the review.'));
                }
            } else {
                $session->setFormData($data);
                if (is_array($validate)) {
                    Mage::log($validate, null, "reviewimage.log");
                    foreach ($validate as $errorMessage) {

                        $session->addError($errorMessage);
                    }
                } else {
                    $session->addError($this->__('Unable to post the review.'));
                }
            }
        }

        if ($redirectUrl = Mage::getSingleton('review/session')->getRedirectUrl(true)) {
            $this->_redirectUrl($redirectUrl);
            return;
        }
        $this->_redirectReferer();
    }

    /**
     * 验证上传的文件大小，< 2M
     * @param $filePath
     * @throws Mage_Core_Exception
     */
    public function validateMaxSize($filePath)
    {
        $_maxFileSize = Mage::helper('reviewimage')->maxFileSize();
        if ($_maxFileSize > 0 && filesize($filePath) > $_maxFileSize) {
            throw Mage::exception('Mage_Core', Mage::helper('review')->__('Uploaded file is larger than %.2f kilobytes allowed by server', ceil($_maxFileSize / 1024)));
        }
    }

    /**
     * like数据提交保存地址
     * /review/product/like
     */
    public function likeAction()
    {
        $reviewId = (int)$this->getRequest()->getParam('review_id');
        $less = $this->getRequest()->getParam('act', false);
        if ($reviewId && is_numeric($reviewId)) {
            // $likedString =  Mage::getSingleton('review/session')->getReviewLiked();
            //  $liked = !empty($likedString)?unserialize(base64_decode($likedString)):array();
            //   if((!empty($liked) && !in_array($reviewId,$liked))|| empty($liked)){
            $review = Mage::getModel('review/review')->load($reviewId);
            if ($review && $review->getId()) {
                //   $liked[]=$reviewId;
                //    $likedString =base64_encode(serialize($liked));
                //     Mage::getSingleton('review/session')->setReviewLiked($likedString);
                try {
                    $liked_value = (int)$review->getReviewLikes();
                    if ($less) {
                        $liked_value--;
                    } else {
                        $liked_value++;
                    }
                    $review->setReviewLikes($liked_value);
                    $review->save();
                } catch (Exception $e) {
                    Mage::log($e->getLine() . '-' . $e->getMessage(), null, 'reviewimage.log');
                }
            }
            //     }
        }
    }

    /**
     *
     *     * 没有商品关联的数据提交，可能有sku，也可能没有。
     *  /review/product/save
     */
    public function saveAction()
    {
        if (!$this->_validateFormKey()) {
            $this->_redirectReferer();
            return;
        }
        $session = Mage::getSingleton('core/session');
        if ($data = Mage::getSingleton('review/session')->getFormData(true)) {
            $rating = array();
            if (isset($data['ratings']) && is_array($data['ratings'])) {
                $rating = $data['ratings'];
            }
        } else {
            $data = $this->getRequest()->getPost();
            $rating = $this->getRequest()->getParam('ratings', array());
        }
        $data['review_rating'] = isset($rating[1]) ? $rating[1] : 4;
        foreach (array('title', 'nickname', 'detail') as $param) {
            if (isset($data[$param])) {
                if (stripos($data[$param], 'http') !== false || stripos($data[$param], '<script>') !== false) {
                    $session->addError($this->__('Unable to post the review.'));
                    $this->_redirectReferer();
                    return;
                }
            }
        }
        /*
         * 上传图片A
         */
        $helper = Mage::helper("reviewimage");
        $nums = $helper->maxImages();
        $path = $helper->getImagePath();
        $_data = array();
        foreach ($nums as $n) {
            $field = 'review_image_' . $n;
            $data[$field] = '';
            if (isset($_FILES[$field]['name']) && $_FILES[$field]['name'] != '') {
                try {
                    /* Starting upload */
                    $uploader = new Varien_File_Uploader($field);
                    // Any extention would work
                    $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                    //  $uploader->addValidateCallback('catalog_product_image',Mage::helper('catalog/image'), 'validateUploadFile');
                    //  $uploader->addValidateCallback('size',$this, 'validateMaxSize');
                    $uploader->setAllowRenameFiles(true);
                    // Set the file upload mode
                    // false -> get the file directly in the specified folder
                    // true -> get the file in the product like folders
                    //	(file.jpg will go in something like /media/f/i/file.jpg)
                    $uploader->setFilesDispersion(false);
                    // We set media as the upload dir
                    $_path = Mage::getBaseDir('media') . DS . $path . DS;
                    $result = $uploader->save($_path, $_FILES[$field]['name']);
                    $filename = $result['file'];
                    //this way the name is saved in DB
                    $_data[] = "$path/" . $filename;
                } catch (Exception $e) {
                    Mage::log($e->getMessage(), null, "reviewimage.log");
                }
            }
        }
        /**
         * 整理成abc先后顺序存入数据库
         */
        if (!empty($_data)) {
            foreach ($nums as $n) {
                $field = 'review_image_' . $n;
                if ($_data) {
                    $data[$field] = array_shift($_data);
                } else {
                    break;
                }
            }
        }

        /*
         * 上传Video 默认Thumb
         */
        if (isset($data['review_video_thumb']) && $data['review_video_thumb'] != '') {
            $fileVideoThumb = explode('#', $data['review_video_thumb']);
            $filethumburl = $fileVideoThumb[0]; //video 图片地址
            $ext = strrchr($filethumburl, "."); //图片后缀
            $filethumbname = strtolower($fileVideoThumb[1]); //video Id

            try {
                $__path = 'media' . DS . $path . DS;  // 根目录
                // 下载图片
                $imgUrl = $__path . 'thumb_' . $filethumbname . $ext;
                // 图片存入到media目录
                file_put_contents(basename('/') . $imgUrl, file_get_contents($filethumburl));
                // 存入数据库图片地址
                $data['review_video_thumb'] = 'reviewimages/' . 'thumb_' . $filethumbname . $ext;
                $data['review_video'] = $data['review_video'] . '#' . $fileVideoThumb[1];

            } catch (Exception $e) {
                Mage::log($e->getMessage(), null, "reviewimage.log");
            }
        }

        if ($helper->usePostEnable()) {
            $data['enable'] = 1;
        }

        $data['review_likes'] = 1;

        $session = Mage::getSingleton('core/session');
        /* @var $session Mage_Core_Model_Session */
        $review = Mage::getModel('review/review')->setData($data);
        /* @var $review Mage_Review_Model_Review */
        $validate = $review->validate();
        if ($validate === true) {
            /*** 获取分类id 和 商品id 如果有传递商品信息过来的话 **/
            $productId = (int)$this->getRequest()->getParam('id');
            if (!$productId && isset($data['entity_pk_value'])) $productId = $data['entity_pk_value'];
            $data['review_catalog'] = $catalogId = 2;
            if (isset($data['sku']) && !empty($data['sku'])) {
                $data['sku'] = trim($data['sku']);
                if (!$productId)
                    $productId = Mage::getModel('catalog/product')->getIdBySku($data['sku']);
                if ($productId) {
                    $product = Mage::getModel('catalog/product')->load($productId);
                    if ($product && $product->getId()) {
                        $minCatalogId = $helper->getMinCatalogId();
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
                        $data['review_catalog'] = $catalogId;
                    }
                } else {
                    $productId = $helper->createSampleProduct();
                }
            } else if (!$productId) {
                $productId = $helper->createSampleProduct();
            }
            //  @file_put_contents(dirname(__FILE__).'/aa.txt',print_r($productId,true)."--16935\n",FILE_APPEND);
            /*** 获取分类id 和 商品id 结束 **/
            try {
                $review->setEntityId($review->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE))
                    ->setEntityPkValue($productId)
                    ->setStatusId(Mage_Review_Model_Review::STATUS_PENDING)
                    ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                    ->setStoreId(1)
                    ->setStores(array(0, 1))
                    ->save();

                $reviewId = $review->getId();


                /*
                                    foreach ($rating as $ratingId => $optionId) {
                                        Mage::getModel('rating/rating')
                                            ->setRatingId($ratingId)
                                            ->setReviewId($review->getId())
                                            ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                                            ->addOptionVote($optionId, $productId);
                                    }
                */
                $review->aggregate();
                $session->addSuccess($this->__('Your review has been accepted for moderation.'));
            } catch (Exception $e) {
                Mage::log($e->getLine() . '-' . $e->getMessage(), null, 'reviewimage.log');
                $session->setFormData($data);
                $session->addError($this->__('Unable to post the review.'));
            }
        } else {
            $session->setFormData($data);
            if (is_array($validate)) {
                foreach ($validate as $errorMessage) {
                    $session->addError($errorMessage);
                }
            } else {
                $session->addError($this->__('Unable to post the review.'));
            }
        }
        if ($redirectUrl = Mage::getSingleton('review/session')->getRedirectUrl(true)) {
            $this->_redirectUrl($redirectUrl);
            return;
        }
        $this->_redirectReferer();
    }

    /**
     * /review/product/form
     */
    public function formAction()
    {
        $this->loadLayout();
        $helper = Mage::helper("reviewimage");
        $head = $this->getLayout()->getBlock('head');

        // 获取产品名称
        $productId = $this->getRequest()->getParam('id', false);
        if ($productId) {
            $product = Mage::registry('current_product');
            if (!$product) {
                $product = Mage::getModel('catalog/product')->load($productId);
                Mage::register('current_product', $product);
            }
        }
        $productName = $product->getName();
        $head->setTitle($productName . '  ' . $helper->formTitle());
        $head->setKeywords($helper->formKeywords());
        $head->setDescription($helper->formDescription());
        $this->renderLayout();
    }

    /**
     * /review/product/ajax
     */
    public function ajaxAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }


    public function listAction()
    {
        parent::listAction(); // TODO: Change the autogenerated stub
    }

}
