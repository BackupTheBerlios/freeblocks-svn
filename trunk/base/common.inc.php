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

?>