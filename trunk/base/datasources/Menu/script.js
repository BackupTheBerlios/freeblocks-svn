
IODatasource["Menu"]= {

	// misc functions
	addLine: function(){

		// first locate the model
		var parent= $('ds_editor_Menu');
		var model= Element.childrenWithClassName(parent, 'line_model')[0];

		function pageCheckChanged(event){
			// first get a reference to the url element
			var el= Event.element(event);
			var index= el.id.split('-')[1];
			var url_el= document.getElementById('Menu_item_target-' + index);
			var newnode;

			if( el.checked )
			{
				// replace the input by a select
				newnode= document.createElement('select');
				newnode.id= url_el.id;
				for(var i= 0; i< Datasource.pages.length; i++)
				{
					newnode.options[i]= new Option(Datasource.pages[i], Datasource.pages[i]);
				}
			}
			else
			{
				newnode= document.createElement('input');
				newnode.id= url_el.id;
			}

			FormElement.setValue(newnode, url_el.value);
			url_el.parentNode.replaceChild(newnode, url_el);
		}

		function removeLine(event){
			var el= Event.element(event);
			Element.remove(el.parentNode);
		}

		var i= 0;
		// find an unused index
		for(;; i++){
			if( $('menu_line_item_' + i) == null )
				break;
		}

		var new_el= $('menu_line_item_' + i);

		if( new_el == null ){
			new_el= model.cloneNode(true);
			new_el.id= 'menu_line_item_' + i;
			Element.removeClassName(new_el, 'line_model');

			// now give correct id to the new fields
			for(var j= 0; j< new_el.childNodes.length; j++)
			{
				var child= new_el.childNodes[j];
				if( child.id )
				{
					var parts= child.id.split('-');
					child.id= parts[0] + '-' + i;
				}
			}

			model.parentNode.insertBefore(new_el, model);

		}

		// add checkbox callback
		var check= document.getElementById("Menu_item_page-" + i);
		check.onclick= pageCheckChanged;
		pageCheckChanged({target: check});

		// add remove callback
		var el= Element.childrenWithClassName(new_el, 'menu_remove_button')[0];
		el.onclick= removeLine;
	},


	// external interface
	load: function(data){

		var i= 0;

		// then create each element if it does not
		// already exists
		for(; i< data.length; i++){
			if( $('menu_line_item_' + i) == null ){
				this.addLine();
			}

			["label", "page", "target"].each(function(name){
				var el= document.getElementById('Menu_item_' + name + '-' + i);
				FormElement.setValue(el, data[i][name]);
			});
		}

		// remove the other fields
		while(true){
			var el= $('menu_line_item_' + i);
			if( el != null ){
				Element.remove(el);
			}
			else{
				break;
			}

			i++;
		}
	},

	save: function(data){
		var parent= $('ds_editor_Menu');

		var nodes= $A(Element.childrenWithClassName(parent, 'line')).findAll(function(el){ return !Element.hasClassName(el, 'line_model') });
		for(var i= 0; i< nodes.length; i++){
			data[i]= {};
			["label", "page", "target"].each(function(name){
				var el= document.getElementById('Menu_item_' + name + '-' + i);
				data[i][name]= FormElement.getValue(el);
			});
		}
	}
};

// init
$('menu_add_button').onclick= function(){
	IODatasource["Menu"].addLine();
	return false;
}

