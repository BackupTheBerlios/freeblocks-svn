<?php

require_once(dirname(__FILE__) . '/../../lib/xtpl/xtemplate.class.php');

$xtpl= new XTemplate(dirname(__FILE__) . '/templates/edit_datasources.xtpl');
$xtpl->parse('main');
$xtpl->out('main');

?>