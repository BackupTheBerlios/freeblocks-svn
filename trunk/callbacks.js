var rules= {

	'.error_close': function(el){
		el.onclick= function(e){

			//this.parentNode.style.display= "none";
			Element.remove(this.parentNode);
			return false;
		}
	},

	'#apply_properties': function(el){
		el.onclick= function(e){
			lastselected.savePropertyPanel();
			lastselected.updateContent();
			$('save_page').disabled= false;
		}
	},

	'#delete_component': function(el){
		el.onclick= function(e){
			if( lastselected != null )
			{
				var form= document.getElementsByTagName('form').item(0);
				form.removeChild( lastselected._div );
				hidePropertyPanels();
			}
		}
	},

	'#save_page' : function(el){
		el.onclick= function(){
			var opt = document.getElementById('properties_panel') ;
			var f= document.getElementById("savedPage");

			// first line
			var newInput= document.createElement("input");
			newInput.type= "hidden";
			newInput.name= "lines[]";

			newInput.value='<page name="' + $('page_name').value + '" template="' + $('page_template').value + '" >';
			f.appendChild(newInput);

			var nodes= document.getElementsByClassName('component');
			for(var i= 0; i< 3; i++)
			{

				var obj= nodes[i].obj;
				var newInput= document.createElement("input");
				newInput.type= "hidden";
				newInput.name= "lines[]";
				var x= nodes[i].style.left.replace(/px/i, '').replace(/pt/i, '');
				var y= nodes[i].style.top.replace(/px/i, '').replace(/pt/i, '');

				newInput.value= '<component x="' + x + '" y="' + y + '" ';

				for( property in obj )
				{
					if( (typeof obj[property] != "function") && (property.charAt(0) != '_') &&
						(property != "x") && (property != "y") )
					{
						//$('middle_container').innerHTML+= property + "<br/>";
						newInput.value+= property + '="' + escape(obj[property]) + '" ';
					}
				}



				// if node has children then include them as well
				if( obj['_children'] != null )
				{
					newInput.value+= '>';

					for(var j= 0; j< obj['_children'].length; j++)
					{
						var child= obj['_children'][j];
						newInput.value+= "<" + child['tagName'] + " ";

						for(prop in child)
						{
							if( (prop != "tagName") && (prop != "_v") )
							{
								newInput.value+= prop + '="' + escape(child[prop]) + '" ';
							}
						}

						newInput.value+= " />";
					}

					newInput.value+= '</component>';
				}
				else
				{
					newInput.value+= '/>';
				}

				f.appendChild(newInput);
			}

			// last line
			var newInput= document.createElement("input");
			newInput.type= "hidden";
			newInput.name= "lines[]";

			newInput.value='</page>';
			f.appendChild(newInput);

			f.submit();
		}
	}

};

Behaviour.register(rules);