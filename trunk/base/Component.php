<?php

require_once( dirname(__FILE__) . "/../lib/xtpl/xtemplate.class.php");

class Property
{
	public $name;
	public $dispname;
	public $type;
	public $params;
	public $value;

	function __construct($n, $d, $t, $val, $p= array())
	{
		$this->name= $n;
		$this->dispname= $d;
		$this->type= $t;
		$this->params= $p;
		$this->value= $val;
	}
}

abstract class BaseComponent
{
	private $_attributes= array();
	private $_properties= array();

	// type					// params
	const TYPE_TEXT= 1;		// lines: 1...x
	const TYPE_SLIDER= 2;	// min: minimum, max: maximum
	const TYPE_CHOICE= 3;	// values: list of possible values ( val => disp )
	const TYPE_BOOL=4;		// inverse: true become false


	function addProperty($name, $dispname, $type, $value= "", $param= "")
	{
		$this->_properties[$name]= new Property($name, $dispname, $type, $value, $param);
	}



	// getters
	public function getAttribute($name)
	{
		return isset($this->_attributes[$name])?$this->_attributes[$name]:null;
	}

	public function getAttributes()
	{
		return $this->_attributes;
	}

	function getProperties()
	{
		return $this->_properties;
	}

	function getPropertyValue($name)
	{
		$ret= null;

		if( isset($this->_properties[$name]) )
		{
			$ret= $this->_properties[$name]->value;
		}

		return $ret;
	}



	// setters
	public function setAttribute($name, $val)
	{
		$this->_attributes[$name]= $val;
	}

	function setPropertyValue($name, $val)
	{
		if( isset($this->_properties[$name]) )
		{
			$this->_properties[$name]->value= $val;
		}
	}

}


abstract class Component extends BaseComponent
{
	protected $_xml_subnodes= array();
	protected $_css_style= array();

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
		$this->xtpl->assign('ID', $this->getAttribute('id'));
		$this->xtpl->parse('main');

		return $this->xtpl->text('main');
	}

	public function addXMLSubNode(DOMElement $node)
	{
		$this->_xml_subnodes[]= $node;
	}

	public function getCSS()
	{
		$ret= '#' . $this->getAttribute('id') . "{";

		foreach($this->_css_style as $name => $val)
		{
			$ret.= "{$name}: {$val};";
		}

		$ret.= "}";

		return $ret;
	}

	public function getFirstNonEmpty(/* ... */)
	{
		$ret= null;

		for($i= 0; $i< func_num_args() ; $i++)
		{
			$arg= func_get_arg($i);

			if( !empty($arg) )
			{
				$ret= $arg;
				break;
			}
		}

		return $ret;
	}

	public function setCSSStyle($name, $val)
	{
		$this->_css_style[$name]= $val;
	}
}

?>