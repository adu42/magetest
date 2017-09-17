<?php
/**
 * Connector for Rural module
 */

class Dock_Dock_Helper_Connector_Dock_Rural extends Mage_Core_Helper_Abstract
{
    /**
     * Module names
     */
    const MODULE_NAME = 'Dock_Rural';
    //const MODULE_SHORT_NAME  = 'rural';
    const HELPER_TEMPLATE_PAGE_HTML_HEADER = 'rural/template_page_html_header';

	/**
	 * Module enabled flag
	 *
	 * @var bool
	 */
	protected $isModEnabled;

	/**
	 * Initialization
	 */
	public function __construct()
	{
		$this->isModEnabled = Mage::helper('core')->isModuleEnabled(self::MODULE_NAME);
	}

	/**
	 * Get array of flags indicating if child blocks of the header (e.g. cart) are displayed inside main menu
	 * If module not enabled, return NULL.
	 *
	 * @return array|NULL
	 */
	public function getIsDisplayedInMenu()
	{
		if($this->isModEnabled)
		{
			return Mage::helper(self::HELPER_TEMPLATE_PAGE_HTML_HEADER)->getIsDisplayedInMenu();
		}
		return NULL;
	}
}
