<?php

/**
 * Storage class are used to save and load roperties
 * of each component along with the pages
 *
 */
abstract class Storage
{
	protected $_components_data= array();
	private $_connec_data= array();

	static function getDataPath()
	{
		return '';
	}



	protected $_components= array();

	public function __construct($connec_arr)
	{
		$this->_connec_data= $connec_arr;
	}

	abstract public function loadData();

	abstract public function saveComponent(Component $c);

	abstract public function loadComponent(Component $c);


	/**
	 * return component list
	 * @return array
	 */
	abstract public function getComponents();
}

?>