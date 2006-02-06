


Menu= Class.create();
Menu.prototype= Object.extend(new Component(), {

	updateContent: function(){
		var nodes= this._div.getElementsByTagName('a');
		for(var i= 0; i< nodes.length; i++)
		{
			nodes[i].href= this._children[i].url;
			nodes[i].innerHTML= this._children[i].label;
		}

		this._div.style.width= this.width;
	}
});

