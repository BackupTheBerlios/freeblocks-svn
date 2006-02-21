<?php

require_once(dirname(__FILE__) . "/Datasource.php");

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

	/**
	 * Contains the page requested
	 * for the operation
	 *
	 * @var text
	 */
	protected $_current_page;

	/**
	 * Pages list, filled by loadData
	 *
	 * @var array
	 */
	protected $_pages_list= array();

	/**
	 * Datasources
	 *
	 * @var array
	 */
	protected $_data_sources= array();

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

	public function getDatasources()
	{
		return $this->_data_sources;
	}


	abstract public function loadData();
	abstract public function getPagesList();
	abstract public function savePage($components);

	abstract function getDatasource($block_type, $block_name);
}

?>