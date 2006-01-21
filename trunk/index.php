<?php

/*
http params:
- page
*/

require_once(dirname(__FILE__) . "/config.inc.php");
require_once(dirname(__FILE__) . "/lib/xtpl/xtemplate.class.php");
require_once(dirname(__FILE__) . "/base/Component.php");
require_once(dirname(__FILE__) . "/base/Page.php");



if( !isset($_GET['page']) )
{
	$_GET['page']= 'index';
}


// load the storage class
$storage_class= strtoupper($CONF['storage']['current']) . "Storage";
$storage_class_path= dirname(__FILE__) . "/base/storage/" . $storage_class . ".php";

if( file_exists($storage_class_path) )
{
	require_once($storage_class_path);

	if( !class_exists($storage_class) )
	{
		die("Storage class '{$storage_class}' not found in file '{$storage_class_path}' ");
	}
}
else
{
	die("Storage class file not found: '{$storage_class_path}'");
}


$page= new Page();

// load and parse the config file
$filename= call_user_func(array($storage_class,'getDataPath')) . '/' . $CONF['configs']['current'];

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
					$page->setProperty($name, $val);
				}



				// parse children
				foreach( $node->childNodes as $subnode )
				{
					if( $subnode instanceof DOMElement )
					{
						$class_name= $subnode->getAttribute('type') . 'Component';

						require_once( dirname(__FILE__) . '/components/' . $class_name . '.php' );

						$comp= new $class_name();


						foreach($subnode->attributes as $attr)
						{
							$comp->setProperty($attr->name, $attr->value);
						}

						$page->addComponent($comp);

						switch($subnode->getAttribute('position'))
						{
						case 'absolute':

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


$xtpl= $page->getTemplate();

$xtpl->assign('CSS', "themes/{$CONF['themes']['current']}/style.css");
// render the template
$xtpl->parse('main');

$page->getTemplate()->out('main');

?>