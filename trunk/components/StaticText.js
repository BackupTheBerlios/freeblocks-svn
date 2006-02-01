


StaticText= Class.create();
StaticText.prototype= Object.extend(new Component(), {

	updateContent: function(){
		this._div.getElementsByTagName('div').item(0).innerHTML= this.text;
		this._div.style.width= this.width || 'auto';
	}
});

