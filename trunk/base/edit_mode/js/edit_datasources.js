

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



	$H(Datasource.datasources).each(function(pair){



		// create toggle and content node for this category
		// clone template nodes
		var new_toggle= toggle_model.cloneNode(true);
		var new_content= toggled_content_model.cloneNode(true);

		new_toggle.innerHTML= pair.key;
		new_toggle.id= "";
		new_content.id= pair.key;

		$A(pair.value).each(function(item){
			var new_node= document.createElement('A');
			new_node.href="#";
			new_node.innerHTML= item.id;
			new_node._data_items= item.content;

			new_content.appendChild(new_node);
			Element.show(new_node);
		});

		top_node.appendChild(new_toggle);
		top_node.appendChild(new_content);

		Element.removeClassName(new_toggle, 'model');
		Element.removeClassName(new_content, 'model');

	});
}

function initTree()
{
	var containers= document.getElementsByClassName('toggled_content').each( function(cont){
		var nodes= $A(cont.getElementsByTagName('A')).each(function(el){

			new Ajax.Request('base/datasources/' + el.parentNode.id + '/script.js?r=' + Math.random(), {
				method: 'get',
				onComplete: function(req){
					eval(req.responseText);
				}

			});

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
			IODatasource[selected_ds.parentNode.id].save(selected_ds._data_items);
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
		}
	}

	return false;
}



// editors
function ds_loadData()
{

}

