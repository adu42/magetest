<?php
/**
 * by@Ado
 * 切换列表页小图色卡块
 */
class Ado_SEO_Block_Catalog_Product_List_Minicolor extends Mage_Core_Block_Template
{
    protected $_product;

    /**
     * 分类里开启才使用，否则不使用
     * 目前都没有图片
     * @param $product
     * @param string $template
     * @return string
     */
    public function getMiniColorHtml($product, $template='catalog/product/list/minicolors.phtml'){
        $catalog = $this->getCurrentCategory();
        if(!$catalog || !$catalog->getColorFilter())return '';
        $this->setTemplate($template);
        $this->setProduct($product);
        return $this->toHtml();
    }

    public function setProduct($product){
        $this->_product = $product;
    }

    public function getProduct(){
        return $this->_product;
    }
    /**
     * 获得图片及其颜色
     * @return array
     */
    public function getColors(){
        $helper = Mage::helper('catalog/image');
        $images = $helper->getColorImages();
        if($color = $this->getCurrentColor() && is_array($images)){
            foreach ($images as &$image){
                $image['selected']=0;
                if($image['title']==$color){
                    $image['selected']=1;
                }
                $image['color'] = strtolower(str_replace(' ','-',$image['title']));
            }
        }
        return $images;
    }

    /**
     * 获得图像链接
     * @param $image
     * @return string
     */
    public function getImageUrl($image){
       return (string)Mage::helper('catalog/image')->init($this->getProduct(), 'image',$image)->resize(300,400) ;
    }

    /**
     * 获得当前分类
     * @return bool|mixed
     */
    public function getCurrentCategory(){
        $current_category =  Mage::registry('current_category');
        if($current_category && $current_category->getId()){
            return $current_category;
        }
        return false;
    }

    /**
     * 缓存
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $id = 0;
        $product =  $this->getProduct();
        if($product && $product->getId())$id = $product->getId();
        return array(
           'BLOCK_TPL',
            Mage::app()->getStore()->getCode(),
            $this->getTemplateFile(),
            'TEMPLATE' => $this->getTemplate(),
            'CURRENT_PRODUCT'=>$id,
        );
    }

    /**
     * 获取当前选中的颜色
     * @return mixed
     */
    public function getCurrentColor(){
        $color = $this->getRequest()->getParam(Ado_SEO_Block_Catalog_Product_List_Colors::COLOR_ATTRIBUTE_CODE,'');
        $color = trim(strtolower($color));
        return $color;
    }



}