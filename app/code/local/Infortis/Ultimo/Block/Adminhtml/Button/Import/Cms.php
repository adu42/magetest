<?php

class Infortis_Ultimo_Block_Adminhtml_Button_Import_Cms extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Import static blocks
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return String
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
		$elementOriginalData = $element->getOriginalData();
		$actionType = false;
		
		if (isset($elementOriginalData['process']))
		{
			$actionType = $elementOriginalData['process'];
		}
		else
		{
			return '<div>Action was not specified</div>';
		}

		$params = '';
		if (isset($elementOriginalData['params']))
		{
			$params = '/' . $elementOriginalData['params'];
		}

		$buttonLabel = '';
		if (isset($elementOriginalData['button_label']))
		{
			$buttonLabel = $elementOriginalData['button_label'];
		}

		$url = $this->getUrl('adminhtml/cmsimport/' . $actionType . $params);
		
		$html = $this->getLayout()->createBlock('adminhtml/widget_button')
			->setType('button')
			->setClass('import-cms')
			->setLabel($buttonLabel)
			->setOnClick("setLocation('$url')")
			->toHtml();
			
        return $html;
    }
}
