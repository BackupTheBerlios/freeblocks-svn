<?php

require_once(dirname(__FILE__) . '/../../../lib/xtpl/xtemplate.class.php');
require_once(dirname(__FILE__) . '/../../Datasource.php');

class TextDatasource extends Datasource
{
	function getEditor()
	{
		$xtpl= new XTemplate(dirname(__FILE__) . '/editor.xtpl');
		$xtpl->parse('main');

		return $xtpl->text('main');
	}
}

?>

