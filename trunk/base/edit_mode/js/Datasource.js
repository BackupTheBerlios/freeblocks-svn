
var Datasource= Class.create();
var Datasource= {

	/* variables */
	datasources: {},
	pages: [],

	/* funcions */

	loadFromServer: function(){

		new Ajax.Request('base/edit_mode/datasources.xml.php', {

			method: 'get',

			onSuccess: function(req){
				var xml= req.responseXML.getElementsByTagName('root').item(0);

				for(var i= 0; i< xml.childNodes.length; i++)
				{
					var top_node= xml.childNodes.item(i);

					if( top_node.nodeName == 'datalist' ){
						$A(top_node.childNodes).each(function(node){
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
						});
					}
					else if( top_node.nodeName == 'pages' ){
						$A(top_node.childNodes).each(function(node){
							Datasource.pages.push(node.getAttribute('name'));
						});
					}
				}
			},

			onFailure: function(){ alert('server error: unable to load datasources'); }
		});
	}

};


