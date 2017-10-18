<?php

class Ado_Api_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * 获得裁剪图，如果创建过就不再创建
     * image
     */
    public function getImage($columnValue,$width,$height)
    {
        $endPath = Ado_Api_Model_Banneritem::BANNERITEM_MEDIA_PATH . DS;
        $path = Mage::getBaseDir('media'). DS . $endPath;
        $resizedImage = Mage::getBaseUrl("media") . $columnValue;
        if (!empty($columnValue) && $width && $height) {
            $imageName = $columnValue;
            $imageName = str_replace(array($path,$endPath), '', $imageName);
            $imageName = str_replace('//', '/', $imageName);
            // $imageUrl = Mage::getBaseUrl("media") . $this->getImagePath() . $imageName;
            $imagePath = $path. $imageName;

            $imageResized = $path .'resize' . DS . $imageName;
            $resizedImage = Mage::getBaseUrl("media") .$endPath. 'resize' . $imageName;
            $resizedImage = str_replace('\\','/',$resizedImage);
            if (!file_exists($imageResized) && file_exists($imagePath)) {
                try{
                    $imageObj = new Varien_Image($imagePath);
                    $imageObj->constrainOnly(true);
                    $imageObj->keepAspectRatio(true);
                    $imageObj->keepFrame(true);
                    $imageObj->backgroundColor(array(255, 255, 255));
                    $imageObj->resize($width, $height);
                    $imageObj->save($imageResized);
                }catch (Exception $e){
                    Mage::logException($e);
                }
            }
        }
        return $resizedImage;
    }
}