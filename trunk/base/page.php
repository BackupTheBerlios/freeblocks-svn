<?php

require_once(dirname(__FILE__) . "/Component.php");

class Page extends BaseComponent
{
	protected $_components= array();

	public function __construct()
	{
		$this->addProperty('name', 'Name', BaseComponent::TYPE_TEXT);
		$this->addProperty('template', 'Template', BaseComponent::TYPE_TEXT, 'default.xtpl');
	}


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