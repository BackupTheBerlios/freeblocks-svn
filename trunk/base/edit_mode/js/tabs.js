
var active_tab= null;
var init_functions= {};

// activate tabs
function initTabs()
{


	// activate all the tabs
	var nodes= Element.childrenWithClassName('tabs', 'tab');
	for(var i= 0; i< nodes.length; i++)
	{
		nodes[i]._tabData= null;

		nodes[i].onclick= function(event){

			// unselect last selected tab and hide his content
			if( active_tab != null )
			{
				Element.removeClassName(active_tab, 'selected_tab');
				Element.hide(active_tab._contentDiv);
			}

			// then change it to the new one
			active_tab= this;
			Element.addClassName(active_tab, 'selected_tab');

			if( this._contentDiv == null )
			{
				// then creates it
				this._contentDiv= document.createElement('div');
				this._contentDiv.id= this.id + '_content';
				$('inner_content').appendChild(this._contentDiv);
				Element.hide(this._contentDiv);

				showLoadingIndicator();
				new Ajax.Updater(this._contentDiv, this.href, {
					method: 'get',
					onComplete: function(req){
						// call the init function if exists
						if( init_functions[active_tab.id + "_init"] )
						{
							init_functions[active_tab.id + "_init"].call();
						}
						// show the content
						Element.show(active_tab._contentDiv);
						hideLoadingIndicator();
					}
				});
			}
			else
			{
				//Element.update('inner_content', this._tabData);
				Element.show(this._contentDiv);
			}

			Event.stop(event);
			return false;
		};
	}
}


window.setTimeout("initTabs()", 200);
