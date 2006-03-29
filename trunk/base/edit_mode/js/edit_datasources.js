


// called from tabs.js
init_functions["datasource_tab_init"]= function (){

	// initialise page
	buildTree(); // build tree from Datasource object
	initTree();
}


var selected_ds= null;
var IODatasource= [];


function buildTree()
{
	var top_node= $('ds_left_block');

	var toggle_model= $('toggle_model');
	var toggled_content_model= $('toggled_content_model');


	function createItemLink(content_node, item){
		content_node= $(content_node);
		var new_node= document.createElement('A');
		new_node.href="#";
		new_node.innerHTML= item.id;
		new_node._data_items= item.content;
		new_node._id= item.id;
		new_node.onmouseout= function(){
			$('ds_item_buttons').hide();
		};

		new_node.onmouseover= function(){
			var buttons= $('ds_item_buttons');

			$('ds_delete_button').onclick= function(){
				// remove in ds list
				Datasource.removeItem(this.parentNode.id, this._id);

				// move the buttons and hide them
				buttons.hide();
				$('ds_left_block').appendChild(buttons);

				// then remove the html item
				$(this).remove();
				Datasource.save();
			}.bind(this);

			this.insertBefore(buttons, this.firstChild);
			buttons.show();
		};

		content_node.appendChild(new_node);
		Element.show(new_node);
	}

	$H(Datasource.datasources).each(function(pair){

		// create toggle and content node for this category if it does not already exists
		// clone template nodes
		var new_toggle= toggle_model.cloneNode(true);
		var new_content= toggled_content_model.cloneNode(true);
		var tmp;

		new_toggle.innerHTML= pair.key;
		new_toggle.id= "";
		new_content.id= pair.key;

		// create add item link
		tmp= document.createElement('a');
		tmp.href= "#";
		tmp.className= 'ds_add_item';
		tmp.innerHTML= "(Add item)";
		tmp._type= pair.key;
		tmp.onclick= function(event){
			var el= Event.element(event);
			var new_item= Datasource.addItem(el._type);

			// then create the html element in the left menu
			createItemLink(el._type, new_item);
			initTree();
			Datasource.save();
		};

		new_toggle.insertBefore(tmp, new_toggle.firstChild);

		$A(pair.value).each(function(item){
			createItemLink(new_content, item);
		});

		top_node.appendChild(new_toggle);
		top_node.appendChild(new_content);

		Element.removeClassName(new_toggle, 'model');
		Element.removeClassName(new_content, 'model');

	});
}

var already_added_js= [];

function initTree()
{
	var containers= document.getElementsByClassName('toggled_content').each( function(cont){
		var nodes= $A(cont.getElementsByTagName('A')).each(function(el){

			if( already_added_js.indexOf(el.parentNode.id) == -1 ){
				already_added_js.push(el.parentNode.id);
				new Ajax.Request('base/datasources/' + el.parentNode.id + '/script.js?r=' + Math.random(), {
					method: 'get',
					onComplete: function(req){
						eval(req.responseText);
					}

				});
			}

			el.onclick= ds_selectItem;

			if( selected_ds == null )
			{
				ds_selectItem({target: el});
			}
		});
	});
}

function ds_selectItem(event)
{
	var el= Event.element(event);
	var parent= el.parentNode;

	if( selected_ds != null )
	{
		// save content
		if( IODatasource[selected_ds.parentNode.id] ){
			selected_ds._data_items.clear();
			IODatasource[selected_ds.parentNode.id].save(selected_ds._data_items);
			Datasource.save();
		}

		Element.removeClassName(selected_ds, 'selected');
		Element.hide('ds_editor_' + selected_ds.parentNode.id);
	}

	Element.addClassName(el, 'selected');
	selected_ds= el;

	var panel= $('ds_editor_' + parent.id);
	if( panel != null )
	{
		Element.show(panel);

		// then load the data from the clcked item
		if( IODatasource[parent.id] ){
			IODatasource[parent.id].load(el._data_items);
		} else {
			//alert("1: " + parent.id);
		}
	}

	return false;
}



// editors
function ds_loadData()
{

}



