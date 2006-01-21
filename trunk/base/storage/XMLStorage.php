<?php

require_once(dirname(__FILE__) . "/Storage.php");


class XMLStorage extends Storage
{
	static function getDataPath()
	{
		return dirname(__FILE__) . '/../../configs/xml';
	}

	public function saveComponent(Component $c)
	{

	}

	public function loadComponent(Component $c)
	{

	}

	public function getComponents()
	{

	}
}

?>