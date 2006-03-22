<?php

$start_time= microtime(true);

/*
http params:
- page (string)
- edit (1 | 0)
*/

require_once(dirname(__FILE__) . "/config.inc.php");
require_once(dirname(__FILE__) . "/lib/xtpl/xtemplate.class.php");
require_once(dirname(__FILE__) . "/base/Component.php");
require_once(dirname(__FILE__) . "/base/Page.php");
require_once(dirname(__FILE__) . "/base/common.inc.php");


///////////
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

//////////
// load page properties from the xml
$page= new Page();

// Load data layout data from storage class
$storage= new $storage_class( array($CONF['configs']['current'], $_GET['page']) );
$storage->loadData();

foreach( $storage->getPageData() as $name => $val )
{
	$page->setPropertyValue($name, $val);
}



//////////////
// build a list of all available components type
$available_components= array();
$dir= opendir("components");
if( $dir )
{
	while(($fname= readdir($dir)) !== false)
	{
		if( ($fname[0] != '.') && is_dir('components/' . $fname) )
		{
			// load class definition
			require_once("components/{$fname}/{$fname}.php");
			$available_components[]= $fname;
		}
	}
}
/*
//////////////
// build a list of all available datasource type
$available_datasources= array();
$dir= opendir("base/datasources");
if( $dir )
{
	while(($fname= readdir($dir)) !== false)
	{
		if( ($fname[0] != '.') && is_dir('base/datasources/components/' . $fname) )
		{
			// load class definition
			require_once("base/datasources/{$fname}/{$fname}.php");
			$available_datasources[]= $fname;
		}
	}
}
*/

// render page
$xtpl= new XTemplate(dirname(__FILE__) . "/base/edit_mode/templates/main.xtpl");

$css_files= array(
	'base/edit_mode/css/main.css',
	'base/edit_mode/css/edit_datasources.css'
);

$js_files= array(
	'lib/prototype/prototype.js',
	'lib/dragdrop/coordinates.js',
	'lib/dragdrop/dragdrop.js',
	'lib/dragdrop/drag.js',
	'tinymce/jscripts/tiny_mce/tiny_mce.js',
	'lib/moofx/moo.fx.js',
	'lib/moofx/moo.fx.pack.js',


	'base/edit_mode/js/Datasource.js',
	'edit_mode.js',
	'base/Component.js',
	'base/edit_mode/js/tabs.js',
	'base/edit_mode/js/edit_datasources.js'

);

// link component specific css and javascript
foreach($available_components as $comp)
{
	$fname_css= "components/{$comp}/{$comp}.css";
	$fname_js= "components/{$comp}/{$comp}.js";

	if( file_exists($fname_css) ){
		$css_files[]= $fname_css;
	}

	if( file_exists($fname_js) ){
		$js_files[]= $fname_js;
	}
}


foreach($css_files as $url)
{
	$xtpl->assign('URL', $url);
	$xtpl->parse('main.css_include');
}

foreach($js_files as $url)
{
	$xtpl->assign('URL', $url);
	$xtpl->parse('main.js_include');
}

// now create tab for each page in the xml file
foreach($storage->getPagesList() as $p)
{
	$xtpl->assign('URL', 'base/edit_mode/edit_page.php?page=' . $p);
	$xtpl->assign('NAME', $p);
	$xtpl->parse('main.page_tab');
}


$xtpl->parse('main');
$xtpl->out('main');

?>


