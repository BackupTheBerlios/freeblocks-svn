var rules= {

	'.add_button': function(el){
		el.onclick= function(e){
			return false;
		}
	},

	// disable links in components
	'.component A': function(el){
		el.onclick= function(e){
			return false;
		}
	},

	'#show_properties': function(el){
		el.onchange= function(e){
			$('properties_panel').style.visibility= $('show_properties').checked?'visible':'hidden';
		}
	},

	'#apply_properties': function(el){
		el.onclick= function(e){
			tinyMCE.triggerSave();
			lastselected.savePropertyPanel();
			lastselected.updateContent();

			$('save_page').disabled= false;
			$('page_select').disabled= true;
		}
	},

	'#delete_component': function(el){
		el.onclick= function(e){
			if( lastselected != null )
			{
				Element.remove(lastselected._div);
				hidePropertyPanels();
			}
		}
	},

////////////////////////
	// bottom bar

	'#page_properties': function(el){
		el.onclick= function(e){
			if( lastselected != null )
			{
				Element.removeClassName(lastselected._div, 'component_selected');
				lastselected.savePropertyPanel();
				lastselected.updateContent();
			}
			page.fillPropertyPanel();
			lastselected= page;
			$('disp_comp_id').innerHTML= 'Page';
			display_properties('Page');
		}
	},

	'#page_select' : function(el){
		el.onchange= function(e){
			window.location= '?edit=1&page=' + $('page_select').value;
		}
	},

	'#save_page' : function(el){
		el.onclick= function(){
			//var opt = document.getElementById('properties_panel') ;
			//var f= document.getElementById("savedPage");
			var data= '';


			data+='<page ';
			for( prop in page )
			{
				if( (typeof page[prop] != "function") && (prop.charAt(0) != '_'))
				{
					data+= prop + '="' + escape(page[prop].replace(/"/g, "'")) + '" ';
				}
			}

			data+= '>';


			var nodes= document.getElementsByClassName('component');
			for(var i= 0; i< nodes.length; i++)
			{
				var obj= nodes[i].obj;

				if( obj != null)
				{
					obj.updateComponentProp();

					var x= nodes[i].style.left.replace(/px/i, '').replace(/pt/i, '');
					var y= nodes[i].style.top.replace(/px/i, '').replace(/pt/i, '');

					data+= '<component x="' + x + '" y="' + y + '" ';

					for( property in obj )
					{
						if( (typeof obj[property] != "function") && (property.charAt(0) != '_') &&
							(property != "x") && (property != "y") )
						{
							data+= property + '="' + escape(obj[property].replace(/"/g, "'")) + '" ';
						}
					}



					// if node has children then include them as well
					if( obj['_children'] != null )
					{
						data+= '>\n';

						for(var j= 0; j< obj['_children'].length; j++)
						{
							var child= obj['_children'][j];
							data+= "<" + child['tagName'] + " ";

							for(prop in child)
							{
								if( (prop != "tagName") && (prop != "_v") )
								{
									data+= prop + '="' + escape(child[prop]) + '" ';
								}
							}

							data+= " />\n";
						}

						data+= '</component>\n';
					}
					else
					{
						data+= '/>\n';
					}
				}
			}

			data+='</page>';

			displayloading();

			//$('middle_container').textContent= data;

			new Ajax.Request('save_page.php', {

				parameters: 'lines=' + data + "&page=" + $('old_page_name').value,

				onSuccess: function(req){
					var xml= req.responseXML.getElementsByTagName('return').item(0);

					add_display_msg(xml.getAttribute('msg'), (xml.getAttribute('ret') == "ok")?'lightgreen':'red');
					$('save_page').disabled= true;
					$('page_select').disabled= false;
					hideLoading();

					//$('middle_container').textContent= req.responseText;
				},

				onFailure: function(){ alert('failed'); }
			});

		}
	}

};

Behaviour.register(rules);
