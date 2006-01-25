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
$storage= new $storage_class($CONF['configs']['current']);
$storage->loadFile();


$xtpl= $page->getTemplate();

$xtpl->assign('TEMPLATE_DIR', "themes/{$CONF['themes']['current']}");
$xtpl->assign('TITLE', $page->getAttribute('title'));

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

$page->getTemplate()->out('main');

?>