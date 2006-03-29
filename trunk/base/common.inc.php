<?php

function out($txt)
{
	$ending= "";

	if( defined('DEBUG') )
	{
		$ending= "<br/>\n";
	}

	echo $txt . $ending;
}

function xml_out($txt)
{
	$ending= "";

	if( defined('DEBUG') )
	{
		$ending= "\n";
	}

	echo $txt . $ending;
}


// some checks
if( get_magic_quotes_gpc() == 1 )
{
	die("You need to disable magic_quotes_gpc to continue");
}

?>