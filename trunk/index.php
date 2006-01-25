<?php

/*
http params:
- page (string)
- edit (1 | 0)
*/

require_once(dirname(__FILE__) . "/config.inc.php");
require_once(dirname(__FILE__) . "/lib/xtpl/xtemplate.class.php");
require_once(dirname(__FILE__) . "/base/Component.php");
require_once(dirname(__FILE__) . "/base/Page.php");



if( !isset($_GET['page']) )
{
	$_GET['page']= 'index';
}

$edit_mode= (isset($_GET['edit']) && ($_GET['edit'] == '1'));


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





// build a list of all available components
$available_components= array();
$dir= opendir("components");
if( $dir )
{
	while(($fname= readdir($dir)) !== false)
	{
		if( !in_array($fname, array(".", "..")) && ereg("([^.]+)\.php", $fname, $parts) )
		{
			// load class definition
			include_once("components/" . $fname);
			$available_components[]= $parts[1];
		}
	}
}



$page= new Page();

// Load data from storage class
$storage= new $storage_class( array($CONF['configs']['current'], $_GET['page']) );
$storage->loadData();

foreach( $storage->getPageData() as $name => $val )
{
	$page->setPropertyValue($name, $val);
}

// open the template file associated with requested page
$filename= $CONF['themes']['base_folder'] . '/' . $CONF['themes']['current'] . '/' . $page->getPropertyValue('template');
if( !file_exists($filename) )
{
	$CONF['themes']['current']= 'default';
	$filename= $CONF['themes']['base_folder'] . '/' . $CONF['themes']['current'] . '/' . $page->getPropertyValue('template');
}

$xtpl= new XTemplate($filename);

