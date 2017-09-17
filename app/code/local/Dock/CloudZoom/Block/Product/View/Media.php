<?php

class Dock_CloudZoom_Block_Product_View_Media extends Mage_Catalog_Block_Product_View_Media
{
    public function renderCloudOptions()
    {
        $output = "";
        $width = $this->getCloudConfig('general/big_image_width');
        if (empty($width) || !is_numeric($width)) {
            $width = 'auto';
        }
        $height = $this->getCloudConfig('general/big_image_height');
        if (empty($height) || !is_numeric($height)) {
            $height = 'auto';
        }
        $output .= "zoomWidth: '" . $width . "',";
        $output .= "zoomHeight: '" . $height . "',";
        $output .= "position: '" . $this->getCloudConfig('general/position') . "',";
        $output .= "smoothMove: " . (int) $this->getCloudConfig('general/smooth_move') . ",";
        $output .= "showTitle: " . ($this->getCloudConfig('general/show_title') ? 'true' : 'false') . ",";
        $output .= "titleOpacity: " . (float) ($this->getCloudConfig('general/title_opacity')/100) . ",";

        $adjustX = (int) $this->getCloudConfig('general/adjustX');
        $adjustY = (int) $this->getCloudConfig('general/adjustY');
        if ($adjustX > 0) {
            $output .= "adjustX: " . $adjustX . ",";
        }
        if ($adjustY > 0) {
            $output .= "adjustY: " . $adjustY . ",";
        }

        $output .= "lensOpacity: " . (float) ($this->getCloudConfig('general/lens_opacity')/100) . ",";

        $tint = $this->getCloudConfig('general/tint_color');
        if (!empty($tint)) {
            $output .= "tint: '" . $tint . "',";
        }
        $output .= "tintOpacity: " . (float) ($this->getCloudConfig('general/tint_opacity')/100) . ",";
        $output .= "softFocus: " . ($this->getCloudConfig('general/soft_focus') ? 'true' : 'false') . "";

        return $output;
    }

    public function getCloudConfig($name)
    {
        return Mage::getStoreConfig('cloudzoom/' . $name);
    }

    public function getCloudImage($product, $imageFile=null)
    {
        if ($imageFile !== null) {
            if(is_object($imageFile))$imageFile = $imageFile->getFile();
        }
        $image = $this->helper('catalog/image')->init($product, 'image', $imageFile);

        $width = $this->getCloudConfig('images/main_width');
        $height = $this->getCloudConfig('images/main_height');

        if (!empty($width) && !empty($height)) {
            return $image->resize($width, $height);
        } else if (!empty($width)) {
            return $image->resize($width);
        } else if (!empty($height)) {
            return $image->resize($height);
        }
        return $image;
    }
}
