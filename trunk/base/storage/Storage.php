<?php

/**
 * Storage class are used to save and load roperties
 * of each component along with the pages
 *
 */
abstract class Storage
{
	static function getDataPath()
	{
		return '';
	}



	protected $_components= array();

	public function __construct()
	{

	}

	abstract public function saveComponent(Component $c);

	abstract public function loadComponent(Component $c);


	/**
	 * return component list
	 * @return array
	 */
	abstract public function getComponents();
}

?>