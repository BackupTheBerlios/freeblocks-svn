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
require_once(dirname(__FILE__) . "/base/common.inc.php");


define('DEBUG', 1);


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
		if( ($fname[0] != '.') && is_dir('components/' . $fname) )
		{
			// load class definition
			require_once("components/{$fname}/{$fname}.php");
			$available_components[]= $fname;
		}
	}
}



$page= new Page();

// Load data layout data from storage class
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

// create all the components from loaded data
// and load associated datasources
foreach( $storage->getComponentsData() as $comp_data )
{
	$class_name= $comp_data['type'];
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

			case 'width':
				$width= $val;
				break;

			case 'parent':
				$parent= strtoupper($val);
				break;

			case 'id':
				$comp_id= $val;
				break;

			case 'datasource':
				$datasource= $val;
				break;
			}
		}

		$c->setPropertyValue($name, $val);
	}

	// load datasource
	if( isset($datasource) )
	{
		$ds= $storage->getDatasource($class_name, $datasource);
		$c->setDatasource($ds);
	}

	$page->addComponent($c);


	$comp_class= get_class($c);

	if( $edit_mode )
	{
		$script="
			tmp= new {$comp_class}();
			tmp.setup( $('{$comp_id}') );
			new Effect.Move($('{$comp_id}'), {
				mode: 'absolute',
				x: {$x},
				y: {$y}
			});

		";

		foreach($c->getProperties() as $name => $prop)
		{
			$script.= "tmp['{$name}']= unescape(\"{$prop->value}\");\n";
		}

		$script.= "tmp.updateContent();";

		$xtpl->concat('ADDED_JS', $script);
	}

	switch( $c->getPropertyValue('position') )
	{
	case 'fixed':
		if( $edit_mode )
		{
			$xtpl->concat('ADDED_JS', "
				$('{$comp_id}').obj._drag_obj= new Draggable('{$comp_id}', {handle: 'handle', snap: {$CONF['dragdrop_snap']}});
			");
		}

		if( strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false )
		{
			$c->setCSSStyle('position', 'absolute');
			$c->setCSSStyle('left', "expression( {$x} + ( ignoreMe2 = document.body.scrollLeft ) + 'px' )");
			$c->setCSSStyle('top', "expression( {$y} + ( ignoreMe = document.body.scrollTop ) + 'px' )");
		}
		else
		{
			$c->setCSSStyle('position', 'fixed');
			$c->setCSSStyle('left', $x . 'px');
			$c->setCSSStyle('top', $y . 'px');
		}

		$c->setCSSStyle('z-index', 500);
		$c->setCSSStyle('width', $width . 'px');

		$xtpl->concat('ADDED_CSS', $c->getCSS());
		$xtpl->concat('BODY', $c->renderComponent());
		break;

	case 'absolute':

		if( $edit_mode )
		{
			$xtpl->concat('ADDED_JS', "
				$('{$comp_id}').obj._drag_obj= new Draggable('{$comp_id}', {snap: {$CONF['dragdrop_snap']}, handle: 'handle'});
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
$xtpl->concat('HEAD', '<script type="text/javascript" src="tinymce/jscripts/tiny_mce/tiny_mce.js"></script>' . "\n");


$xtpl->concat('HEAD', '<link rel="stylesheet" href="base.css"></link>' . "\n");

// add component specifis css
foreach($available_components as $comp)
{
	$fname= "components/{$comp}/{$comp}.css";

	if( file_exists($fname) )
	{
		$xtpl->concat('HEAD', "<link rel=\"stylesheet\" href=\"{$fname}\"></link>\n");
	}
}

if( $edit_mode )
{
	$xtpl->concat('HEAD', '<link rel="stylesheet" href="edit_mode.css"></link>' . "\n");

	$xtpl->concat('TITLE', ' (Edit Mode)');

	foreach($available_components as $comp)
	{
		$xtpl->concat('HEAD', "<script src=\"components/{$comp}/{$comp}.js\" type=\"text/javascript\"></script>\n");
	}


// build page bar
	$opt_pages_list= '';
	foreach($storage->getPagesList() as $p)
	{
		$opt_pages_list.= "<option value=\"{$p}\" " . (($p==$_GET['page'])?'selected="true"':'') . ">{$p}</option>";
	}

	$xtpl->concat('BODY', '
	<div id="toolbar">
		<div id="loading_indicator">Loading...
		</div>

		<div class="toolbar_button">
			<input id="show_properties" type="checkbox" checked="1"/>
			<label for="show_properties">Show properties</label>
		</div>

		<div class="toolbar_button">
			<select id="page_select">' . $opt_pages_list . '</select>
		</div>

		<div class="toolbar_button">
			<input id="save_page" type="button" value="Save Page" disabled="true">
		</div>

		<div class="toolbar_button">
			<input id="page_properties" type="button" value="Page properties">
		</div>

		<div class="toolbar_button">
			<input id="view_xml" onclick="window.open(\'\')" type="button" value="View saved XML">
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
				tmp= new {$comp}();
				tmp.setup();
		";

		foreach($c->getProperties() as $name => $prop)
		{
			$script.= "tmp['{$name}']= unescape(\"{$prop->value}\");\n";
		}

		$script.= "
				tmp._drag_obj= new Draggable(tmp._div.id, {handle: 'handle'});
				Effect.Center(tmp._div);
			}
		";

		$xtpl->concat('ADDED_JS', $script);
	}

	$content.= '</div>';

	$xtpl->concat('BODY', $content);



// build properties panel

	// open template for properties
	$prop_xtpl= new XTemplate('base/templates/properties_panel.xtpl');

	foreach( array_merge($available_components, array('Page'))  as $comp)
	{
		$tmp= new $comp();

		// add properties
		foreach($tmp->getProperties() as $prop)
		{
			$prop_xtpl->assign('ID', $comp . '_' . $prop->name);
			$prop_xtpl->assign('DISPLAY_NAME', $prop->dispname);


			switch($prop->type)
			{

			case BaseComponent::TYPE_TEXT:

				if( !isset($prop->params['lines']) || ($prop->params['lines'] == 1) )
				{
					$prop_xtpl->parse('main.component.category.item.text');
				}
				else
				{
					$prop_xtpl-> assign('LINES', $prop->params['lines']);
					$prop_xtpl->parse('main.component.category.item.textarea');
				}
				break;

			case BaseComponent::TYPE_SLIDER:
				foreach( $prop->params as $name => $val )
				{
					$prop_xtpl->assign('PARAM_' . strtoupper($name), $val);
				}

				$prop_xtpl->parse('main.component.category.item.slider');
				break;

			case BaseComponent::TYPE_CHOICE:

				foreach($prop->params['values'] as $val => $label)
				{
					$prop_xtpl->assign('VALUE', $val);
					$prop_xtpl->assign('LABEL', $label);
					$prop_xtpl->parse('main.component.category.item.choice.option');
				}

				$prop_xtpl->parse('main.component.category.item.choice');
				break;

			case BaseComponent::TYPE_BOOL:
				$prop_xtpl->parse('main.component.category.item.bool');
				break;
			}

			$prop_xtpl->parse('main.component.category.item');
		}

		$prop_xtpl->assign('TITLE', $comp);
		$prop_xtpl->assign('COMP', $comp);
		$prop_xtpl->assign('CLASS', 'category');
		$prop_xtpl->parse('main.component.category');

		if( ($comp != 'Page') && $tmp->hasChildrenHandler() )
		{
			$prop_xtpl->assign('CUSTOM_CONTENT', $tmp->getPropertyPanelChildrenHandler());
			$prop_xtpl->parse('main.component.custom');
		}

		$prop_xtpl->parse('main.component');
	}

	// parse the main block
	$prop_xtpl->parse('main');

	$xtpl->concat('BODY', trim($prop_xtpl->text('main')));

	$script= 'function hidePropertyPanels(){';
	foreach(array_merge($available_components, array('Page')) as $comp)
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
	foreach( array_merge($available_components, array('Page')) as $comp)
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
/*
		foreach($tmp->getPropertiesArray() as $name => $prop_arr)
		{
			foreach($prop_arr as $prop_name)
			{
				$script_fillProperty.= "
					if( this._children )
					{
						for(var i= 1;; i++)
						{
							var tmp= $('{$comp}_{$name}_{$prop_name}-' + i);

							// first check if we have a children to fill this line
							if( (i-1) >= this._children.length )
							{
								// we dont, so delete this line
								if( tmp != null )
								{
									{$comp}.multi_{$name}_removeline( $('{$comp}_{$name}_{$prop_name}-' + i) );
								}
								else
								{
									// if we are here this means we have no data and
									// no lines, so just end it
									break;
								}
							}
							else
							{
								// else check if a line exists to put data into
								if( tmp == null )
								{
									{$comp}.multi_{$name}_addline();
									tmp= $('{$comp}_{$name}_{$prop_name}-' + i);
								}

								//tmp.value= this._children[i-1].{$prop_name};
								FormElement.setValue(tmp, this._children[i-1].{$prop_name});
								//alert(tmp.id + ' => ' + this._children[i-1].{$prop_name});

								if( this.compValueChanged )
								{
									this.compValueChanged(tmp);
								}
							}
						}
					}
				";




				$script_saveProperty.= "
					for(var i= 1;; i++)
					{
						var tmp= $('{$comp}_{$name}_{$prop_name}-' + i);
						if( tmp != null )
						{
							if( this._children == null )
							{
								this._children= new Array();
							}

							if( (i-1) >= this._children.length )
							{
								this._children[i-1]= {tagName: '{$name}' };
							}

							this._children[i-1].{$prop_name}= FormElement.getValue(tmp);
						}
						else
						{
							break;
						}
					}
				";
			}
		}
*/
		$script_fillProperty.= "};";

		if( $comp != 'Page' )
		{
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
					if( curr_pos != this.position )
					{
						// remove from current container
						this._div.parentNode.removeChild(this._div);

						// then add it as a child of body
						var body= document.getElementsByTagName('body').item(0);
						this._div.style.position= this.position;
						this._div.style.left= '0';
						this._div.style.top= '0';

						body.appendChild(this._div);
						this._drag_obj= new Draggable(this._div.id, {snap: {$CONF['dragdrop_snap']}, handle: 'handle'});
					}
					break;
				}";
		}

		$script_saveProperty.= '};';
	}

	$xtpl->concat('ADDED_JS', $script_fillProperty);
	$xtpl->concat('ADDED_JS', $script_saveProperty);
	$xtpl->concat('ADDED_JS', $script_init_obj);
	$xtpl->concat('BODY', '<script src="callbacks.js" type="text/javascript"></script>' . "\n");
	$xtpl->concat('BODY', '<div id="debug">Generation time: ' . (microtime(true) - $start_time) . '</div>');

	// init page properties
	$script= '';
	foreach($page->getProperties() as $name => $prop)
	{
		$script.= "page['{$name}']= unescape(\"{$prop->value}\");\n";
	}

	$script.= 'var pages_list= ["' . implode( '","', $storage->getPagesList()) . '"];';


	$xtpl->concat('ADDED_JS', $script);

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

	$xtpl->concat('BODY', '<script type="text/javascript" src="edit_mode.js"></script>' . "\n");
}


// render the template
$xtpl->parse('main');

$xtpl->out('main');

?>