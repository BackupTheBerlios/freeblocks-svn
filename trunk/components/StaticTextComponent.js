


StaticTextComponent= Class.create();
StaticTextComponent.prototype= Object.extend(new Component(), {

	updateContent: function(){
		this._div.childNodes.item(0).nodeValue= this.text;
		this._div.style.width= this.width || 'auto';
	}
});

