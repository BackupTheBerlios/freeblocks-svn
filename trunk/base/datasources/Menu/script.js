
tinyMCE.addMCEControl($('Text_editor_area'), 'Text_editor_area');


IODatasource["Menu"]= {
	load: function(data){

		// first locate the model
		var parent= $('ds_editor_Menu');
		var model= Element.childrenWithClassName(parent, 'line_model')[0];

		// then create each element if it does not
		// already exists
		for(var i= 0; i< data.length; i++){
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

			["label", "page", "target"].each(function(name){
				var el= document.getElementById('Menu_item_' + name + '-' + i);
				el.value= data[i][name];
			});
		});
	},

	save: function(data){

	}
};
