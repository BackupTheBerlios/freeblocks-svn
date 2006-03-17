
Element.childrenWithClassName = function(element, className) {
  return $A($(element).getElementsByTagName('*')).select(
    function(c) { return Element.hasClassName(c, className) });
}


///////
// loading indicator

function showLoadingIndicator(){
	Element.show('loading_msg');
}

function hideLoadingIndicator(){
	Element.hide('loading_msg');
}

/*
tinyMCE.init({
	mode 		: "textareas",
	theme 		: "advanced",
	plugins 	: "advimage,fullscreen",
	external_image_list_url : "img_list.js.php",
	advimage_styles : "float left=float_left;float right=float_right",
	theme_advanced_disable : "formatselect",
	theme_advanced_statusbar_location: "bottom",
	theme_advanced_buttons3_add : "fullscreen",
	inline_styles : true,
	content_css : "base.css"
});


var drag_prop= new Draggable('properties_panel',
	{handle: 'title',
	 starteffect: null,
	 endeffect: null,
	 change: function(obj){
	 	var now= new Date();
		setCookie('prop_x', obj.element.style.left, new Date(now.getTime() +3600 * 15 * 1000));
		setCookie('prop_y', obj.element.style.top, new Date(now.getTime() +3600 * 15 * 1000));
	 }
	}
);

drag_prop.element.style.left= getCookie('prop_x');
drag_prop.element.style.top= getCookie('prop_y');
*/




// enumerate all the possible containers on the template
function initSortable()
{
	var containers= new Array();
	var nodes= document.getElementsByClassName('container');
	for(var i= 0; i< nodes.length; i++){
		containers.push( nodes[i].id );

	}

	for(var i=0; i< containers.length; i++){
		/*
		Sortable.create(containers[i], {
			tag: 'div',
			handle: 'handle',
			hoverclass: 'hover',
			constraint: false,
			dropOnEmpty: true,
			containment: containers
		})
		*/

		new DragContainer(nodes[i], 'global_group', {
				/* container options*/
				allowOutsideContainers: true,

				onDragOver: function(){
					this._top.element.style["background"]= "#EEF";
				},

				onDragOut: function(){
					this._top.element.style["background"]= "none";

					// force the element to recompute its size
					var content_node= Element.childrenWithClassName(this._top.element, 'content')[0];
					var tmp= content_node.innerHTML;
					content_node.innerHTML= "";
					content_node.innerHTML= tmp;
				},

				onActivate: function(){
					this._top.element.style["border"]= "1px solid red";
				},

				onDeActivate: function(){
					this._top.element.style["border"]= "1px solid black";
				}
			},

			/* inner elements options */
			{
				handle: 'handle',

				onDragEnd2: function(dragged_obj, event){

					log('f');

					var comp= dragged_obj.element.obj;

					if( dragged_obj.activeContainer != null )
					{
						comp.position= 'container';
						comp.parent= dragged_obj.activeContainer.element.id;
					}
					else if( comp.position == 'container' )
					{
						comp.position= 'absolute';
					}


					component_clicked(dragged_obj.element, true);
				}
			}
		);
	}
}

//window.setTimeout("initSortable()", 100);


