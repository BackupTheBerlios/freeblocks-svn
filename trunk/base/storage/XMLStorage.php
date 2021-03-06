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


	public function __construct($param= array())
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
					if( $node->nodeName == 'pages' )
					{
						foreach( $node->childNodes as $child )
						{
							if( $child->nodeName == 'page' )
							{
								$this->_pages_list[]= strval( $child->getAttribute('name') );

								// load requested page
								if( $child->getAttribute('name') == $this->_current_page )
								{
									// parse page properties
									foreach( $child->attributes as $attr)
									{
										$name= strval($attr->name);
										$val= strval($attr->value);
										$this->_page_data[$name]= $val;
									}

									foreach( $child->childNodes as $comp_node )
									{
										if( $comp_node->nodeName == 'component' )
										{
											$comp= array();
											foreach($comp_node->attributes as $attr)
											{
												$comp[$attr->name]= rawurldecode($attr->value);
											}

											$this->_components_data[]= $comp;
										}
									}

								}
							}
						}
					}
					// we parse all the datasources we find
					else if( $node->nodeName == 'datasources' )
					{
						foreach( $node->childNodes as $child )
						{
							if( $child->nodeName == 'data' )
							{
								$ds= new DatasourceSruct();
								$ds->id= strval( $child->getAttribute('id') );
								$ds->type= strval( $child->getAttribute('type') );

								if( !isset($this->_data_sources[$ds->type]) )
								{
									$this->_data_sources[$ds->type]= array();
								}

								foreach( $child->childNodes as $ds_node )
								{
									if( $ds_node->nodeName == 'item' )
									{
										$tmp= array();
										foreach( $ds_node->attributes as $attr )
										{
											$name= strval($attr->name);
											$val= strval($attr->value);
											$tmp[$name]= $val;
										}

										$ds->content[]= $tmp;
									}
								}

								$this->_data_sources[$ds->type][$ds->id]= $ds;
							}
						}
					}
				}
			}
		}
	}


	function getDatasource($block_type, $block_name)
	{
		$ret= null;

		$block_type= strtolower($block_type);
		$block_name= strtolower($block_name);

		if( isset($this->_data_sources[$block_type]) )
		{
			$tmp= $this->_data_sources[$block_type];
			if( isset($tmp[$block_name]) )
			{
				$ret= $tmp[$block_name];
			}
		}

		return $ret;
	}


	/**
	 * replace data for current page
	 *
	 * @param mixed $components components array
	 */
	public function saveDatasources($datasources)
	{
		global $CONF;
		$ret= true;


		if( is_writable($this->_filename) )
		{
			$xml= $this->_xml;
			if( $xml->load($this->_filename) )
			{
				// keep a reference on the top node
				$config_node= $xml->documentElement;
				$old_page_node= null;

				// find the datasources node
				$ds_node= $config_node->getElementsByTagName('datasources')->item(0);

				// remove old content
				while($ds_node->hasChildNodes() )
				{
					$ds_node->removeChild( $ds_node->firstChild );
				}

				// and create the new one
				foreach( get_object_vars($datasources) as $ds_type => $ds_list)
				{
					foreach( $ds_list as $ds_object )
					{
						$new_type= $xml->createElement('data');
						$new_type->setAttribute('type', $ds_type);
						$new_type->setAttribute('id', $ds_object->id);

						foreach($ds_object->content as $item)
						{
							$new_item= $xml->createElement('item');

							foreach( get_object_vars($item) as $name => $value )
							{
								$new_item->setAttribute($name, $value);
							}

							$new_type->appendChild($new_item);
						}

						$ds_node->appendChild($new_type);
					}
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
			$xml= $this->_xml;
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