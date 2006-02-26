/**********************************************************
Adapted from the sortable lists example by Tim Taylor
http://tool-man.org/examples/sorting.html
Modified by Tom Westcott : http://www.cyberdummy.co.uk
**********************************************************/

function log(txt){

}

var DragContainer= Class.create();
DragContainer.prototype= {

	options: null,
	element: null,
	group: null,

	initialize: function(htmlElement, group){
		htmlElement= $(htmlElement);

		this.group= group;
		this.element= htmlElement;
		this.element._contObj= this;
		this.options= Object.extend({
			onDragOver: function(){},
			onDragOut: function(){},
			onDragDrop: function(){},
			onActivate: function(){},
			onDeActivate: function(){},
			_top: this
		}, arguments[2] || {});

		var items= htmlElement.getElementsByTagName( "div" );

		for( var i= 0; i< items.length; i++)
		{
			var drag= new DraggableItem(items[i], {
				threshold: 5,
				onDragStart: this.onDragStart,
				onDrag: this.onDrag,
				onDragEnd: this.onDragEnd
			});

			drag.activeContainer= htmlElement;

			// tracks if the item is currently outside all containers
			DragDrop.wasOutside= false;
		}

		DragDrop.containers.push(this);
	},

	onDragStart: function(nwPosition, sePosition, nwOffset, seOffset) {

		log('onStart');

		// update all container bounds, since they may have changed
		// on a previous drag
		//
		// could be more smart about when to do this
		for(var i= 0; i< DragDrop.containers.length; i++)
		{
			var container= DragDrop.containers[i];

			container.northwest= Coordinates.northwestOffset( container, true );
			container.southeast= Coordinates.southeastOffset( container, true );

			// activate each container
			container.options.onActivate();
		}

		var element= this._top.element;
		var parent= element.parentNode;

		// item starts out over current parent
		parent._contObj.options.onDragOver();
		parent_id= parent.id;
		parent_group= parent._contObj.group;
	},

	onDrag: function(nwPosition, sePosition, nwOffset, seOffset){

		var element= this._top.element;
		var parent= element.parentNode;

		// check if we were nowhere
		if( DragDrop.wasOutside ){

			// check each container to see if in its bounds
			for(var i= 0; i< DragDrop.containers.length; i++)
			{
				var container= DragDrop.containers[i];

				//log('within ' + container.id + ': ' + (DragUtils.within(container, element)?'true':'false') + '  ,group: ' + container.group + ' ' + parent_group);

				if( DragUtils.within(container.element, element) && (container.group == parent_group)) {

					// we're inside this one
					container.options.onDragOver();
					DragDrop.wasOutside= false;
					element.activeContainer= container;

					/*
					// since wasOutside was true, the current parent is a
					// temporary clone of some previous container node and
					// it needs to be removed from the document
					var tempParent= this.parentNode;
					tempParent.removeChild( this );
					container.appendChild( this );
					tempParent.parentNode.removeChild( tempParent );
					*/
					break;
				}
			}
			// we're still not inside the bounds of any container
			if( this.wasOutside )
			{
				return;
			}
		}
		// check if we're outside our parent's bounds
		else if( !DragUtils.within(parent, element) )
		{
			log('onDragOut - outside parent');

			// we left the old container
			element._dragObj.activeContainer._contObj.options.onDragOut();

			//parent._contObj.options.onDragOut();
			//parent._contObj.options.onActivate();
			DragDrop.wasOutside= true;

			// check if we're inside a new container's bounds
			for(var i= 0; i< DragDrop.containers.length; i++)
			{
				var container= DragDrop.containers[i];

				if( DragUtils.within(container.element, element) && (container.group == parent_group)) {
					// we're inside this one
					container.options.onDragOver();
					DragDrop.wasOutside= false;
					parent.removeChild( element );
					container.element.appendChild( element );
					break;
				}
			}

			// if we're not in any container now, make a temporary clone of
			// the previous container node and add it to the document
			if( DragDrop.wasOutside ){
				/*
				var tempParent= parent.cloneNode( false );
				parent.removeChild( element );
				tempParent.appendChild( element );
				// body puts a border or item at bottom of page if do not have this
				tempParent.style.border= 0;
				document.getElementsByTagName( "body" ).item(0).appendChild( tempParent );
				*/
				return;
			}
		}

		// if we get here, we're inside some container bounds, so we do
		// everything the original dragsort script did to swap us into the
		// correct position

		//var parent= this.parentNode;

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
	},

	onDragEnd: function(nwPosition, sePosition, nwOffset, seOffset){

		var element= this._top.element;
		var parent= element.parentNode;

		for(var i= 0; i< DragDrop.containers.length; i++)
		{
			// deactivate each container
			DragDrop.containers[i].options.onDeActivate();
		}

		// if the drag ends and we're still outside all containers
		// it's time to remove ourselves from the document or add
		// to the trash bin
		if (this.wasOutside) {
			var container;
			for(var i= 0; i< DragDrop.containers.length; i++)
			{
				container= DragDrop.containers[i];

				if( container.id == parent_id )
				{
					break;
				}
			}

			DragDrop.wasOutside= false;
			this.parentNode.removeChild( this );
			container.appendChild( this );
			this.style["top"] = "0px";
			this.style["left"] = "0px";
			//var container = DragDrop.firstContainer;
			//container.appendChild( this );
			return;
		}

		parent._contObj.options.onDragOut();
		parent._contObj.options.onDragDrop();
		element.style["top"] = "0px";
		element.style["left"] = "0px";
	}

};

var DragDrop= {
	containers: null,
	firstContainer : null,
	lastContainer : null,
	parent_id : null,
	parent_group : null,

	serData : function ( group, theid ) {
		var container = DragDrop.firstContainer;
		var j = 0;
		var string = "";

		while (container != null) {
			if(theid != null && container.id != theid)
			{
				container = container.nextContainer;
				continue;
			}

			if(group != null && container.group != group)
			{
				container = container.nextContainer;
				continue;
			}

			j ++;
			if(j > 1)
			{
				string += ":";
			}
			string += container.id;

			var items = container.getElementsByTagName( "li" );
			string += "(";
			for (var i = 0; i < items.length; i++) {
				if(i > 0)
				{
					string += ",";
				}
				string += items[i].id;
			}
			string += ")";

			container = container.nextContainer;
		}
		return string;
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
