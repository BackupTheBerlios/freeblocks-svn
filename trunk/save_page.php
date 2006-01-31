<?php

require_once(dirname(__FILE__) . "/config.inc.php");


// save XML page
if( isset($_POST["Submit"]) )
{

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

	$storage= new $storage_class( array($CONF['configs']['current'], $_GET['page']) );

	$lines= join("\n", $_POST['lines']);

	$page_xml= new DOMDocument();
	if( $page_xml->loadXML($lines) )
	{
		$storage->savePage($page_xml->documentElement);
	}



	/*
	$file= fopen($_POST["page"], "w");
	fwrite($file, "<config>\n");
	$lines= $_POST["lines"];

	foreach($lines as $l)
	{
		fwrite($file, stripcslashes($l) . "\n");
	}

	fwrite($file, "</config>\n");
	fclose($file);
	//echo "File saved.<br />\n";
	//echo "<a href=\"edit.php?page={$_GET['page']}\">Return to Editor</a><br />\n";
	//echo "<a href=\"{$_GET['page']}\">Open Generated XML file</a>";
	$success_msg= "XML file saved";
	*/
}

?>
