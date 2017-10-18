<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-8-24
 * Time: 10:02
 */
class Ado_Api_Block_Product_Options extends Mage_Core_Block_Template
{
    protected $_optionsViewBlock=null;
    protected $_debug = true;

    protected function _construct()
    {
        parent::_construct();
        $this->addData(array(
            'cache_lifetime'    => null,
        ));
    }

    public function setProduct($product){
        $this->_product = $product;
        return $this;
    }

    public function getProduct(){
       return $this->_product ;
    }

     /**
     * 获取定制属性选项
     * @param null $productId
     * @return string
     */
    public function getOptionsHtml($productId=null){
        $_html='';
        $_product = $this->getProduct();

        if(!$_product && $productId){
            $_product=Mage::getModel('catalog/product')->load($productId);
        }
        if($_product){
            $_options=$_product->getOptions();
            if($_product->getId() && count($_options)>0){
                foreach($_options as $_option){
                    $_html.=''.$this->getOptionsViewBlockHtml($_option).'';
                }
            }
            $_html = $this->removeEvents($_html);
        }
        //$_html=str_replace('name="options','name="option['.$productId.']',$_html);
        return $_html;
    }



    /**
     * 移除绑定的事件
     * @param $html
     * @return mixed
     */
    private function removeEvents($html){
        $events=array(
            ' onclick="opConfig.reloadPrice()"',
            ' onchange="opConfig.reloadPrice()"',
        );
        return str_replace($events,'',$html);
    }

    /**
     * 装配属性选项
     * @param null $productId
     * @return string
     */
    private function getOptionsViewBlockHtml($option){
        if($this->_optionsViewBlock==null){
            $this->_optionsViewBlock = Mage::getBlockSingleton('catalog/product_view_options');
            //$this->_optionsViewBlock->setTemplate('catalog/product/view/options.phtml');
            $this->_optionsViewBlock->addOptionRenderer('select','catalog/product_view_options_type_select','ado_cart/product/view/options/type/select.phtml');
            $this->_optionsViewBlock->addOptionRenderer('text','catalog/product_view_options_type_text','ado_cart/product/view/options/type/text.phtml');
            $this->_optionsViewBlock->addOptionRenderer('file','catalog/product_view_options_type_file','ado_cart/product/view/options/type/file.phtml');
            $this->_optionsViewBlock->addOptionRenderer('date','catalog/product_view_options_type_date','ado_cart/product/view/options/type/date.phtml');
        }
        return $this->_optionsViewBlock->getOptionHtml($option);
    }

    /**
     * ajax更新购物车数据url
     * @param string $route
     * @return mixed
     */
    public function getUpdateUrl($route='mapi/cart/updateCart'){
        return Mage::getSingleton('core/url')->getUrl($route);
    }
}