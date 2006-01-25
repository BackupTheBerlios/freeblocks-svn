<?php

require_once(dirname(__FILE__) . "/Component.php");

class Page extends BaseComponent
{
	protected $_components= array();


	public function addComponent(Component $comp)
	{
		$this->_components[]= $comp;
	}

	public function getComponents()
	{
		return $this->_components;
	}
}

?>