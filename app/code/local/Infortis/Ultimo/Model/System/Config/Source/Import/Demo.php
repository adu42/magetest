<?php

class Infortis_Ultimo_Model_System_Config_Source_Import_Demo
{
	public function toOptionArray()
	{
		$numberOfDemos = 8;
		$array = array();

		for ($i = 1; $i < ($numberOfDemos + 1); $i++)
		{
			$array[] = array('value' => $i, 'label' => 'Demo ' . $i);
		}

		return $array;
	}
}
