<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Regular License.
 * You may not use any part of the code in whole or part in any other software
 * or product or website.
 *
 * @author		Dock
 * @copyright	Copyright (c) 2014 Dock
 * @license		Regular License http://themeforest.net/licenses/regular 
 */

class Dock_Dataporter_Helper_Cfgporter_Data extends Mage_Core_Helper_Abstract
{
	const PRESET_FILE_MAIN_DIR		= 'config';

	/**
	 * Modules associated with package
	 *
	 * @var array
	 */
	protected $_packageModules = array(
		'Dock_Rural' =>
			array('Dock_Rural', 'Dock_Brands', 'Dock_CloudZoom', 'Dock_UltraMegamenu', 'Dock_UltraSlideshow'),
		'Dock_Fortis' =>
			array('Dock_Fortis', 'Dock_Brands', 'Dock_CloudZoom', 'Dock_UltraMegamenu', 'Dock_UltraSlideshow'),
	);

	/**
	 * Human-readable names of modules
	 *
	 * @var array
	 */
	protected $_moduleNames = array(
		'Dock_Rural'			=> 'Rural',
		'Dock_Fortis'			=> 'Fortis',
		'Dock_Brands'			=> 'Brands',
		'Dock_CloudZoom'		=> 'Zoom',
		'Dock_UltraMegamenu'	=> 'Menu',
		'Dock_UltraSlideshow'	=> 'Slideshow',
	);

	/**
	 * File path elements
	 *
	 * @var string
	 */
	protected $_presetFileExt = 'xml';
	protected $_presetFileModuleDirType = 'etc';
	protected $_presetFileTmpBaseDir;

	/**
	 * Resource initialization
	 */
	public function __construct()
	{
		$this->_presetFileTmpBaseDir = Mage::getBaseDir('export');
	}

	/**
	 * Get modules associated with package
	 *
	 * @param string
	 * @return array
	 */
	public function getPackageModules($package)
	{
		if (isset($this->_packageModules[$package]))
		{
			return $this->_packageModules[$package];
		}
	}

	/**
	 * Get human-readable names of modules
	 *
	 * @return array
	 */
	public function getModuleNames()
	{
		return $this->_moduleNames;
	}

	/**
	 * Get human-readable name of a module
	 *
	 * @param string
	 * @return string
	 */
	public function getModuleName($module)
	{
		if (isset($this->_moduleNames[$module]))
		{
			return $this->_moduleNames[$module];
		}
	}

	/**
	 * Determines and returns file path of the config preset file
	 *
	 * @param string
	 * @param string
	 * @return string
	 */
	public function getPresetFilepath($filename, $modulename)
	{
		$baseDir = $this->getPresetDir($modulename);
		return $baseDir . DS . $filename . '.' . $this->_presetFileExt;
	}

	/**
	 * Determines and returns path of the directory with config preset files
	 *
	 * @param string
	 * @return string
	 */
	public function getPresetDir($modulename)
	{
		if ($modulename)
		{
			$baseDir = Mage::getConfig()->getModuleDir($this->_presetFileModuleDirType, $modulename);
		}
		else
		{
			$baseDir = $this->_presetFileTmpBaseDir . DS . Dock_Dataporter_Helper_Data::FILE_TOP_LEVEL_DIR;
		}

		return $baseDir . DS . Dock_Dataporter_Helper_Data::FILE_MAIN_DIR . DS . self::PRESET_FILE_MAIN_DIR;
	}
}
