


Draggable.prototype.draw= function(point) {
	var pos = Position.cumulativeOffset(this.element);
	var d= this.currentDelta();
	pos[0]-= d[0];
	pos[1]-= d[1];

	var w= this.element.clientWidth;
	var h= this.element.clientHeight;

	var off= [w/2, this.offset[1]];

	var p= [0,1].map(function(i){ return (point[i]-pos[i]-off[i]) }.bind(this));

	if(this.options.snap)
	{
		if(typeof this.options.snap == 'function')
		{
			p= this.options.snap(p[0],p[1]);
		}
		else
		{
			if(this.options.snap instanceof Array)
			{
				p= p.map( function(v, i) { return Math.round(v/this.options.snap[i])*this.options.snap[i] }.bind(this))
			}
			else
			{
				p= p.map( function(v) { return Math.round(v/this.options.snap)*this.options.snap }.bind(this))
			}
		}
	}

	var style= this.element.style;
	if((!this.options.constraint) || (this.options.constraint=='horizontal'))
	{
		style.left= p[0] + "px";
	}

	if((!this.options.constraint) || (this.options.constraint=='vertical'))
	{
		style.top= p[1] + "px";
	}

	if(style.visibility=="hidden")
	{
		style.visibility = ""; // fix gecko rendering
	}
}




