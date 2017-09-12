<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/2
 * Time: 16:42
 */
require_once(Mage::getModuleDir('controllers', 'Mage_Adminhtml') . DS . 'Catalog/Product/ReviewController.php');

class Kush_Reviewimage_Adminhtml_Catalog_Product_ReviewController extends Mage_Adminhtml_Catalog_Product_ReviewController
{
    public function postAction()
    {
        $productId = $this->getRequest()->getParam('product_id', false);
        $session = Mage::getSingleton('adminhtml/session');

        if ($data = $this->getRequest()->getPost()) {

            /*
         * 上传图片A
         */
            $helper = Mage::helper("reviewimage");
            $nums = $helper->maxImages();
            $path = $helper->getImagePath();
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

                        $result =  $uploader->save($_path, $_FILES[$field]['name']);
                        $filename = $result['file'];
                        //this way the name is saved in DB
                        $data[$field] = "$path/" . $filename;
                    } catch (Exception $e) {
                        Mage::log($e->getMessage(), null, "reviewimage.log");
                    }
                }
            }

                     /**
                     * Video Thumb
                     */   

                    $field_video_thumb = 'review_video_thumb' ;
                    if (isset($data[$field_video_thumb]['delete']) && $data[$field_video_thumb]['delete'] == 1) {
                        //删除已上传的图片
                        $helper->removeFile($data[$field_video_thumb]['value']);
                        $data[$field_video_thumb] = '';
                    }              
                    if (!isset($data[$field_video_thumb])) $data[$field_video_thumb] = '';
                    if (is_array($data[$field_video_thumb])) unset($data[$field_video_thumb]);
                    if (isset($_FILES[$field_video_thumb]['name']) && $_FILES[$field_video_thumb]['name'] != '') {
                        try {
                            /* Starting upload */
                            $uploader = new Varien_File_Uploader($field_video_thumb);
                            // Any extention would work
                            $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                            $uploader->addValidateCallback('catalog_product_image', Mage::helper('catalog/image'), 'validateUploadFile');
                            $uploader->addValidateCallback('size', $this, 'validateMaxSize');
                            $uploader->setAllowRenameFiles(false);
                            // Set the file upload mode
                            // false -> get the file directly in the specified folder
                            // true -> get the file in the product like folders
                            //  (file.jpg will go in something like /media/f/i/file.jpg)
                            $uploader->setFilesDispersion(false);
                            // We set media as the upload dir
                            $_path = Mage::getBaseDir('media') . DS . $path . DS;
                            $uploader->save($_path, $_FILES[$field_video_thumb]['name']);
                            //this way the name is saved in DB
                            $data[$field_video_thumb] = "$path/" . $_FILES[$field_video_thumb]['name'];
                        } catch (Exception $e) {
                            Mage::log($e->getMessage(), null, "reviewimage.log");
                        }
                    }

            
            /**
             * 获得商品分类id
             */
            $arrRatingId = $this->getRequest()->getParam('ratings', array());
            list($catalog_id, $store_id) = $helper->getCatalogId($productId);
            if (!$data['review_catalog']) {
                $data['review_catalog'] = $catalog_id;
            }
            foreach ($arrRatingId as $ratingId => $optionId) {
                if ($optionId) {
                    $option = Mage::getModel('rating/rating_option')->load($optionId);
                    if ($option) {
                        $data['review_rating'] = $option->value;
                    }
                }
            }

            $data['review_rating'] =  (empty($data['review_rating']))?($data['review_rating']?$arrRatingId[1]:5):$data['review_rating'];


            if (Mage::app()->isSingleStoreMode()) {
                $data['stores'] = array(Mage::app()->getStore(true)->getId());
            } else if (isset($data['select_stores'])) {
                $data['stores'] = $data['select_stores'];
            }


            $review = Mage::getModel('review/review')->setData($data);


            // try {
            $review->setEntityId(1)// product
            ->setEntityPkValue($productId)
                ->setStoreId($store_id)
                ->setStatusId($data['status_id'])
                ->setCustomerId(null)//null is for administrator only
                ->save();


            foreach ($arrRatingId as $ratingId => $optionId) {
                Mage::getModel('rating/rating')
                    ->setRatingId($ratingId)
                    ->setReviewId($review->getId())
                    ->addOptionVote($optionId, $productId);
            }

            $review->aggregate();

            $session->addSuccess(Mage::helper('catalog')->__('The review has been saved.'));
            if ($this->getRequest()->getParam('ret') == 'pending') {
                $this->getResponse()->setRedirect($this->getUrl('*/*/pending'));
            } else {
                $this->getResponse()->setRedirect($this->getUrl('*/*/'));
            }

            return;
            /*  } catch (Mage_Core_Exception $e) {
                  $session->addError($e->getMessage());
              } catch (Exception $e) {
                  print_r($e->getMessage());
                  $session->addException($e, Mage::helper('adminhtml')->__('An error occurred while saving review.'));
              } */
        }
        $this->getResponse()->setRedirect($this->getUrl('*/*/'));
        return;
    }


    public function saveAction()
    {
        if (($data = $this->getRequest()->getPost()) && ($reviewId = $this->getRequest()->getParam('id'))) {
            $review = Mage::getModel('review/review')->load($reviewId);
            $session = Mage::getSingleton('adminhtml/session');
            if (!$review->getId()) {
                $session->addError(Mage::helper('catalog')->__('The review was removed by another user or does not exist.'));
            } else {

                /*
                * 上传图片A
                */
                $helper = Mage::helper("reviewimage");
                $nums = $helper->maxImages();
                $path = $helper->getImagePath();
                foreach ($nums as $n) {
                    $field = 'review_image_' . $n;
                    if (isset($data[$field]['delete']) && $data[$field]['delete'] == 1) {
                        //删除已上传的图片
                        $helper->removeFile($data[$field]['value']);
                        $data[$field] = '';
                    }
                    if (!isset($data[$field])) $data[$field] = '';
                    if (is_array($data[$field])) unset($data[$field]);
                    if (isset($_FILES[$field]['name']) && $_FILES[$field]['name'] != '') {
                        try {
                            /* Starting upload */
                            $uploader = new Varien_File_Uploader($field);
                            // Any extention would work
                            $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                            $uploader->addValidateCallback('catalog_product_image', Mage::helper('catalog/image'), 'validateUploadFile');
                            $uploader->addValidateCallback('size', $this, 'validateMaxSize');
                            $uploader->setAllowRenameFiles(false);
                            // Set the file upload mode
                            // false -> get the file directly in the specified folder
                            // true -> get the file in the product like folders
                            //	(file.jpg will go in something like /media/f/i/file.jpg)
                            $uploader->setFilesDispersion(false);
                            // We set media as the upload dir
                            $_path = Mage::getBaseDir('media') . DS . $path . DS;
                            $uploader->save($_path, $_FILES[$field]['name']);
                            //this way the name is saved in DB
                            $data[$field] = "$path/" . $_FILES[$field]['name'];
                        } catch (Exception $e) {
                            Mage::log($e->getMessage(), null, "reviewimage.log");
                        }
                    }
                }

                if(isset($data['review_product']) && !empty($data['review_product'])){
                    $data['review_product']=trim($data['review_product']);
                    if(!is_numeric($data['review_product'])){
                        $data['review_product']=Mage::getModel('catalog/product')->getIdBySku($data['review_product']);
                    }
                    if($data['review_product'] && is_numeric($data['review_product'])){
                        $review->setEntityPkValue($data['review_product']);
                     }
                     
                    // unset($data['review_product']);
                }

                     /**
                     * Video Thumb
                     */   

                    $field_video_thumb = 'review_video_thumb' ;
                    if (isset($data[$field_video_thumb]['delete']) && $data[$field_video_thumb]['delete'] == 1) {
                        //删除已上传的图片
                        $helper->removeFile($data[$field_video_thumb]['value']);
                        $data[$field_video_thumb] = '';
                    }              
                    if (!isset($data[$field_video_thumb])) $data[$field_video_thumb] = '';
                    if (is_array($data[$field_video_thumb])) unset($data[$field_video_thumb]);
                    if (isset($_FILES[$field_video_thumb]['name']) && $_FILES[$field_video_thumb]['name'] != '') {
                        try {
                            /* Starting upload */
                            $uploader = new Varien_File_Uploader($field_video_thumb);
                            // Any extention would work
                            $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                            $uploader->addValidateCallback('catalog_product_image', Mage::helper('catalog/image'), 'validateUploadFile');
                            $uploader->addValidateCallback('size', $this, 'validateMaxSize');
                            $uploader->setAllowRenameFiles(false);
                            // Set the file upload mode
                            // false -> get the file directly in the specified folder
                            // true -> get the file in the product like folders
                            //  (file.jpg will go in something like /media/f/i/file.jpg)
                            $uploader->setFilesDispersion(false);
                            // We set media as the upload dir
                            $_path = Mage::getBaseDir('media') . DS . $path . DS;
                            $uploader->save($_path, $_FILES[$field_video_thumb]['name']);
                            //this way the name is saved in DB
                            $data[$field_video_thumb] = "$path/" . $_FILES[$field_video_thumb]['name'];
                        } catch (Exception $e) {
                            Mage::log($e->getMessage(), null, "reviewimage.log");
                        }
                    }


                /**
                 * 自定义评分
                 */
                $arrRatingId = $this->getRequest()->getParam('ratings', array());
                foreach ($arrRatingId as $ratingId => $optionId) {
                    if ($optionId) {
                        $option = Mage::getModel('rating/rating_option')->load($optionId);
                        if ($option) {
                            $data['review_rating'] = $option->getValue();
                        }
                    }
                }
                $data['review_rating'] =  (empty($data['review_rating']))?($data['review_rating']?$arrRatingId[1]:5):$data['review_rating'];

                list($catalog_id, $store_id) = $helper->getCatalogId($review->getEntityPkValue());
                if (!$data['review_catalog']) {
                    $data['review_catalog'] = $catalog_id;
                }

                try {
                    $review->addData($data)->save();

                    $votes = Mage::getModel('rating/rating_option_vote')
                        ->getResourceCollection()
                        ->setReviewFilter($reviewId)
                        ->addOptionInfo()
                        ->load()
                        ->addRatingOptions();
                    foreach ($arrRatingId as $ratingId => $optionId) {
                        if ($vote = $votes->getItemByColumnValue('rating_id', $ratingId)) {
                            Mage::getModel('rating/rating')
                                ->setVoteId($vote->getId())
                                ->setReviewId($review->getId())
                                ->updateOptionVote($optionId);
                        } else {
                            Mage::getModel('rating/rating')
                                ->setRatingId($ratingId)
                                ->setReviewId($review->getId())
                                ->addOptionVote($optionId, $review->getEntityPkValue());
                        }
                    }

                    $review->aggregate();

                    $session->addSuccess(Mage::helper('catalog')->__('The review has been saved.'));
                } catch (Mage_Core_Exception $e) {
                    $session->addError($e->getMessage());
                } catch (Exception $e) {
                    $session->addException($e, Mage::helper('catalog')->__('An error occurred while saving this review.'));
                }
            }

            return $this->getResponse()->setRedirect($this->getUrl($this->getRequest()->getParam('ret') == 'pending' ? '*/*/pending' : '*/*/'));
        }
        $this->_redirect('*/*/');
    }
}