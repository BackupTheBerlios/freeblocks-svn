

/*
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
*/

Effect.Center = function(element)
{
	try
	{
		element = $(element);
	}
	catch(e)
	{
		return;
	}

	var my_width  = 0;
	var my_height = 0;

	if ( typeof( window.innerWidth ) == 'number' )
	{

		my_width  = window.innerWidth;
		my_height = window.innerHeight;
	}
	else if ( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) )
	{

		my_width  = document.documentElement.clientWidth;
		my_height = document.documentElement.clientHeight;
	}
	else if ( document.body && ( document.body.clientWidth || document.body.clientHeight ) )
	{

		my_width  = document.body.clientWidth;
		my_height = document.body.clientHeight;
	}


	element.style.position = 'absolute';
	element.style.display  = 'block';
	element.style.zIndex   = 99;


	var divheight = parseInt( element.style.Height );
	var divwidth  = parseInt( element.style.Width );

	divheight = divheight ? divheight : 200;
	divwidth  = divwidth  ? divwidth  : 150;

	var scrollY = 0;

	if ( document.documentElement && document.documentElement.scrollTop )
	{
		scrollY = document.documentElement.scrollTop;
	}
	else if ( document.body && document.body.scrollTop )
	{
		scrollY = document.body.scrollTop;
	}
	else if ( window.pageYOffset )
	{
		scrollY = window.pageYOffset;
	}
	else if ( window.scrollY )
	{
		scrollY = window.scrollY;
	}


	var setX = ( my_width  - divwidth  ) / 2;
	var setY = ( my_height - divheight ) / 2 + scrollY;

	setX = ( setX < 0 ) ? 0 : setX;
	setY = ( setY < 0 ) ? 0 : setY;

	element.style.left = setX + "px";
	element.style.top  = setY + "px";

}



