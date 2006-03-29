<?php

require_once(dirname(__FILE__) . "/../../config.inc.php");
require_once(dirname(__FILE__) . "/../common.inc.php");
require_once(dirname(__FILE__) . "/JSON.php");


$json= new Services_JSON();
$json_obj= $json->decode($_POST['data']);


// load the storage class
$storage_class= strtoupper($CONF['storage']['current']) . "Storage";
$storage_class_path= dirname(__FILE__) . "/../storage/" . $storage_class . ".php";

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

$storage= new $storage_class( array($CONF['configs']['current'], "") );


header('Content-Type: text/xml');
echo '<?xml version="1.0" ?>';
echo '<root>';

$ret= $storage->saveDatasources($json_obj);
if( $ret === true )
{
	echo '<return ret="ok" msg="XML file saved" />';
}
else
{
	echo '<return ret="err" msg="file not writeable" />';
}

echo '</root>';


?>