foreach( $storage->getComponentsData() as $comp_data )
{
	$class_name= $comp_data['type'] . 'Component';
	require_once( dirname(__FILE__) . '/components/' . $class_name . '.php' );
	$c= new $class_name();

	foreach( $comp_data as $name => $val )
	{
		if( !in_array($name, array('type')) )
		{
			switch($name)
			{
			case 'x':
				$x= $val;
				break;

			case 'y':
				$y= $val;
				break;

			case 'parent':
				$parent= strtoupper($val);
				break;

			case 'id':
				$comp_id= $val;
				break;
			}

			$c->setPropertyValue($name, $val);
		}
	}

	$page->addComponent($c);

	switch( $c->getPropertyValue('position') )
	{
	case 'absolute':

		if( $edit_mode )
		{
			$xtpl->assign('ADDED_JS', "
				new Draggable('{$comp_id}', {snap: 10});
			");
		}

		$c->setCSSStyle('position', 'absolute');
		$c->setCSSStyle('left', $x);
		$c->setCSSStyle('top', $y);
		$c->setCSSStyle('z-index', 500);

		$xtpl->assign('ADDED_CSS', $c->getCSS());
		$xtpl->assign('BODY', $c->renderComponent());
		break;

	case 'container':
		$xtpl->assign($parent, $c->renderComponent());
		break;
	}
}


$xtpl->assign('TEMPLATE_DIR', "themes/{$CONF['themes']['current']}");
$xtpl->assign('TITLE', $page->getPropertyValue('title'));

if( $edit_mode )
{
	$xtpl->assign('TITLE', ' (Edit Mode)');
	$xtpl->assign('ADDED_JS', "
		new Draggable('properties_panel',
			{handle: 'title',
			 change: function(obj){
			 	var now= new Date();
				setCookie('prop_x', obj.element.style.left, new Date(now.getTime() +3600 * 15 * 1000));
				setCookie('prop_y', obj.element.style.top, new Date(now.getTime() +3600 * 15 * 1000));
			 }
			}
		);");

	// enumerate all the possible containers on the template
	$xtpl->assign('ADDED_JS', "
		var containers= new Array();
		var nodes= document.getElementsByClassName('container');
		for(var i= 0; i< nodes.length; i++){
			containers.push( nodes[i].id );

		}

		for(var i=0; i< containers.length; i++){
			Sortable.create(containers[i], {
				tag: 'div',
				hoverclass: 'hover',
				constraint: false,
				dropOnEmpty: true,
				containment: containers
			})
		}
	");


// build properties panel
	$content= "
		<div id=\"properties_panel\">
			<div class=\"title\">Properties</div>
			<div class=\"body\">";

	foreach($available_components  as $comp)
	{
		$content.= "<div id=\"panel_{$comp}\" class=\"prop_panel\">
					<div class=\"category\">
						<div class=\"title\">{$comp}</div>";

		$tmp= new $comp();

		foreach($tmp->getProperties() as $prop)
		{
			$content.= "<div class=\"item\">";


			switch($prop->type)
			{
			default:
				$content.= "<label for=\"{$comp}_{$prop->name}\">{$prop->dispname}</label><input class=\"prop\" id=\"{$comp}_{$prop->name}\" type=\"text\"/>";
				break;

			case BaseComponent::TYPE_TEXT:
				$content.= "<label for=\"{$comp}_{$prop->name}\">{$prop->dispname}</label>";
				if( !isset($prop->params['lines']) || ($prop->params['lines'] == 1) )
				{
					$content.= "<input class=\"prop\" id=\"{$comp}_{$prop->name}\" type=\"text\"/>";
				}
				else
				{
					$content.= "<textarea class=\"prop\" id=\"{$comp}_{$prop->name}\" rows=\"{$prop->params['lines']}\"></textarea>";
				}
				break;

			case BaseComponent::TYPE_SLIDER:
				$content.= "<table width=\"95%\" style=\"margin:0;padding:0;\">
							<tr>
							<td width=\"2px\">
							<label for=\"{$comp}_{$prop->name}\">{$prop->dispname}</label>
							</td>
							<td>
							<div id=\"{$comp}_{$prop->name}\" class=\"slider\">
								<div id=\"handle_{$comp}_{$prop->name}\" class=\"slider_handle\"></div>
							</div>
							</td>
							</tr>
							</table>
							<script>
								$('{$comp}_{$prop->name}').slider= new Control.Slider('handle_{$comp}_{$prop->name}', '{$comp}_{$prop->name}',
								{minimum: {$prop->params['min']},
								 maximum: {$prop->params['max']}
								});

							</script>";
				break;

			case BaseComponent::TYPE_CHOICE:
				$content.= "<label for=\"{$comp}_{$prop->name}\">{$prop->dispname}</label>
							<select id=\"{$comp}_{$prop->name}\" >";

				foreach($prop->params['values'] as $val => $label)
				{
					$content.= "<option value=\"{$val}\">{$label}</option>";
				}

				$content.= "</select>";
				break;

			case BaseComponent::TYPE_BOOL:
				$content.= "<label for=\"{$comp}_{$prop->name}\">{$prop->dispname}</label>
							<input id=\"{$comp}_{$prop->name}\" type=\"checkbox\"/>";
				break;


			}

			$content.= "</div>";
		}

		$content.= "</div></div>";
	}

	$content.= "
		<input id=\"save_page\" type=\"button\" value=\"Save Page\">
		<input id=\"apply_properties\" type=\"button\" value=\"Apply Items Properties\">
		<input id=\"delete_component\" type=\"button\" value=\"Remove component\">
			</div>
		</div>";

	$xtpl->assign('BODY', $content);

	$script= 'function hidePropertyPanels(){';
	foreach($available_components as $comp)
	{
		$script.= "$('panel_{$comp}').style.display= \"none\";";
	}
	$script.= '}';

	$xtpl->assign('ADDED_JS', $script);
	// and call it once to hide all panels at start
	$xtpl->assign('ADDED_JS', 'hidePropertyPanels();');
}



// render the template
$xtpl->parse('main');

$xtpl->out('main');

?>