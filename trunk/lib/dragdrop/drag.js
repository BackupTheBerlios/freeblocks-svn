/*
 * drag.js - click & drag DOM elements
 *
 * originally based on Youngpup's dom-drag.js, www.youngpup.net
 */

/**********************************************************
 Further modified from the example by Tim Taylor
 http://tool-man.org/examples/sorting.html

 Changed onMouseMove where it calls group.onDrag and then
 adjusts the offset for changes to the DOM.  If the item
 being moved changed parents it would be off so changed to
 get the absolute offset (recursive northwestOffset).

 **********************************************************/

var DraggableItem= Class.create();
DraggableItem.prototype= {

	initialize: function(htmlElement){
		htmlElement= $(htmlElement);

		log('new draggable: ' + htmlElement.id);

		this.element= htmlElement;
		this.element._dragObj= this;

		this.options= Object.extend({

			handle: htmlElement,
			minX: null,
			minY: null,
			maxX: null,
			maxY: null,
			threshold: 0,
			thresholdY: 0,
			thresholdX: 0,

			onDragStart: function(){},
			onDragEnd: function(){},
			onDrag: function(){},

			_top: this

		}, arguments[1] || {});

		Event.observe(this.options.handle, "mousedown", this.onMouseDown);
	},

	constrainVertical : function() {
		var nwOffset = Coordinates.northwestOffset(this, true);
		this.options.minX = nwOffset.x;
		this.options.maxX = nwOffset.x;
	},

	constrainHorizontal : function() {
		var nwOffset = Coordinates.northwestOffset(this, true);
		this.options.minY = nwOffset.y;
		this.options.maxY = nwOffset.y;
	},



	onMouseDown: function(event) {
		if( Event.isLeftClick(event) )
		{
			event= Drag.fixEvent(event);
			var element= Event.element(event);
			var obj= element._dragObj;
			Drag.dragged_obj= obj;


			var mouse= 		event.windowCoordinate;

			var nwOffset= 	Coordinates.northwestOffset(element, true);
			var nwPosition= Coordinates.northwestPosition(element);
			var sePosition= Coordinates.southeastPosition(element);
			var seOffset= 	Coordinates.southeastOffset(element, true);

			obj.originalOpacity= 	Element.getOpacity(element);
			obj.originalZIndex= 	Element.getStyle(element, 'z-index');
			obj.initialWindowCoordinate= mouse;
			// TODO: need a better name, but don't yet understand how it
			// participates in the magic while dragging
			obj.dragCoordinate= mouse;
			obj.options.onDragStart(nwPosition, sePosition, nwOffset, seOffset);

			// TODO: need better constraint API
			if( obj.options.minX != null )
				obj.minMouseX= mouse.x - nwPosition.x + obj.options.minX - nwOffset.x;

			if( obj.options.maxX != null )
				obj.maxMouseX= obj.minMouseX + obj.options.maxX - obj.options.minX;


			if( obj.options.minY != null )
				this.minMouseY= mouse.y - nwPosition.y + obj.options.minY - nwOffset.y;

			if( obj.options.maxY != null )
				obj.maxMouseY = obj.minMouseY + obj.options.maxY - obj.options.minY;

			obj.mouseMin= new Coordinate(obj.minMouseX, obj.minMouseY);
			obj.mouseMax= new Coordinate(obj.maxMouseX, obj.maxMouseY);

			Event.observe(document, "mousemove", 	Drag.onMouseMove);
			Event.observe(document, "mouseup", 		Drag.onMouseUp);

			return false;
		}
	}

};

