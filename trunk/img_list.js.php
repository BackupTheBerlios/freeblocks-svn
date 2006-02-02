var tinyMCEImageList = new Array();


	// Name, URL
	["Logo 1", "logo.jpg"],
	["Logo 2 Over", "logo_over.jpg"]



<?php
	$imgs_list= array();

	// build images list
	$dir= opendir('user_images');
	if( $dir !== false )
	{
		while( ($file= readdir($dir)) !== false )
		{
			// remove . .. .svn
			if( $file[0] != "." )
			{
				$imgs_list['img/' . $file]= $file;
				echo "tinyMCEImageList.push(['{$file}', 'user_images/{$file}']);";
			}
		}

		closedir($dir);
	}
?>

