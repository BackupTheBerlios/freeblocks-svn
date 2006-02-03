<?php

require_once(dirname(__FILE__) . "/config.inc.php");

function encode_str_callback($parts)
{
	return $parts[1] . '="' . rawurlencode($parts[2]) . '"';
}

function encode_str($str)
{
	return preg_replace_callback('/([a-zA-Z]+)\s*=\s*"(.*?)"/', 'encode_str_callback', $str);
}



// save XML page
if( isset($_POST["lines"]) )
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

	$storage= new $storage_class( array($CONF['configs']['current'], $_POST['page']) );

	//$lines= stripcslashes(join("\n", $_POST['lines']));
	$lines= $_POST["lines"];

	if( get_magic_quotes_gpc() )
	{
		$lines= stripcslashes($lines);
	}

	$lines= encode_str($lines);

	header('Content-Type: text/xml');
	echo '<?xml version="1.0" ?>';
	echo '<root>';

	$page_xml= new DOMDocument();
	if( @$page_xml->loadXML($lines) )
	{
		$storage->savePage($page_xml->documentElement);
		echo '<return ret="ok" msg="XML file saved" />';
	}
	else
	{
		echo '<return ret="err" msg="Unable to save XML file"/>';
		$file= fopen("faild_xml.txt", "w");
		if( $file )
		{
			fputs($file, $lines);
			fclose($file);
		}
	}

	echo '</root>';
}

?>
