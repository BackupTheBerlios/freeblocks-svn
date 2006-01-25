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
								$comp['sub']= array();

								// parse children nodes
								foreach( $subnode->childNodes as $prop_node )
								{
									if( $prop_node instanceof DOMElement )
									{
										$sub= array();
										foreach( $prop_node->attributes as $attr )
										{
											$sub[$attr->name]= $attr->value;
										}

										$comp['sub'][]= $sub;
										//$comp->addXMLSubNode($prop_node);
									}
								}


								foreach($subnode->attributes as $attr)
								{
									$comp[$attr->name]= $attr->value;
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

?>