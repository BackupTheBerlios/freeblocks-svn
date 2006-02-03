


StaticText= Class.create();
StaticText.prototype= Object.extend(new Component(), {

	updateContent: function(){
		var content_div= Element.childrenWithClassName(this._div, 'content')[0];

		if( this.preview == 'true' )
		{
			content_div.innerHTML= this.text;
		}
		else
		{
			this.showNoPreviewContent(content_div);
		}
		this._div.style.width= this.width || 'auto';
	}
});

