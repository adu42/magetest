<?php

class Infortis_Ultimo_Adminhtml_CmsimportController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
	{
		$this->getResponse()->setRedirect($this->getUrl("adminhtml/system_config/edit/section/ultimo/"));
	}
	
	public function blocksAction()
	{
		$demoNumber = Mage::helper('ultimo')->getCfg('install/demo_number');
		$overwrite = Mage::helper('ultimo')->getCfg('install/overwrite_blocks');
		Mage::getSingleton('ultimo/import_cms')->importCmsItems('cms/block', 'block', $demoNumber, $overwrite);
		
		$this->getResponse()->setRedirect($this->getUrl("adminhtml/system_config/edit/section/ultimo/"));
	}
	
	public function pagesAction()
	{
		$demoNumber = Mage::helper('ultimo')->getCfg('install/demo_number');
		$overwrite = Mage::helper('ultimo')->getCfg('install/overwrite_pages');
		Mage::getSingleton('ultimo/import_cms')->importCmsItems('cms/page', 'page', $demoNumber, $overwrite);
		
		$this->getResponse()->setRedirect($this->getUrl("adminhtml/system_config/edit/section/ultimo/"));
	}

	public function expblocksAction()
	{
		$storeId = null;
		$paramStore = $this->getRequest()->getParam('s');
		if ($paramStore !== null)
		{
			$storeId = $paramStore;
		}

		$withDefaultStore = true;
		$paramWithdefaultstore = $this->getRequest()->getParam('withdefaultstore');  
		if ($paramWithdefaultstore !== null)
		{
			$withDefaultStore = $paramWithdefaultstore;
		}

		Mage::getSingleton('ultimo/import_cms')->exportCmsItems('cms/block', 'block', $storeId, $withDefaultStore);  

		$this->getResponse()->setRedirect($this->getUrl("adminhtml/system_config/edit/section/ultimo/"));
	}
	
	public function exppagesAction()
	{
		$storeId = null;
		$paramStore = $this->getRequest()->getParam('s');
		if ($paramStore !== null)
		{
			$storeId = $paramStore;
		}

		$withDefaultStore = true;
		$paramWithdefaultstore = $this->getRequest()->getParam('withdefaultstore');  
		if ($paramWithdefaultstore !== null)
		{
			$withDefaultStore = $paramWithdefaultstore;
		}

		Mage::getSingleton('ultimo/import_cms')->exportCmsItems('cms/page', 'page', $storeId, $withDefaultStore);  

		$this->getResponse()->setRedirect($this->getUrl("adminhtml/system_config/edit/section/ultimo/"));
	}
}
