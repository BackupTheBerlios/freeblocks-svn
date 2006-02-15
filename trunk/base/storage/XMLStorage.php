<?php

require_once(dirname(__FILE__) . "/Storage.php");


class XMLStorage extends Storage
{
	/**
	 * @var DOMDocument
	 */
	private $_xml;

	/**
	 * XML config file path
	 *
	 * @var text
	 */
	private $_filename;

	/**
	 * Contains the page requested
	 * for the operation
	 *
	 * @var text
	 */
	private $_current_page;

	/**
	 * Pages list, filled by loadData
	 *
	 * @var array
	 */
	private $_pages_list= array();


	public function __construct($param)
	{
		parent::__construct($param);

		$this->_xml= new DOMDocument();
		$this->_filename= dirname(__FILE__) . '/../../configs/xml/' . $this->_connec_data[0];
		$this->_current_page= $this->_connec_data[1];
	}

	public function getPagesList()
	{
		return $this->_pages_list;
	}

	public function loadData()
	{
		if( file_exists($this->_filename) )
		{
			$xml= $this->_xml;

			if( $xml->load($this->_filename) )
			{
				$this->_pages_list= array();

				foreach($xml->documentElement->childNodes as $node)
				{
					// load requested page
					if( ($node->nodeName == "page") )
					{
						$this->_pages_list[]= $node->getAttribute('name');

						if( $node->getAttribute('name') == $this->_current_page )
						{
							// parse page properties
							foreach( $node->attributes as $attr)
							{
								$name= $attr->name;
								$val= $attr->value;
								$this->_page_data[$name]= $val;
							}



							// parse children
							foreach( $node->childNodes as $subnode )
							{
								if( $subnode instanceof DOMElement )
								{
									$comp= array();
									$comp['_sub']= array();

									// parse children nodes
									foreach( $subnode->childNodes as $prop_node )
									{
										if( $prop_node instanceof DOMElement )
										{
											$sub= array();
											foreach( $prop_node->attributes as $attr )
											{
												$sub[$attr->name]= rawurldecode($attr->value);
											}

											$sub['tagName']= $prop_node->nodeName;
											$comp['_sub'][]= $sub;
											//$comp->addXMLSubNode($prop_node);
										}
									}


									foreach($subnode->attributes as $attr)
									{
										$comp[$attr->name]= rawurldecode($attr->value);
										//$comp->setAttribute($attr->name, $attr->value);
									}

									$this->_components_data[]= $comp;
								}
							}
						}
					}
				}
			}
		}
	}

	/**
	 * replace data for current page
	 *
	 * @param mixed $components components array
	 */
	public function savePage($page_node)
	{
		global $CONF;
		$ret= true;

		// first load the xml file in memory
		if( !file_exists($this->_filename) )
		{
			$f= @fopen($this->_filename, "w");
			if( $f === false )
			{
				$ret= false;
			}
			else
			{
				fwrite($f, '
					<?xml version="1.0"?>
					<config>
					</config>
				');

				fclose($f);
			}
		}

		if( $ret && is_writable($this->_filename) )
		{
			$xml= new DOMDocument();
			if( $xml->load($this->_filename) )
			{
				// keep a reference on the top node
				$config_node= $xml->documentElement;
				$old_page_node= null;

				// find the page node
				if( $config_node->hasChildNodes() )
				{
					foreach( $config_node->childNodes as $node )
					{
						if( ($node instanceof DOMElement) && ($node->nodeName == 'page') && ($node->getAttribute('name') == $this->_current_page) )
						{
							// we found it
							$old_page_node= $node;
							break;
						}
					}
				}

				$new_page_node= $xml->importNode($page_node, true);

				// if page exists
				if( $old_page_node !== null )
				{
					$config_node->replaceChild($new_page_node, $old_page_node);
				}
				else
				{
					$config_node->appendChild($new_page_node);
				}

				// save new xml to file
				$xml->save($this->_filename);
			}
		}
		else
		{
			$ret= false;
		}

		return $ret;
	}
}

?>