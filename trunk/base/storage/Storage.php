<?php

/**
 * Storage class are used to save and load roperties
 * of each component along with the pages
 *
 */
abstract class Storage
{
	protected $_page_data= array();
	protected $_components_data= array();
	protected $_connec_data= null;

	public function __construct($connec_arr)
	{
		$this->_connec_data= $connec_arr;
	}

	public function getPageData()
	{
		return $this->_page_data;
	}

	public function getComponentsData()
	{
		return $this->_components_data;
	}


	abstract public function loadData();
	abstract public function getPagesList();
	abstract public function savePage($components);

	//abstract function loadDatasource($block_type);
}

?>