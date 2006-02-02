<?php

$start_time= microtime(true);

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
			require_once("components/" . $fname);
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
	$class_name= $comp_data['type'];
	require_once( dirname(__FILE__) . '/components/' . $class_name . '.php' );
	$c= new $class_name();

	unset($x, $y, $parent, $comp_id);

	$x= $y= '';

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
		}

		$c->setPropertyValue($name, $val);
	}

	$page->addComponent($c);

	switch( $c->getPropertyValue('position') )
	{
	case 'absolute':

		if( $edit_mode )
		{
			$xtpl->concat('ADDED_JS', "
				$('{$comp_id}')._drag_obj= new Draggable('{$comp_id}', {snap: 10});
			");
		}

		$c->setCSSStyle('position', 'absolute');
		$c->setCSSStyle('left', $x);
		$c->setCSSStyle('top', $y);
		$c->setCSSStyle('z-index', 500);

		$xtpl->concat('ADDED_CSS', $c->getCSS());
		$xtpl->concat('BODY', $c->renderComponent());
		break;

	case 'container':
		$xtpl->concat($parent, $c->renderComponent());
		break;
	}

	$comp_class= get_class($c);

	if( $edit_mode )
	{
		$script="
			tmp= new {$comp_class}();
			tmp._div= $('{$comp_id}');
			tmp._div.obj= tmp;
			tmp._div.style.left= '{$x}';
			tmp._div.style.top= '{$y}';
			tmp._div.onclick= function(){ component_clicked(this) };

			tmp._drag_obj= tmp._div._drag_obj;

			handle= document.createElement('<div>');
			handle.className= 'handle';

			Element.setOpacity(handle, 0.1);

			tmp._div.appendChild(handle);

			if( tmp.updateContent == null )
			{
				tmp.updateContent= function(){};
			}
		";

		foreach($c->getProperties() as $name => $prop)
		{
			$script.= "tmp['{$name}']= unescape('{$prop->value}');\n";
		}

		// if the component has children
		// then create them on the js object
		if( $c->hasProperty('_sub') && (count($c->getPropertyValue('_sub')) > 0)  )
		{
			$script.= "tmp._children= new Array();\n";

			foreach( $c->getPropertyValue('_sub') as $child_data )
			{
				$script.="tmp._children.push({";

				foreach( $child_data as $name => $val)
				{
					$script.= $name . ': "' . $val . '",';
				}

				$script.=" _v: null });\n";
			}

		}
	}


	$xtpl->concat('ADDED_JS', $script);
}


$xtpl->concat('TEMPLATE_DIR', $CONF['themes']['base_folder'] . "/{$CONF['themes']['current']}");
$xtpl->concat('TITLE', $page->getPropertyValue('title'));

// add standard header
$xtpl->concat('HEAD', '<script src="lib/behaviour/behaviour.js" type="text/javascript"></script>' . "\n");
$xtpl->concat('HEAD', '<script src="lib/scriptaculous/lib/prototype.js" type="text/javascript"></script>' . "\n");
$xtpl->concat('HEAD', '<script src="lib/scriptaculous/src/scriptaculous.js" type="text/javascript"></script>' . "\n");
$xtpl->concat('HEAD', '<script src="lib/scriptaculous/src/dragdrop.js" type="text/javascript"></script>' . "\n");
$xtpl->concat('HEAD', '<script src="base/Component.js" type="text/javascript"></script>' . "\n");
$xtpl->concat('HEAD', '<script src="override.js" type="text/javascript"></script>' . "\n");
$xtpl->concat('HEAD', '<script language="javascript" type="text/javascript" src="tinymce/jscripts/tiny_mce/tiny_mce.js"></script>' . "\n");


$xtpl->concat('HEAD', '<link rel="stylesheet" href="base.css"></link>' . "\n");

if( $edit_mode )
{
	$xtpl->concat('HEAD', '<link rel="stylesheet" href="edit_mode.css"></link>' . "\n");
}

foreach($available_components as $comp)
{
	$xtpl->concat('HEAD', "<script src=\"components/{$comp}.js\" type=\"text/javascript\"></script>\n");
}

