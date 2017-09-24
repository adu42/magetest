<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition End User License Agreement
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magento.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright Copyright (c) 2006-2017 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Product options text type block
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Block_Product_View_Options_Type_Select
    extends Mage_Catalog_Block_Product_View_Options_Abstract
{
    protected $_images = null;
    protected $_defaultLabel;
   // protected $_defaultId;

    /**
     * Return html for control element
     * $showType>0 PC
     * @return string
     */
    public function getValuesHtml($showType=0)
    {
        $_option = $this->getOption();
        $configValue = $this->getProduct()->getPreconfiguredValues()->getData('options/' . $_option->getId());
        $store = $this->getProduct()->getStore();

        if ($_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN
            || $_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE) {
            $require = ($_option->getIsRequire()) ? ' required-entry' : '';
            $extraParams = '';
            $hide = '';
            $defaultHide = $this->getDefaultHideClass();
            if($defaultHide==3 && $showType)$hide=' hidden';
            $optionTitle = $this->getflagStr($_option->getTitle());
            $select = $this->getLayout()->createBlock('core/html_select')
                ->setData(array(
                    'id' => 'select_'.$_option->getId(),
                    'class' => $require.' product-custom-option product-custom-option-'.$optionTitle.$hide,
                    'title'=> $optionTitle,
                ));
            if ($_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN) {
                $select->setName('options['.$_option->getid().']')
                    ->addOption('', $this->__('-- Please Select --'));
            } else {
                $select->setName('options['.$_option->getid().'][]');
                $select->setClass('multiselect'.$require.' product-custom-option');
            }
            $optionValues = $_option->getValues();
            $i=0;$defaultSelected = 0;
            /**
             * start
             * pc 0
             * mb 1
             */
            if($showType==1){
                $defaultSelected=0;
            }
            $defaultColor= $this->getCurrentColor();
            foreach ($optionValues as $_value) {
                $selected = false;
                $_valueTitle = $this->getflagStr($_value->getTitle());
                if(empty($defaultColor) && in_array($optionTitle,array('color','colour')) &&  $i===$defaultSelected){
                    $selected = true;
                }else if($defaultColor && $_valueTitle==$defaultColor){
                    $selected = true;
                }
                if($selected){
                    $select->setValue($_value->getOptionTypeId());
                }
                $priceStr = $this->_formatPrice(array(
                    'is_percent'    => ($_value->getPriceType() == 'percent'),
                    'pricing_value' => $_value->getPrice(($_value->getPriceType() == 'percent'))
                ), false);
                $select->addOption(
                    $_value->getOptionTypeId(),
                    $this->__($this->getSizeType($_value->getTitle(),$_value)). ' ' . $priceStr . '',
                    array('price' => $this->helper('core')->currencyByStore($_value->getPrice(true), $store, false),'as'=>$this->getflagStr($_value->getTitle()))
                );
                $i++;
            }
            if ($_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE) {
                $extraParams = ' multiple="multiple"';
            }
            if (!$this->getSkipJsReloadPrice()) {
                $extraParams .= ' onchange="opConfig.reloadPrice()"';
            }
            $select->setExtraParams($extraParams);

            if ($configValue) {
                $select->setValue($configValue);
            }

            $html = $select->getHtml();
            if($showType==1)
            $html .= $this->getColorHtml($_option,$optionValues,$configValue);
            return $html;
        }

        if ($_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_RADIO
            || $_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX
            ) {
            $selectHtml = '<ul id="options-'.$_option->getId().'-list" class="options-list">';
            $require = ($_option->getIsRequire()) ? ' validate-one-required-by-name' : '';
            $arraySign = '';
            switch ($_option->getType()) {
                case Mage_Catalog_Model_Product_Option::OPTION_TYPE_RADIO:
                    $type = 'radio';
                    $class = 'radio';
                    if (!$_option->getIsRequire()) {
                        $selectHtml .= '<li><input type="radio" id="options_' . $_option->getId() . '" class="'
                            . $class . ' product-custom-option" name="options[' . $_option->getId() . ']"'
                            . ($this->getSkipJsReloadPrice() ? '' : ' onclick="opConfig.reloadPrice()"')
                            . ' value="" checked="checked" /><span class="label"><label for="options_'
                            . $_option->getId() . '" as="None">' . $this->__('None') . '</label></span></li>';
                    }
                    break;
                case Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX:
                    $type = 'checkbox';
                    $class = 'checkbox';
                    $arraySign = '[]';
                    break;
            }
            $count = 1;
            foreach ($_option->getValues() as $_value) {
				if(!($_value->getTitle())){
					continue;
				}else{
					$count++;

                $priceStr = $this->_formatPrice(array(
                    'is_percent'    => ($_value->getPriceType() == 'percent'),
                    'pricing_value' => $_value->getPrice($_value->getPriceType() == 'percent')
                ));

                $htmlValue = $_value->getOptionTypeId();
                if ($arraySign) {
                    $checked = (is_array($configValue) && in_array($htmlValue, $configValue)) ? 'checked' : '';
                } else {
                    $checked = $configValue == $htmlValue ? 'checked' : '';
                }

					$selectHtml .= '<li>' . '<input type="' . $type . '" class="' . $class . ' ' . $require
						. ' product-custom-option"'
						. ($this->getSkipJsReloadPrice() ? '' : ' onclick="opConfig.reloadPrice()"')
						. ' name="options[' . $_option->getId() . ']' . $arraySign . '" id="options_' . $_option->getId()
						. '_' . $count . '" value="' . $htmlValue . '" ' . $checked . ' price="'
						. $this->helper('core')->currencyByStore($_value->getPrice(true), $store, false) . '" />'
						. '<span class="label"><label for="options_' . $_option->getId() . '_' . $count . '" as="'.$this->getflagStr($_value->getTitle()).'">'
						. $this->__($this->getSizeType($_value->getTitle(),$_value)) . ' ' . $priceStr . '</label></span>';
					if ($_option->getIsRequire()) {
						$selectHtml .= '<script type="text/javascript">' . '$(\'options_' . $_option->getId() . '_'
						. $count . '\').advaiceContainer = \'options-' . $_option->getId() . '-container\';'
						. '$(\'options_' . $_option->getId() . '_' . $count
						. '\').callbackFunction = \'validateOptionsCallback\';' . '</script>';
					}
					$selectHtml .= '</li>';
				}
				
                
            }
            $selectHtml .= '</ul>';

            return $selectHtml;
        }
    }

    /**
     * by@ado
     *
     * @param $_title
     * @param $_value
     * @return mixed
     */
	protected function getSizeType($_title,$_value){
		    $isSize = false;
			$_title = strtolower(trim($_title));
			if($_title=='size')$isSize = true;
			$value = $_value->getTitle();
			if(!$isSize) return $value;
		return Mage::helper('ado_seo')->getSizeType($_title,$value);
	}


    /**
     * by@ado
     * show as picture
     * colors
     *  as == kvalue
     * @param $optionValues
     * @return string
     */
	protected function getColorHtml($_option,$optionValues,$defaultValue=''){
	    $optionTitle = $_option->getTitle();
        $optionTitle = strtolower($optionTitle);
	    if($optionTitle!='color' && $optionTitle!='colour')return $this->getSelectHtml($_option,$optionValues,$defaultValue);
        $_template = '<ul class="color-chart-box">';
        $fabric = $this->getFabric();
        if(!$fabric)$fabric='Elastic-Woven-Satin';
        $_color_small_image = '../color-chart/'.$fabric.'/%s.jpg'; //$this->getSkinUrl('color-chart/'.$fabric.'/%s.jpg');
        $i=0;
        $defaultColor= $this->getCurrentColor();
        foreach($optionValues as $_value){
            $selected = '';
            $_is_show_as = false;
            $_valueTitle = $this->getflagStr($_value->getTitle());
            if(stripos($_valueTitle,'show')!==false){  //show as picture
                $_small_image = $this->getShowAsPicture($_valueTitle,$i);
                $_is_show_as = true;
            }else{  //
                $_small_image = vsprintf($_color_small_image,array($this->getFileStr($_value->getTitle())));
                $_small_image = $this->getSkinUrl($_small_image);
            }
            if(empty($defaultColor) && $_value->getIsDefault()){
                $selected = ' on';
                $this->setDefaultLabel($_value->getTitle());
            }elseif($defaultColor && $_valueTitle==$defaultColor){
                $selected = ' on';$this->setDefaultLabel($_value->getTitle());
            }
            $_template .= '<li data-optionkey="'.$_option->getId().'" data-optionvalue="'.$_value->getOptionTypeId().'" onclick="doPickColor(this)" id="pis-'.$_valueTitle.'" class="pis-color-a pis-color'.$selected.'" data-color="'.$_valueTitle.'" data-alt="'.$this->__($_value->getTitle()).'" data-label="'.$this->__($_value->getTitle()).'">' ;
           // $_template .= '<dl class="pis-color'.$selected.'">';
            if($fabric || $_is_show_as){
                $_template .= '<div><img src="'.$_small_image.'" width="28" height="28" /><div class="pis-box-img "><img src="'.$_small_image.'" /><p>'.$this->__($_value->getTitle()).'</p></div></div>';
            }else{
                $_template .= '<div><span class="'. $_valueTitle .'" ></span></div>';
            }
         //   $_template .= '<i></i></dl>';
            $_template .= '</li>';
            $i++;
        }
        $_template .= '</ul>' ;
	    return $_template;
    }


    protected function getSelectHtml($_option,$optionValues,$defaultValue=''){
        $optionTitle = $_option->getTitle();
        $optionTitle = strtolower($optionTitle);
        $_template = '<ul class="select-box-'.$optionTitle.'">';
        $i=0;
        $defaultNote = '';
        foreach($optionValues as $_value){
            $selected = '';
            $_valueTitle = $this->getflagStr($_value->getTitle());
            if($defaultValue && ($_valueTitle==$defaultValue || $defaultValue==$_value->getOptionTypeId())){
                $defaultNote = $_value->getNote();
                $selected = ' on';
                $this->setDefaultLabel($_value->getTitle());
            }elseif ($_value->getIsDefault()){
                $defaultNote = $_value->getNote();
                $selected = ' on';
                $this->setDefaultLabel($_value->getTitle());
            }
            $_template .= '<li data-optionkey="'.$_option->getId().'" data-optionvalue="'.$_value->getOptionTypeId().'" onclick="doPickSize(this)" id="pick-'.$_valueTitle.'" class="pick-option pick-a-'.$optionTitle.$selected.'" data-alt="'.$this->escapeHtml($_value->getNote()).'" data-label="'.$this->__($_value->getTitle()).'">' ;
            $_template .= '<span class="'. $_valueTitle .'" >'.$_value->getTitle().'</span>';
            $_template .= '<i></i>';
            $_template .= '</li>';
            $i++;
        }
        $_template .= '</ul>' ;
        $_template .= '<div id="select-box-description-'.$_option->getId().'" class="select-box-description">'.$defaultNote.'</div>' ;
        return $_template;
    }

    /**
     * by@ado
     * //
     * @param $labelTitle
     * @return mixed|string
     */
    private function getFileStr($labelTitle){
        $labelTitle = ucwords($labelTitle);
        $labelTitle = str_replace(array('-',' '),'_',$labelTitle);
        return $labelTitle;
    }

    /**
     * by@ado
     * show as pictureСͼ
     * @param $selectAsPicture
     * @param int $defaultIndex
     * @return string
     */
    private function getShowAsPicture($selectAsPicture,$defaultIndex = 0){
        $imgUrl = '';
        $helper = Mage::helper('catalog/image');
        if(empty($this->_images)){
            $galleryImages = $this->getProduct()->getMediaGalleryImages();
            $images = array();
            $i=0;
            foreach ($galleryImages as $_image){
              //  print_r($_image->getData());die();
                $images[$i]['label'] = $this->getflagStr($_image->getLabel());
                $images[$i]['file']= $_image->getFile();
                $images[$i]['color']= $_image->getColor();
                if((stripos($images[$i]['label'],$selectAsPicture)!==false ||stripos($images[$i]['color'],$selectAsPicture)!==false)&& $images[$i]['file']){
                    try{
                      $image = $helper->init($this->getProduct(), 'small_image',$images[$i]['file'])->resize(160);
                      $imgUrl = $images[$i]['src'] = (string)$image;
                    }catch (Exception $e){
                        $imgUrl='';
                    }
                }
                $i++;
            }
                // if(empty($imgUrl))$imgUrl= isset($this->_images[$defaultIndex])?$this->_images[$defaultIndex]['src']:'';
            $this->_images = $images;
        }


        if(empty($imgUrl) && !empty($this->_images)){
            foreach ($this->_images as $image){
                if(stripos($image['label'],$selectAsPicture)!==false || stripos($image['color'],$selectAsPicture)!==false){
                    try{
                        $image = $helper->init($this->getProduct(), 'small_image',$image['file'])->resize(160);
                        $imgUrl = (string)$image;
                    }catch (Exception $e){
                        $imgUrl='';
                    }
                    break;
                }
            }

            if(empty($imgUrl)){
                if(isset($this->_images[$defaultIndex])){
                    try{
                        $image = $helper->init($this->getProduct(), 'small_image',$this->_images[$defaultIndex]['file'])->resize(160);
                        $imgUrl = (string)$image;
                    }catch (Exception $e){
                        $imgUrl=$this->getSkinUrl('color-chart/show_as_picture.jpg');
                    }
                }else{
                    $imgUrl=$this->getSkinUrl('color-chart/show_as_picture.jpg');
                }
            }
        }
        return $imgUrl;

    }


    public function getCurrentColor(){
        $defaultColor=$this->getRequest()->getParam(Ado_SEO_Block_Catalog_Product_List_Colors::COLOR_ATTRIBUTE_CODE,'');
        return strtolower($defaultColor);
    }

    public function getDefaultLabel(){
        Mage::log($this->_defaultLabel,null,'aa.txt');
        return $this->_defaultLabel;
    }

    public function setDefaultLabel($label){
        $this->_defaultLabel=$label;
        Mage::log($this->_defaultLabel,null,'aa.txt');
        return $this;
    }
}
