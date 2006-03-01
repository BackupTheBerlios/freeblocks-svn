/**********************************************************
Adapted from the sortable lists example by Tim Taylor
http://tool-man.org/examples/sorting.html
Modified by Tom Westcott : http://www.cyberdummy.co.uk
Heavily modified by Julien Ammous : http://freeblocks.berlios.de
**********************************************************/

function log(txt){

}

var DragContainer= Class.create();
DragContainer.prototype= {

	options: null,
	element: null,
	group: null,

	initialize: function(htmlElement/*, group, options, child_options*/){
		htmlElement= $(htmlElement);

		this.group= arguments[1] || 'default';
		this.element= htmlElement;
		this.element._contObj= this;
		this.options= Object.extend({
			onDragOver: function(){},
			onDragOut: function(){},
			onDragDrop: function(){},
			onActivate: function(){},
			onDeActivate: function(){},
			allowOutsideContainers: false,
			_top: this
		}, arguments[2] || {});

		var items= this.element.getElementsByTagName( "div" );

		for( var i= 0; i< items.length; i++)
		{
			// only direct children
			if( items[i].parentNode === this.element )
			{
				DragDrop.addDraggable(items[i], this, this.group, arguments[3] || {});
			}
		}

		DragDrop.containers.push(this);
	}
};

var DragDrop= {
	containers: null,
	firstContainer : null,
	lastContainer : null,
	parent_group : null,
	enter_container_key: null,
	key_pressed: false,

	KEY_CTRL: 17,
	KEY_C: 67,

	addDraggable: function(element, parent, group /*, options */ ){
		var drag= new DraggableItem(element, Object.extend({
			threshold: 5,
			onDragStart: this.onDragStart,
			onDrag: this.onDrag,
			onDragEnd: this.onDragEnd,
			container_group: group
		}, arguments[3] || {}));

		// tracks if the item is currently outside all containers
		drag.activeContainer= parent;
		if( parent != null )
		{
			drag.originalContainer= parent.element;
		}
		else
		{
			drag.originalContainer= null;
		}

		return drag;
	},

	onKeyUpDown: function(event){
/*
		if( event.type == 'keydown' )
		{
			log('down: ' + event.keyCode);
		}
*/
		// enter this condition if:
		//    key_pressed= true  && event is keyup
		// or key_pressed= false && event is keydown
		// and if we are dragging something
		if( (DragDrop.key_pressed == (event.type=="keyup")) && ( (event.type=="keyup") || (Drag.dragged_obj != null)) )
		{
			if( (DragDrop.enter_container_key != null) && (event.keyCode==DragDrop.enter_container_key) )
			{
				DragDrop.key_pressed= (event.type=="keydown")?true:false;
			}

			if( DragDrop.key_pressed == (event.type=="keydown") )
			{
				if( event.type=="keyup" ){
					DragDrop.deActivateTargetContainers();
				}
				else {
					DragDrop.activateTargetContainers();
				}

				if( Drag.dragged_obj != null )
				{
					Drag.onMouseMove(event);
				}

				Event.stop(event);
			}
		}
	},

	onDragStart: function(draggable_obj, event) {

		var element= draggable_obj.element;
		var parent= draggable_obj.activeContainer;

		log('onDragStart(p: '+parent+', e: '+element+')');

		if( DragDrop.canEnterContainer() )
		{
			DragDrop.activateTargetContainers();
		}

		DragDrop.parent_group= element._dragObj.options.container_group;

		if( parent != null )
		{
			// item starts out over current parent
			parent.options.onDragOver();
		}
	},

	activateTargetContainers: function()
	{
		// activate each container of item group
		for(var i= 0; i< DragDrop.containers.length; i++)
		{
			var container= DragDrop.containers[i];

			if( Drag.dragged_obj.options.container_group == container.group )
			{
				container.options.onActivate();
			}
		}
	},

	deActivateTargetContainers: function()
	{
		// deactivate each container of item group
		for(var i= 0; i< DragDrop.containers.length; i++)
		{
			var container= DragDrop.containers[i];

			if( (Drag.dragged_obj == null) || (Drag.dragged_obj.options.container_group == container.group) )
			{
				container.options.onDeActivate();
			}
		}
	},

	canEnterContainer: function(){
		var ret= false;
		if( DragDrop.enter_container_key == null )
		{
			ret= true;
		}
		else
		{
			ret= DragDrop.key_pressed;
		}

		return ret;
	},

	onDrag: function(draggable_obj, event){

		var element= draggable_obj.element;
		var parent= draggable_obj.activeContainer;

		// check if we're outside the last container we were in
		if( (parent != null) && ( !DragDrop.canEnterContainer() || !DragUtils.within(parent.element, element)) )
		{
			log('onDragOut('+draggable_obj+',' + parent + ')');

			// we left the old container
			parent.options.onDragOut();

			draggable_obj.activeContainer= null;

			if( parent.options.allowOutsideContainers == true )
			{
				// change parent to body
				element.style.position= 'relative';
				element.parentNode.removeChild( element );
				document.getElementsByTagName('body').item(0).appendChild( element );
			}
		}

		// check if we were nowhere
		if( (parent == null) && DragDrop.canEnterContainer() )
		{
			// check each container to see if in its bounds
			for(var i= 0; i< DragDrop.containers.length; i++)
			{
				var container= DragDrop.containers[i];

				if( DragUtils.within(container.element, element) && (container.group == DragDrop.parent_group)) {

					log('onDragOver(' + container.element.id + ')');
					// we're inside this one
					container.options.onDragOver();
					element._dragObj.activeContainer= container;

					// change parent
					element.parentNode.removeChild( element );
					container.element.appendChild( element );

					break;
				}
			}
		}


		if( element._dragObj.activeContainer != null )
		{
			// if we get here, we're inside some container bounds, so we do
			// everything the original dragsort script did to swap us into the
			// correct position

			var item= element;
			var next= DragUtils.nextItem(item);
			while (next != null && element.offsetTop >= next.offsetTop - 2) {
				var item = next;
				var next = DragUtils.nextItem(item);
			}
			if (element != item) {
				DragUtils.swap(element, next);
				return;
			}

			var item = element;
			var previous = DragUtils.previousItem(item);
			while (previous != null && element.offsetTop <= previous.offsetTop + 2) {
				var item= previous;
				var previous= DragUtils.previousItem(item);
			}
			if (element != item) {
				DragUtils.swap(element, item);
				return;
			}
		}

	},

	onDragEnd: function(draggable_obj, event){

		var element= draggable_obj.element;
		var parent= draggable_obj.activeContainer;

		DragDrop.deActivateTargetContainers();

		if( parent != null )
		{
			parent.options.onDragOut();
			parent.options.onDragDrop();
			element.style["top"] = "0px";
			element.style["left"] = "0px";
		}
		// move the item back to the container it was in
		else if( element._dragObj.options.allowOutsideContainers == false )
		{
			var container= element._dragObj.originalContainer;

			element._dragObj.activeContainer= container._contObj;
			container.appendChild( element );
			element.style["top"]= "0px";
			element.style["left"]= "0px";
			return;
		}
	}

};

