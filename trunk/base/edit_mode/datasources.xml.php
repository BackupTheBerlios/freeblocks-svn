<?php

require_once(dirname(__FILE__) . "/../config.inc.php");
require_once(dirname(__FILE__) . "/common.inc.php");

// uncomment this line to add
// \n after each line
//define('DEBUG', 1);

// load the storage class
$storage_class= strtoupper($CONF['storage']['current']) . "Storage";
$storage_class_path= dirname(__FILE__) . "/storage/" . $storage_class . ".php";

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


$storage= new $storage_class( array($CONF['configs']['current'], $_GET['page']) );
$storage->loadData();

header('Content-Type: text/xml');
xml_out('<?xml version="1.0" ?>');
xml_out('<root>');

foreach( $storage->getDatasources() as $type_name => $datasources )
{
	foreach($datasources as $ds)
	{
		xml_out("<data type=\"{$type_name}\" id=\"{$ds->id}\">");

		foreach( $ds->content as $line )
		{
			$txt= "<item ";
			foreach( $line as $name => $val )
			{
				$txt.= "{$name}=\"{$val}\" ";
			}

			$txt.= "/>";
			xml_out($txt);
		}

		xml_out("</data>");
	}
}

xml_out('</root>');

?>
