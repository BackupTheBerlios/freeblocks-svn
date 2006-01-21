<?php

require_once( dirname(__FILE__) . "/../lib/xtpl/xtemplate.class.php");

abstract class Component
{
	protected $_properties= array();

	public function renderComponent()
	{
		$filename= dirname(__FILE__) . '/../components/' . get_class($this) . ".xtpl";
		$xtpl= new XTemplate( $filename );

		$xtpl->parse('main');

		return $xtpl->text('main');
	}

	public function setProperty($name, $val)
	{
		$this->_properties[$name]= $val;
	}

	public function getProperties()
	{
		return $this->_properties;
	}
}

?>