DragDrop.containers= new Array();

var DragUtils = {
	swap : function(item1, item2) {
		var parent = item1.parentNode;
		parent.removeChild(item1);
		parent.insertBefore(item1, item2);

		item1.style["top"] = "0px";
		item1.style["left"] = "0px";
	},

	nextItem : function(item) {
		var sibling = item.nextSibling;
		while (sibling != null) {
			if (sibling.nodeName == item.nodeName) return sibling;
			sibling = sibling.nextSibling;
		}
		return null;
	},

	previousItem : function(item) {
		var sibling = item.previousSibling;
		while (sibling != null) {
			if (sibling.nodeName == item.nodeName) return sibling;
			sibling = sibling.previousSibling;
		}
		return null;
	},


	within: function(parent, element){

	    var parent_pos = Position.cumulativeOffset(parent);
	    var child_pos = Position.cumulativeOffset(element);

	    return ((child_pos[1] >= parent_pos[1] && child_pos[1] <  parent_pos[1] + parent.offsetHeight &&
	             child_pos[0] >= parent_pos[0] && child_pos[0] <  parent_pos[0] + parent.offsetWidth)
	            ||
	            (child_pos[1] + element.offsetHeight >= parent_pos[1] && child_pos[1] + element.offsetHeight < parent_pos[1] + parent.offsetHeight &&
	             child_pos[0] + element.offsetWidth  >= parent_pos[0] && child_pos[0] + element.offsetWidth  < parent_pos[0] + parent.offsetWidth));
	}
};


Event.observe(document, "keydown", DragDrop.onKeyUpDown);
Event.observe(document, "keyup", DragDrop.onKeyUpDown);


