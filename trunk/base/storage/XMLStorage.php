<?php

require_once(dirname(__FILE__) . "/Storage.php");


class XMLStorage extends Storage
{
	static function getDataPath()
	{
		return dirname(__FILE__) . '/../../configs/xml';
	}


	public function getComponents()
	{

	}

	public function loadData()
	{
		if( file_exists($filename) )
		{
			$xml= new DOMDocument();
			if( $xml->load($filename) )
			{
				foreach($xml->documentElement->childNodes as $node)
				{
					// load requested page
					if( ($node->nodeName == "page") && ($node->getAttribute('name') == $_GET['page']) )
					{
						// open the template file associated with requested page
						$filename= $CONF['themes']['base_folder'] . '/' . $CONF['themes']['current'] . '/' . $node->getAttribute('template');
						if( !file_exists($filename) )
						{
							$CONF['themes']['current']= 'default';
						}

						$xtpl= new XTemplate($filename);
						$page->setTemplate($xtpl);


						// parse page properties
						foreach( $node->attributes as $attr)
						{
							$name= $attr->name;
							$val= $attr->value;
							$page->setAttribute($name, $val);
						}



						// parse children
						foreach( $node->childNodes as $subnode )
						{
							if( $subnode instanceof DOMElement )
							{
								$class_name= $subnode->getAttribute('type') . 'Component';

								require_once( dirname(__FILE__) . '/components/' . $class_name . '.php' );

								$comp= new $class_name();

								// parse children nodes
								foreach( $subnode->childNodes as $prop_node )
								{
									if( $prop_node instanceof DOMElement )
									{
										$comp->addXMLSubNode($prop_node);
									}
								}


								foreach($subnode->attributes as $attr)
								{
									$comp->setAttribute($attr->name, $attr->value);
								}

								$page->addComponent($comp);

								switch($subnode->getAttribute('position'))
								{
								case 'absolute':
									$comp_id= $comp->getAttribute('id');
									$x= $comp->getAttribute('x');
									$y= $comp->getAttribute('y');

									if( $edit_mode )
									{
										$xtpl->assign('ADDED_JS', "
											new Draggable('{$comp_id}', {snap: 10});
										");
									}

									$comp->setCSSStyle('position', 'absolute');
									$comp->setCSSStyle('left', $x);
									$comp->setCSSStyle('top', $y);
									$comp->setCSSStyle('z-index', 500);

									$page->getTemplate()->assign('ADDED_CSS', $comp->getCSS());
									$page->getTemplate()->assign('BODY', $comp->renderComponent());
									break;

								case 'container':
									$parent= strtoupper($subnode->getAttribute('parent'));
									$page->getTemplate()->assign($parent, $comp->renderComponent());
									break;
								}
							}
						}
					}
				}
			}
		}
	}
}

?>