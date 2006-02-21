


Menu= Class.create();
Menu.prototype= Object.extend(new Component(), {

	jsClass: 'Menu',

	updateContent: function(){
		/*
		var nodes= this._div.getElementsByTagName('a');
		var numchildren= (this._children != null)?this._children.length:0;

		for(var i= 0; i< max(nodes.length, numchildren); i++)
		{
			// check if node already exists
			if( i< nodes.length )
			{
				if( i< numchildren )
				{
					nodes[i].href= this._children[i].url;
					nodes[i].innerHTML= this._children[i].label;
				}
				else
				{
					Element.remove(nodes[i]);
				}
			}
			else
			{
				// it doesn't exists, create it
				var newnode= document.createElement('a');
				newnode.href= this._children[i].url;
				newnode.innerHTML= this._children[i].label;
				this._div.appendChild(newnode);
			}
		}
		*/

		this._div.style.width= this.width;
	},

	compValueChanged: function(comp){
		var parts= comp.id.split('-');
		var id= parts[1];
		//var reg= new RegExp("\\?page=(\\w+)", "i");

		//$('Menu_item_page-' + id).checked= false;

		if( parts[0] == 'Menu_item_page' )
		{
			var val= comp.value;

			Menu.pageCheckChanged( comp );

			if( comp.checked )
			{
				$('Menu_item_url-' + id).value= val;
			}
		}
	}

});

// static methods

Menu.multi_item_addline= function(){

	var model= $('Menu_item_label-0').parentNode;
	var new_node= model.cloneNode(true);
	Element.removeClassName(new_node, 'line_model');

	var parent= model.parentNode;
	parent.insertBefore(new_node, parent.lastChild.previousSibling);

	// find an unused id
	var i;
	for(i= 1;;i++)
	{
		if( $('Menu_item_label-' + i) == null )
		{
			break;
		}
	}

	// now give correct id to the new fields
	for(var j= 0; j< new_node.childNodes.length; j++)
	{
		var child= new_node.childNodes[j];
		if( child.id )
		{
			var parts= child.id.split('-');
			child.id= parts[0] + '-' + i;
		}
	}
};

Menu.multi_item_removeline= function(el){
	Element.remove(el.parentNode);
};

Menu.pageCheckChanged= function(el){
	// first get a reference to the url element
	var url_el= el.nextSibling.nextSibling;
	var newnode;

	if( el.checked )
	{
		// replace the input by a select
		newnode= document.createElement('select');
		newnode.id= url_el.id;
		for(var i= 0; i< pages_list.length; i++)
		{
			newnode.options[i]= new Option(pages_list[i], pages_list[i]);
		}
	}
	else
	{
		newnode= document.createElement('input');
		newnode.id= url_el.id;
	}

	url_el.parentNode.replaceChild(newnode, url_el);
};




