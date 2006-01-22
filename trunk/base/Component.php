<?php

require_once( dirname(__FILE__) . "/../lib/xtpl/xtemplate.class.php");

abstract class Component
{
	protected $_properties= array();

	/**
	 * template
	 *
	 * @var XTemplate
	 */
	protected $xtpl= null;

	public function __construct()
	{
		$filename= dirname(__FILE__) . '/../components/' . get_class($this) . ".xtpl";
		$this->xtpl= new XTemplate( $filename );
	}

	public function renderComponent()
	{
		$this->xtpl->assign('ID', $this->getProperty('name'));
		$this->xtpl->parse('main');

		return $this->xtpl->text('main');
	}

	public function setProperty($name, $val)
	{
		$this->_properties[$name]= $val;
	}

	public function getProperty($name)
	{
		return isset($this->_properties[$name])?$this->_properties[$name]:null;
	}

	public function getProperties()
	{
		return $this->_properties;
	}
}

?>