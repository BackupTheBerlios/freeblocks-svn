<?php

require_once(dirname(__FILE__) . '/../../lib/xtpl/xtemplate.class.php');
require_once(dirname(__FILE__) . "/../../config.inc.php");
require_once(dirname(__FILE__) . "/../common.inc.php");


// list available datasource type
$datasources= array();

$dir_path= dirname(__FILE__) . '/../datasources/';
$dir= opendir($dir_path);
if( $dir )
{
	while(($fname= readdir($dir)) !== false)
	{
		if( ($fname[0] != '.') && is_dir($dir_path . $fname) )
		{
			// load class definition
			require_once($dir_path . "{$fname}/{$fname}Datasource.php");
			$tmp= $fname . 'Datasource';

			if( class_exists($tmp) )
			{
				$datasources[$fname]= array(new $tmp(), array());
			}
			else
			{
				die("unable to find class: " . $tmp);
			}
		}
	}
}


// load datasource list
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


$storage= new $storage_class( array($CONF['configs']['current'], $_GET['page']) );
$storage->loadData();

foreach( $storage->getDatasources() as $type_name => $ds_list )
{
	foreach($ds_list as $ds)
	{
		if( isset($datasources[$type_name]) )
		{
			$datasources[$type_name][1][]= $ds->id;
		}
	}
}



$xtpl= new XTemplate(dirname(__FILE__) . '/templates/edit_datasources.xtpl');

foreach($datasources as $name => $arr)
{
	$ds= $arr[0];
	$data= $arr[1];

/*
	foreach($data as $id)
	{
		$xtpl->assign('NAME', $id);
		$xtpl->parse('main.category.item');
	}
*/
	$xtpl->assign('ID', 'ds_editor_' . $name);
	$xtpl->assign('CONTENT', $ds->getEditor());
	$xtpl->parse('main.editor_div');
/*
	$xtpl->assign('NAME', $name);
	//$xtpl->assign('ID', get_class($ds));
	$xtpl->parse('main.category');
	*/
}

$xtpl->parse('main');
$xtpl->out('main');

?>