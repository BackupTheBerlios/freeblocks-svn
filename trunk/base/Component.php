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
	private $_properties= array();

	// type					// params
	const TYPE_HIDDEN= 0;
	const TYPE_TEXT= 1;		// lines: 1...x
	const TYPE_SLIDER= 2;	// min: minimum, max: maximum
	const TYPE_CHOICE= 3;	// values: list of possible values ( val => disp )
	const TYPE_BOOL=4;		// inverse: true become false


	function addProperty($name, $dispname, $type, $value= "", $param= "")
	{
		$this->_properties[$name]= new Property($name, $dispname, $type, $value, $param);
	}

	function addPropertyBefore($target, $name, $dispname, $type, $value= "", $param= "")
	{
		$pos= 0;
		$new_prop= new Property($name, $dispname, $type, $value, $param);

		foreach($this->_properties as $prop)
		{
			// target is found
			if( $prop->name == $target )
			{
				break;
			}

			$pos++;
		}

		// insert property before
		//this method does not works
		//array_splice($this->_properties, $pos, 0, array($name => $new_prop));

		$first_part= array_splice($this->_properties, 0, $pos);
		$this->_properties= array_merge($first_part, array($name => $new_prop), $this->_properties);
	}

	function addPropertyAfter($target, $name, $dispname, $type, $value= "", $param= "")
	{

	}

	function hasProperty($name)
	{
		return isset($this->_properties[$name]);
	}



	// getters
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
	function setPropertyValue($name, $val)
	{
		if( isset($this->_properties[$name]) )
		{
			$this->_properties[$name]->value= $val;
		}
		else
		{
			// if property doesn't exist create one
			$this->addProperty($name, '', Component::TYPE_HIDDEN, $val);
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

		$this->addProperty('width', 'Width', Component::TYPE_TEXT, 'auto');
		$this->addProperty('position', 'Positionning', Component::TYPE_CHOICE, 'Container', array(
			'values' => array(
				'container' => 'Container',
				'absolute' => 'Absolute',
				'fixed' => 'Fixed')
		));
	}

	abstract public function getIcon();

	public function renderComponent()
	{
		$this->xtpl->assign('ID', $this->getPropertyValue('id'));
		$this->xtpl->parse('main');

		return $this->xtpl->text('main');
	}

	public function addXMLSubNode(DOMElement $node)
	{
		$this->_xml_subnodes[]= $node;
	}

	public function getCSS()
	{
		$ret= '#' . $this->getPropertyValue('id') . "{";

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