<?php

class Infortis_Ultimo_Model_Import_Cms extends Mage_Core_Model_Abstract
{
	const ITEM_TITLE_PREFIX = 'Ultimo ';

	/**
	 * Path to directory with import files
	 *
	 * @var string
	 */
	protected $_basePath;
	
	/**
	 * Create path
	 */
	public function __construct()
	{
		parent::__construct();
		$this->_basePath = Mage::getBaseDir() . '/app/code/local/Infortis/Ultimo/etc/importexport/cms/';
	}
	
	/**
	 * Import CMS items
	 *
	 * @param string model string
	 * @param string name of the main XML node (and name of the XML file)
	 * @param int demo number
	 * @param bool overwrite existing items
	 */
	public function importCmsItems($modelString, $entityName, $demoNumber, $overwrite = false)
	{


		// XML node name for collection of items (e.g. for static blocks $entityName is "block")
		$containerNodeString = $entityName . 's';

		// Determine name and path of the import file
		$xmlFileName = 'demo' . $demoNumber . '.xml';
		$importPath = $this->_basePath . $containerNodeString . '/';
		$xmlFilePath = $importPath . $xmlFileName;

		try
		{
			if (!is_readable($xmlFilePath))
			{
				throw new Exception(
					Mage::helper('ultimo')->__("Can't read data file: %s", $xmlFilePath)
					);
			}
			$xmlObj = new Varien_Simplexml_Config($xmlFilePath);

			// Get a list (hashtable) of items which already exist in the database
			$oldItems = $this->getExistingItemsIds($modelString);

			// Create a list of items which were already imported (during this execution)
			$alreadyImportedItems = array();
			
			$conflictingOldItems = array();
			$i = 0;
			foreach ($xmlObj->getNode($containerNodeString)->children() as $b)
			{
				$newId = (string) $b->identifier;

				// Check if items with the same ID already exists in the database
				if (isset($oldItems[$newId]))
				{
					// Remember this ID
					$conflictingOldItems[] = $newId;

					// If old items can be overwritten
					if ($overwrite)
					{
						// Delete the old items with this ID
						$oldBlocks = Mage::getModel($modelString)->getCollection()
							->addFieldToFilter('identifier', $newId) //array('eq' => $newId)
							->load();
						foreach ($oldBlocks as $old)
						{
							$old->delete();
						}

						// Remove the deleted item from the list
						unset($oldItems[$newId]);
					}
					else
					{
						// Skip this item and don't import it
						continue;
					}
				}

				$newItem = Mage::getModel($modelString)
					->setIdentifier($b->identifier)
					->setTitle($b->title)
					->setIsActive($b->is_active)
					->setRootTemplate($b->root_template)
					->setContent($b->content);

				// Check if items with the same ID was already imported
				if (isset($alreadyImportedItems[$newId]))
				{
					// If yes, don't assign to any store and deactivate
					$newItem->setIsActive(false);

					// Add suffix (version number of the item) to the title
					$newItem->setTitle($b->title . ' (ver ' . ($alreadyImportedItems[$newId] + 1) . ')');
				}
				else
				{
					// If not, assign to all stores
					$newItem->setStores(array(0));
				}

				$newItem->save();

				// Mark the item as already imported. Count how many times item was imported.
				if (!isset($alreadyImportedItems[$newId]))
				{
					$alreadyImportedItems[$newId] = 1;
				}
				else
				{
					$alreadyImportedItems[$newId]++;
				}

				$i++;
			}
			
			// Final info
			if ($i)
			{
				Mage::getSingleton('adminhtml/session')->addSuccess(
					Mage::helper('ultimo')->__('Number of imported items: <strong>%s</strong>. Items with the following identifiers were imported:<br />%s', $i, implode(', ', array_keys($alreadyImportedItems)))
				);
			}
			else
			{
				Mage::getSingleton('adminhtml/session')->addNotice(
					Mage::helper('ultimo')->__('No items were imported.')
				);
			}
			
			if ($overwrite)
			{
				if ($conflictingOldItems)
					Mage::getSingleton('adminhtml/session')->addSuccess(
						Mage::helper('ultimo')
						->__('Items (<strong>%s</strong>) with the following identifiers were overwritten:<br />%s', count($conflictingOldItems), implode(', ', $conflictingOldItems))
					);
			}
			else
			{
				if ($conflictingOldItems)
					Mage::getSingleton('adminhtml/session')->addNotice(
						Mage::helper('ultimo')
						->__('Unable to import items (%s) with the following identifiers (they already exist in the database):<br />%s', count($conflictingOldItems), implode(', ', $conflictingOldItems))
					);
			}
		}
		catch (Exception $e)
		{
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			Mage::logException($e);
		}
	}

	/**
	 * Get identifiers of items which already exist in the database
	 *
	 * @param string model string
	 * @return array
	 */
	protected function getExistingItemsIds($modelString)
	{
		$list = array();

		$itemsCollection = Mage::getModel($modelString)->getCollection()
			->load();

		foreach ($itemsCollection as $item)
		{
			$id = $item->getIdentifier();
			if (!isset($list[$id]))
			{
				$list[$id] = 1;
			}
			else
			{
				$list[$id]++;
			}
		}

		return $list;
	}

	/**
	 * Export
	 *
	 * @param string
	 * @param string
	 * @param int
	 * @param bool
	 *
	 */
	public function exportCmsItems($modelString, $entityName, $storeId = null, $withDefaultStore = true)
	{
	}

}