var Drag = {
	BIG_Z_INDEX: 10000,
	dragged_obj: null,
	isDragging: false,

	makeDraggable : function(item) {

		with(group){

			/*
			constrain = Drag.constrain;
			setDragHandle = Drag.setDragHandle;
			setDragThreshold = Drag.setDragThreshold;
			setDragThresholdX = Drag.setDragThresholdX;
			setDragThresholdY = Drag.setDragThresholdY;
			constrainVertical = Drag.constrainVertical;
			constrainHorizontal = Drag.constrainHorizontal;
			*/
		}
	},



	showStatus : function(mouse, nwPosition, sePosition, nwOffset, seOffset) {
		window.status =
				"mouse: " + mouse.toString() + "    " +
				"NW pos: " + nwPosition.toString() + "    " +
				"SE pos: " + sePosition.toString() + "    " +
				"NW offset: " + nwOffset.toString() + "    " +
				"SE offset: " + seOffset.toString();
	},

	onMouseMove : function(event) {
		event = Drag.fixEvent(event);

		var obj= Drag.dragged_obj;
		var element= obj.element;

		var mouse = event.windowCoordinate;

		var nwOffset = Coordinates.northwestOffset(element, true);

		var nwPosition = Coordinates.northwestPosition(element);
		var sePosition = Coordinates.southeastPosition(element);
		var seOffset = Coordinates.southeastOffset(element, true);



		Drag.showStatus(mouse, nwPosition, sePosition, nwOffset, seOffset);

		if( !Drag.isDragging ) {
			if( obj.options.threshold > 0 ){
				var distance= obj.initialWindowCoordinate.distance(mouse);
				if( distance < obj.options.threshold ){
					return true;
				}
			} else if( obj.options.thresholdY > 0 ){
				var deltaY= Math.abs(obj.initialWindowCoordinate.y - mouse.y);
				if( deltaY < obj.options.thresholdY ){
					return true;
				}
			} else if( obj.options.thresholdX > 0 ){
				var deltaX= Math.abs(obj.initialWindowCoordinate.x - mouse.x);
				if( deltaX < obj.options.thresholdX ){
					return true;
				}
			}

			Drag.isDragging= true;
			Element.setStyle(element, { zIndex: Drag.BIG_Z_INDEX });
			Element.setOpacity(element, 0.75);
		}

		// TODO: need better constraint API
		var adjusted= mouse.constrain(obj.mouseMin, obj.mouseMax);
		nwPosition= nwPosition.plus(adjusted.minus(obj.dragCoordinate));
		nwPosition.reposition(element);
		obj.dragCoordinate= adjusted;

		// once dragging has started, the position of the group
		// relative to the mouse should stay fixed.  They can get out
		// of sync if the DOM is manipulated while dragging, so we
		// correct the error here
		//
		// TODO: what we really want to do is find the offset from
		// our corner to the mouse coordinate and adjust to keep it
		// the same

		// changed to be recursive/use absolute offset for corrections
		var offsetBefore= Coordinates.northwestOffset(element, true);
		obj.options.onDrag(nwPosition, sePosition, nwOffset, seOffset);
		var offsetAfter= Coordinates.northwestOffset(element, true);

		if( !offsetBefore.equals(offsetAfter) ){
			var errorDelta= offsetBefore.minus(offsetAfter);
			nwPosition= Coordinates.northwestPosition(element).plus(errorDelta);
			nwPosition.reposition(element);
		}


		return false;
	},

	onMouseUp : function(event){
		event= Drag.fixEvent(event);
		var obj= Drag.dragged_obj;
		var element = obj.element;

		var mouse= event.windowCoordinate;
		var nwOffset= Coordinates.northwestOffset(element, true);
		var nwPosition= Coordinates.northwestPosition(element);
		var sePosition= Coordinates.southeastPosition(element);
		var seOffset= Coordinates.southeastOffset(element, true);

		Event.stopObserving(document, "mousemove", 	Drag.onMouseMove);
		Event.stopObserving(document, "mouseup", 	Drag.onMouseUp);

		obj.options.onDragEnd(nwPosition, sePosition, nwOffset, seOffset);

		if( Drag.isDragging ){
			// restoring zIndex before opacity avoids visual flicker in Firefox
			Element.setStyle(element, { zIndex: obj.originalZIndex });
			Element.setOpacity(element, obj.originalOpacity);
		}

		Drag.dragged_obj= null;
		Drag.isDragging= false;

		return false;
	},

	fixEvent : function(event) {
		if (typeof event == 'undefined') event = window.event;
		Coordinates.fixEvent(event);

		return event;
	}
};
