


Component= Class.create();
Component.prototype= {

	_div: null,
	jsClass: null,

	initialize: function(){

	},

	/*
		argument provided is provided if
		the element attached to this object already exists
		(loading)

		If not present it means we are creating the element
		on the fly
	*/
	setup: function(){
		if( arguments[0] != null )
		{
			// we are loading the component
			this._div= arguments[0];
		}
		else
		{
			// new component, create it
			var orig= $('model_' + this.jsClass);
			var new_comp= orig.cloneNode(true);
			var body= document.getElementsByTagName('body').item(0);

			new_comp.id= Component.getUnusedID();
			Element.setStyle(new_comp, {
				position: 'absolute',
				left: 0,
				top: 0
			});


			body.appendChild(new_comp);

			this['type']= this.jsClass;
			this['id']= new_comp.id;
			this._div= new_comp;
		}

		this._div.obj= this;
		//this._div.onclick= function(){ component_clicked(this) };

		handle= document.createElement('<div>');
		handle.className= 'handle';

		Element.setOpacity(handle, 0.7);

		this._div.insertBefore(handle, this._div.firstChild);

		if( this.updateContent == null )
		{
			this.updateContent= function(){};
		}

		Element.show(this._div);
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

// "static" methods
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



