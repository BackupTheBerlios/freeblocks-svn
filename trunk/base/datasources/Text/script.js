

tinyMCE.addMCEControl($('Text_editor_area'), 'Text_editor_area');

IODatasource["Text"]= {
	load: function(data){
		var tmp= data[0].text;
		$('Text_editor_area').value= tmp;
		tinyMCE.updateContent('Text_editor_area');
	},

	save: function(data){
		tinyMCE.selectedInstance= tinyMCE.getInstanceById('Text_editor_area');
		tinyMCE.triggerSave();
		data[0].text= tinyMCE.getContent();
	}
};
