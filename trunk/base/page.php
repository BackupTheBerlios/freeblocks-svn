<?php

require_once(dirname(__FILE__) . "/Component.php");

class Page extends BaseComponent
{
	protected $_components= array();

	/**
	 * Template
	 *
	 * @var XTemplate
	 */
	protected $_template= null;

	public function setTemplate(XTemplate $tpl)
	{
		$this->_template= $tpl;
	}

	/**
	 * return template
	 *
	 * @return XTemplate
	 */
	public function getTemplate()
	{
		return $this->_template;
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