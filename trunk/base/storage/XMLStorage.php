<?php

require_once(dirname(__FILE__) . "/Storage.php");


class XMLStorage extends Storage
{

	public function loadData()
	{
		global $CONF;

		$filename= dirname(__FILE__) . '/../../configs/xml/' . $this->_connec_data[0];
		if( file_exists($filename) )
		{
			$xml= new DOMDocument();
			if( $xml->load($filename) )
			{
				foreach($xml->documentElement->childNodes as $node)
				{
					// load requested page
					if( ($node->nodeName == "page") && ($node->getAttribute('name') == $this->_connec_data[1]) )
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

	/**
	 * replace data for current page
	 *
	 * @param mixed $components components array
	 */
	public function savePage($page_node)
	{
		global $CONF;

		// first load the xml file in memory
		$filename= dirname(__FILE__) . '/../../configs/xml/' . $this->_connec_data[0];
		if( file_exists($filename) )
		{
			$xml= new DOMDocument();
			if( $xml->load($filename) )
			{
				// keep a reference on the top node
				$config_node= $xml->documentElement;
				$old_page_node= null;

				// find the page node
				foreach( $config_node->childNodes as $node )
				{
					if( ($node instanceof DOMElement) && ($node->nodeName == 'page') && ($node->getAttribute('name') == $this->_connec_data[1]) )
					{
						// we found it
						$old_page_node= $node;
						break;
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
				$xml->save($filename);
			}

		}
	}
}

?>