if( $edit_mode )
{
	$xtpl->concat('TITLE', ' (Edit Mode)');
	$xtpl->concat('ADDED_JS', "
		var drag_prop= new Draggable('properties_panel',
			{handle: 'title',
			 change: function(obj){
			 	var now= new Date();
				setCookie('prop_x', obj.element.style.left, new Date(now.getTime() +3600 * 15 * 1000));
				setCookie('prop_y', obj.element.style.top, new Date(now.getTime() +3600 * 15 * 1000));
			 }
			}
		);

		drag_prop.element.style.left= getCookie('prop_x');
		drag_prop.element.style.top= getCookie('prop_y');

	// enumerate all the possible containers on the template

		function initSortable()
		{
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
		}

		initSortable();
	");

// build page bar
	$xtpl->concat('BODY', '
	<div id="toolbar">

		<div class="toolbar_item">
			<label for="page_name">Page name:</label>
			<input id="page_name" type="text" value="' . $_GET['page'] . '" disabled="true"/>
		</div>

		<div class="toolbar_item">
			<label for="page_template">Page template:</label>
			<input id="page_template" type="text" value="' . $page->getPropertyValue('template') . '" disabled="true"/>
		</div>

		<div class="toolbar_item">
			<input id="save_page" type="button" value="Save Page" disabled="true">
		</div>

		<div class="toolbar_item">
			<input id="view_xml" onclick="window.open(\'\')" type="button" value="View saved XML">
		</div>

		<div id="loading_indicator">Loading...
		</div>
	</div>
	');

// build components bar
	$content= '<div id="components_panel">';

	foreach($available_components as $comp)
	{
		$c= new $comp();

		$content.= "<a class='item' href='' onclick='create_" . $comp . "(); return false;' style=\"background: url('base/img/" . call_user_method('getIcon', $comp) . "') no-repeat center center\" ></a>";
		$script= "
			function create_{$comp}(){
				var orig= $('model_{$comp}');
				var new_comp= orig.cloneNode(true);

				var body= document.getElementsByTagName('body').item(0);

				new_comp.id= Component.getUnusedID();

				tmp= new {$comp}();
				tmp._div= new_comp;
				new_comp.obj= tmp;
				tmp._div.style.left= '0';
				tmp._div.style.top= '0';
				tmp._div.onclick= function(){ component_clicked(this) };

				handle= document.createElement('<div>');
				handle.className= 'handle';

				Element.setOpacity(handle, 0.1);

				tmp._div.appendChild(handle);

				if( tmp.updateContent == null )
				{
					tmp.updateContent= function(){};
				}

				tmp._div.style.position= 'absolute';
				tmp._div.style.left= '0';
				tmp._div.style.top= '0';

				tmp['type']= '{$comp}';
				tmp['id']= new_comp.id;

				body.appendChild(tmp._div);
				tmp._drag_obj= new Draggable(tmp._div.id);
				Element.show(new_comp);
			}
		";

		$xtpl->concat('ADDED_JS', $script);
	}

	$content.= '</div>';

	$xtpl->concat('BODY', $content);



// build properties panel

	$xtpl->concat('ADDED_JS', '
		tinyMCE.init({
			mode 		: "textareas",
			theme 		: "advanced",
			language	: "en",
			plugins 	: "advimage",
			external_image_list_url : "img_list.js.php",
			advimage_styles : "float_left;float_right",
			theme_advanced_disable : "formatselect"
		});

		function myCustomFileBrowser(field_name, url, type, win) {
			// Do custom browser logic
			win.document.forms[0].elements[field_name].value = "my browser value";
		}
	');

	// open template for properties
	$prop_xtpl= new XTemplate('base/templates/properties_panel.xtpl');

	foreach($available_components  as $comp)
	{
		$tmp= new $comp();

		foreach($tmp->getProperties() as $prop)
		{

			$prop_xtpl->assign('ID', $comp . '_' . $prop->name);
			$prop_xtpl->assign('DISPLAY_NAME', $prop->dispname);


			switch($prop->type)
			{

			case BaseComponent::TYPE_TEXT:

				if( !isset($prop->params['lines']) || ($prop->params['lines'] == 1) )
				{
					$prop_xtpl->parse('main.component.item.text');
				}
				else
				{
					$prop_xtpl-> assign('LINES', $prop->params['lines']);
					$prop_xtpl->parse('main.component.item.textarea');
				}
				break;

			case BaseComponent::TYPE_SLIDER:
				foreach( $prop->params as $name => $val )
				{
					$prop_xtpl->assign('PARAM_' . strtoupper($name), $val);
				}

				$prop_xtpl->parse('main.component.item.slider');
				break;

			case BaseComponent::TYPE_CHOICE:

				foreach($prop->params['values'] as $val => $label)
				{
					$prop_xtpl->assign('VALUE', $val);
					$prop_xtpl->assign('LABEL', $label);
					$prop_xtpl->parse('main.component.item.choice.option');
				}

				$prop_xtpl->parse('main.component.item.choice');
				break;

			case BaseComponent::TYPE_BOOL:
				$content.= "<label for=\"{$comp}_{$prop->name}\">{$prop->dispname}</label>
							<input id=\"{$comp}_{$prop->name}\" type=\"checkbox\"/>";
				break;


			}

			$prop_xtpl->parse('main.component.item');
		}

		$prop_xtpl->assign('COMP', $comp);
		$prop_xtpl->parse('main.component');
	}

	// parse the main block
	$prop_xtpl->parse('main');

	$xtpl->concat('BODY', trim($prop_xtpl->text('main')));

	$script= 'function hidePropertyPanels(){';
	foreach($available_components as $comp)
	{
		$script.= "$('panel_{$comp}').style.display= \"none\";";
	}
	$script.= '}';

	$xtpl->concat('ADDED_JS', $script);

	// and call it once to hide all panels at start
	$xtpl->concat('ADDED_JS', 'hidePropertyPanels();');
	$xtpl->concat('BODY', '<script src="scripts.js" type="text/javascript"></script>');


	$script_fillProperty= '';
	$script_saveProperty= '';
	foreach($available_components as $comp)
	{
		$tmp= new $comp();

		$script_init_obj= '';

		$script_fillProperty.= "\n{$comp}.prototype.fillPropertyPanel= function(){ ";
		$script_saveProperty.= "\n{$comp}.prototype.savePropertyPanel= function(){ ";

		foreach($tmp->getProperties() as $prop)
		{
			$script_init_obj.= $comp . '.prototype.' . $prop->name . "= '{$prop->value}';";

			switch($prop->type)
			{
			default:
				$script_fillProperty.= "$('{$comp}_{$prop->name}').value= this.{$prop->name} || 'undef';";
				$script_saveProperty.= "this.{$prop->name}= $('{$comp}_{$prop->name}').value;";
				break;

			case Component::TYPE_TEXT:
				$script_fillProperty.= "
					$('{$comp}_{$prop->name}').value= this.{$prop->name} || 'undef';
					tinyMCE.updateContent('{$comp}_{$prop->name}');
				";
				$script_saveProperty.= "
					tinyMCE.triggerSave();
					this.{$prop->name}= $('{$comp}_{$prop->name}').value;
				";
				break;


			case Component::TYPE_SLIDER:
				$script_fillProperty.= "$('{$comp}_{$prop->name}').slider.setValue( this.{$prop->name} || 0 );";
				$script_saveProperty.= "this.{$prop->name}= $('{$comp}_{$prop->name}').slider.values[0];";
				break;

			case Component::TYPE_BOOL:
				$script_fillProperty.= "$('{$comp}_{$prop->name}').checked= (this.{$prop->name} == 'true')?true:false;
				if( $('{$comp}_{$prop->name}').onchange )
				{
					$('{$comp}_{$prop->name}').onchange();
				}";

				$script_saveProperty.= "this.{$prop->name}= ($('{$comp}_{$prop->name}').checked)?'true':'false';";
				break;
			}

		}

		$script_fillProperty.= "};";

		// check if position type changed
		$script_saveProperty.= "
			var curr_pos= Element.getStyle(this._div, 'position');

			switch( this.position )
			{
			case 'container':
				if( curr_pos != 'relative' )
				{
					// find first container and put the component in it
					var cont= document.getElementsByClassName('container')[0];
					this._div.style.position= 'relative';
					this._div.style.left= '0';
					this._div.style.top= '0';
					cont.appendChild(this._div);
					this.parent= cont.id;
					this._drag_obj.destroy();
					initSortable();
				}
				break;

			case 'fixed':
			case 'absolute':
				if( curr_pos != 'absolute' )
				{
					// remove from current container
					this._div.parentNode.removeChild(this._div);

					// then add it as a child of body
					var body= document.getElementsByTagName('body').item(0);
					this._div.style.position= 'absolute';
					this._div.style.left= '0';
					this._div.style.top= '0';

					body.appendChild(this._div);
					this._drag_obj= new Draggable(this._div.id, {snap: 10});
				}
				break;
			}
		};";
	}

	$xtpl->concat('ADDED_JS', $script_fillProperty);
	$xtpl->concat('ADDED_JS', $script_saveProperty);
	$xtpl->concat('ADDED_JS', $script_init_obj);
	$xtpl->concat('BODY', '<script src="callbacks.js" type="text/javascript"></script>' . "\n");
	$xtpl->concat('BODY', '<div id="debug">Generation time: ' . (microtime(true) - $start_time) . '</div>');

	// add model for each component
	foreach($available_components as $comp)
	{
		$tmp= new $comp();
		$tmp->setPropertyValue('id', 'model_' . $comp);
		$tmp->setPropertyValue('display', 'none');
		$xtpl->concat('BODY', $tmp->renderComponent());
		$xtpl->concat('ADDED_JS', 'Element.hide("' . 'model_' . $comp . '");');
	}

	// Store the form sent back to the php to save the page state
	$xtpl->concat('BODY', '
		<form id="savedPage" method="POST" action="save_page.php">
		<input type="hidden" name="Submit" value="1" />
		<input type="hidden" id="old_page_name" name="page" value="' . $_GET['page'] . '" />
		</form>
		<div id="alert_container">
		<div style="display: none" class="error_display"><a class="error_close" href="" onclick="Element.remove(this.parentNode);return false;">Click here to close</a>bla bla</div>
		</div>
		');
}


// render the template
$xtpl->parse('main');

$xtpl->out('main');

?>