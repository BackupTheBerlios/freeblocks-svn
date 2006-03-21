
var Datasource= Class.create();
var Datasource= {

	/* variables */
	datasources: {},

	/* funcions */

	loadFromServer: function(){

		new Ajax.Request('base/edit_mode/datasources.xml.php', {

			method: 'get',

			onSuccess: function(req){
				var xml= req.responseXML.getElementsByTagName('root').item(0);

				for(var i= 0; i< xml.childNodes.length; i++)
				{
					var node= xml.childNodes.item(i);

					if( node.nodeName == 'data' )
					{
						var type= 	node.getAttribute('type');
						var item= {
							id: node.getAttribute('id')
						};

						item.content= new Array();


						for(var j= 0; j< node.childNodes.length; j++)
						{
							var data_node= node.childNodes[j];
							var line= {};

							for(k= 0; k< data_node.attributes.length; k++)
							{
								var name= data_node.attributes[k].name;
								var value= data_node.attributes[k].value;

								line[name]= value;
							}
							item.content.push(line);
						}

						if( !Datasource.datasources[type] )
						{
							Datasource.datasources[type]= new Array();
						}

						Datasource.datasources[type].push(item);
					}
				}
			},

			onFailure: function(){ alert('server error: unable to load datasources'); }
		});
	}

};


