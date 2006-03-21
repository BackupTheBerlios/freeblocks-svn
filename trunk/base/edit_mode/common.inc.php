<?php

function getAvailableDatasourceType()
{
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
					$datasources[strtolower($fname)]= array(new $tmp(), array());
				}
				else
				{
					die("unable to find class: " . $tmp);
				}
			}
		}
	}
}

function getAvailableComponentType()
{

}


?>