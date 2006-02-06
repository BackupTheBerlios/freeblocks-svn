


Component= Class.create();
Component.prototype= {

	_div: null,

	initialize: function(){

	},

	updateComponentProp: function(){
		if( this.position == 'container' )
		{
			this.parent= this._div.parentNode.id;
		}
	},

	showNoPreviewContent: function(el){
		var text= '';

		text+= '[Content hidden]<br/>';
		text+= 'type: ' + this.type + '<br/>';
		text+= 'id: ' + this._div.id + '<br/>';

		el.innerHTML= text;
	}
};

Component.getUnusedID= function(){
		var new_id;
		var n= 1;

		do{
			 new_id= "comp_" + n;
			 n++;
		}
		while( $(new_id) != null );

		return new_id;
	}



