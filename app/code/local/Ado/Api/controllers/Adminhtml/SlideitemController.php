<?php

class Ado_Api_Adminhtml_SlideitemController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('mapi/slideitems')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Slide Item Manager'), Mage::helper('adminhtml')->__('Slide Item Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('mapi/slideitem')->load($id);
		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('slideitem_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('mapi/slideitems');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Slide Item Manager'), Mage::helper('adminhtml')->__('Slide Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Slide Item News'), Mage::helper('adminhtml')->__('Slide Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('mapi/adminhtml_slideitem_edit'))
				->_addLeft($this->getLayout()->createBlock('mapi/adminhtml_slideitem_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mapi')->__('Slide Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
			$data= $this->_filterPostData($data);

            $width = $height = null;
            if($slideId = (int)$this->getRequest()->getParam('slide_id')){
                $slide = Mage::getModel('mapi/slide')->load($slideId);
                $width = $slide->getWidth();
                $height = $slide->getHeight();
            }
            $path = Mage::getBaseDir('media') . DS . Ado_Api_Model_Slideitem::SLIDEITEM_MEDIA_PATH . DS;
			if(isset($_FILES['image']['name']) && $_FILES['image']['name'] != '') {
				try {	
					/* Starting upload */	
					$uploader = new Varien_File_Uploader('image');
					
					// Any extention would work
	           		$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
					$uploader->setAllowRenameFiles(true);
					
					// Set the file upload mode 
					// false -> get the file directly in the specified folder
					// true -> get the file in the product like folders 
					//	(file.jpg will go in something like /media/f/i/file.jpg)
					$uploader->setFilesDispersion(false);
							
					// We set media as the upload dir
					
					$result = $uploader->save($path, $_FILES['image']['name']);

					if(file_exists($path. $result['file'])){
                        $imageObj = new Varien_Image($path . $result['file']);
                        $imageObj->constrainOnly(true);
                        $imageObj->keepAspectRatio(true);
                        $imageObj->keepFrame(true);
                        $imageObj->backgroundColor(array(255, 255, 255));
                        $imageObj->resize($width, $height);
                        $imageObj->save($path. $result['file']);
                    }
					
					//this way the name is saved in DB
					$data['image'] = Ado_Api_Model_Slideitem::SLIDEITEM_MEDIA_PATH.'/'. $result['file'];
				} catch (Exception $e) {
		      
		        }
			} else {
				if(isset($data['image']['delete']) && $data['image']['delete'] == 1) {
				   if(file_exists($path.$data['image']['value']))@unlink($path.$data['image']['value']);
				   $data['image'] = '';
				} else {
				   if(empty($data['image']) && empty($data['image']['value'])){
                      $image =  $this->setImageByLinkUrl($data['link_url'],$width,$height,$path);
                      if($image){
                          $data['image'] = Ado_Api_Model_Slideitem::SLIDEITEM_MEDIA_PATH.'/'.ltrim($image,'/');
                      }else{
                          unset($data['image']);
                      }
				   }else{
                       $data['image'] = $data['image']['value'];
                   }
				}
			}
			
			if(isset($data['slide_order'])){ $data['slide_order']= intval($data['slide_order']); }

          //  $path = Mage::getBaseDir('media') . DS . Ado_Api_Model_Slideitem::SLIDEITEM_MEDIA_PATH . DS;
			if(isset($_FILES['thumb_image']['name']) && $_FILES['thumb_image']['name'] != '') {
				try {	
					/* Starting upload */	
					$uploader = new Varien_File_Uploader('thumb_image');
					
					// Any extention would work
	           		$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
					$uploader->setAllowRenameFiles(true);
					
					// Set the file upload mode 
					// false -> get the file directly in the specified folder
					// true -> get the file in the product like folders 
					//	(file.jpg will go in something like /media/f/i/file.jpg)
					$uploader->setFilesDispersion(false);
							
					// We set media as the upload dir
					$result = $uploader->save($path, $_FILES['thumb_image']['name'] );

                    if(file_exists($path. $result['file'])) {
                        $imageObj = new Varien_Image($path . $result['file']);
                        $imageObj->constrainOnly(true);
                        $imageObj->keepAspectRatio(true);
                        $imageObj->keepFrame(true);
                        $imageObj->backgroundColor(array(255, 255, 255));
                        $imageObj->resize($width, $height);
                        $imageObj->save($path. $result['file']);
                    }
					
					//this way the name is saved in DB
					$data['thumb_image'] = Ado_Api_Model_Slideitem::SLIDEITEM_MEDIA_PATH.'/'. $result['file'];
				} catch (Exception $e) {
		      
		        }
	        
			} else {
				if(isset($data['thumb_image']['delete']) && $data['thumb_image']['delete'] == 1) {
					 $data['thumb_image'] = '';
				} else {
					unset($data['thumb_image']);
				}
			}
	  			
	  			
			//$model = Mage::getModel('mapi/slideitem');
			//$model->setData($data)
			//	->setId($this->getRequest()->getParam('id'));
			$model = Mage::getModel('mapi/slideitem');

			$model->setData($data);
			if($this->getRequest()->getParam('id')){
				$model->setId($this->getRequest()->getParam('id'));
			}
			//exit;
			try {
				if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
					$model->setCreatedTime(now())
						->setUpdateTime(now());
				} else {
					$model->setUpdateTime(now());
				}
				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('mapi')->__('Item was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mapi')->__('Unable to find slide item to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('mapi/slideitem');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $slideIds = $this->getRequest()->getParam('slideitem');
        if(!is_array($slideIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select slide item(s)'));
        } else {
            try {
                foreach ($slideIds as $slideId) {
                    $slide = Mage::getModel('mapi/slideitem')->load($slideId);
                    $slide->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($slideIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
    public function massStatusAction()
    {
        $slideIds = $this->getRequest()->getParam('slideitem');
        if(!is_array($slideIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select slide item(s)'));
        } else {
            try {
                foreach ($slideIds as $slideId) {
                    $slide = Mage::getSingleton('mapi/slideitem')
                        ->load($slideId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($slideIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    
    public function setOrderAction() {
        $params = $this->getRequest()->getParam('items');
        //var_dump($params);exit;
        if(!$params) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
            	$params = explode('|',$params);
                foreach ($params as $param) {
					$param = explode('-',$param);
					if(sizeof($param)>1){
						$model = Mage::getModel('mapi/slideitem');
						$model->setData(array('slide_order'=>$param[1]))->setId($param[0]);
						$model->save();
					}
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($params)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }   
    
    public function exportCsvAction()
    {
        $fileName   = 'slide.csv';
        $content    = $this->getLayout()->createBlock('mapi/adminhtml_slideitem_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'slide.xml';
        $content    = $this->getLayout()->createBlock('mapi/adminhtml_slideitem_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }

    protected function _filterPostData($data)
    {
		//var_dump($data);die;
		$data = $this->_filterDateTime($data, array('item_active_from'));
		$data =	$this->_filterDateTime($data, array('item_active_to'));	
        return $data;
    }

    /**
     *  获取商品资料
     *  用商品图片
     *  用link url去获取分类的url
     *
     * @return mixed
     */
    public function setImageByLinkUrl($cantent,$width, $height,$path){
        if(!empty($cantent) && stripos($cantent,'product')!==false){
            $productId = explode(':',$cantent);
            $productId = isset($productId[1])?$productId[1]:0;
            if($productId){
                $product = Mage::getModel('catalog/product')->load($productId);
                $mainImage = '';
                foreach ($product->getMediaGallery('images') as $image) {
                    if ($image['disabled']) {
                        continue;
                    }
                    $mainImage = $product->getMediaConfig()->getMediaPath($image['file']);
                    break;
                }
               if(!empty($mainImage) && file_exists($mainImage)){
                   $imageObj = new Varien_Image($mainImage);
                   $imageObj->constrainOnly(true);
                   $imageObj->keepAspectRatio(true);
                   $imageObj->keepFrame(true);
                   $imageObj->backgroundColor(array(255, 255, 255));
                   $imageObj->resize($width, $height);
                   $imageObj->save($path. $image['file']);
                   return $image['file'];
               }
            }
        }
        return false;
    }

}