
var Datasource= Class.create();
var Datasource= {

	/* variables */
	datasources: {},
	datasources_ids: [],
	pages: [],

	/* functions */

	addItem: function(type){
		var new_item= {};

		function isDefined(id){
			return (Datasource.datasources_ids.indexOf(id) != -1);
		};

		function findUnusedID(type){
			var base= 'data_' + type.toLowerCase() + '_';
			var i= 1;
			for(;; i++ ){
				if( !isDefined(base + i) ){
					break;
				}
			}

			return base+i;
		};

		if( this.datasources[type] != null ){
			var new_id= findUnusedID(type);
			new_item= {"id": new_id, "content": []};
			this.datasources[type].push( new_item );
			this.datasources_ids.push(new_id);
		}

		return new_item;
	},

	removeItem: function(type, id){
		var new_list= this.datasources_ids.findAll(function(name){ return (name != id); });
		var new_ds= this.datasources[type].findAll(function(ds){ return (ds.id != id); });

		this.datasources_ids= new_list;
		this.datasources[type]= new_ds;
	},

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
								Datasource.datasources_ids.push(item['id']);
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
	},

	// send data to server
	save: function(){
		var json_data= JSON.stringify(this.datasources);

		new Ajax.Request('base/edit_mode/save_datasources.php', {
			method: 'post',
			parameters: 'data=' + json_data
		});
	}

};


