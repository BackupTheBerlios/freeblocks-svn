


Board= Class.create();
Board.prototype= Object.extend(new Component(), {

	updateContent: function(){

		if( this.dynamic == "true" )
		{
			this._div.childNodes.item(0).innerHTML= "Dynamic: " + this.tagname + " / " + this.field;
		}
		else
		{
			this._div.childNodes.item(0).innerHTML= this.text;
		}

		this._div.style.width= this.width;
	}
});

