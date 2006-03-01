
var lastselected= null;

function display_properties(type)
{
	hidePropertyPanels();
	$('panel_' + type).style.display= "block";
}

function component_clicked(target_div)
{
	if( target_div.obj != lastselected )
	{
		displayloading();

		if( lastselected != null )
		{
			//lastselected._div.className= "component";
			Element.removeClassName(lastselected._div, 'component_selected');
			lastselected.savePropertyPanel();
			lastselected.updateContent();
		}

		display_properties(target_div.obj.type);

		lastselected= target_div.obj;
		lastselected.fillPropertyPanel();
		//lastselected._div.className= "component component_selected";
		Element.addClassName(lastselected._div, 'component_selected');
		$('disp_comp_id').innerHTML= lastselected._div.id;

		hideLoading();
	}
}

/*
// fix to prevent selection bug under ie
if (document.all)
{
  document.onselectstart= function(){
  	return false;
  };
}
*/

function max(a, b)
{
	if( a >= b )
	{
		return a;
	}
	else
	{
		return b;
	}
}

FormElement= {
	getValue: function(element){
		element= $(element);
		var method = element.tagName.toLowerCase();

		return FormElement.Getters[method](element);
	},

	setValue: function(element, newval){
		element= $(element);
		var method = element.tagName.toLowerCase();
		FormElement.Setters[method](element, newval);
	}
};

FormElement.Getters= {
	input: function(element) {
		switch (element.type.toLowerCase()) {
		case 'submit':
		case 'hidden':
		case 'password':
		case 'text':
			return FormElement.Getters.textarea(element);
		case 'checkbox':
		case 'radio':
			return FormElement.Getters.inputSelector(element);
		}

		return false;
	},

	inputSelector: function(element){
		return element.checked?'true':'false';
	},

	select: function(element) {
		return element.value;
	},

	textarea: function(element){
		return element.value;
	}
};

FormElement.Setters= {
	input: function(element, newval) {
		switch (element.type.toLowerCase()) {
		case 'submit':
		case 'hidden':
		case 'password':
		case 'text':
			element.value= newval;
			break;

		case 'checkbox':
		case 'radio':
			element.checked= (newval == 'true');
			break;
		}

		return false;
	},

	select: function(element, newval) {
		element.value= newval;
		/*
		for(var i= 0; i< element.options.length; i++)
		{
			alert( element.options[i].value + ' ' + newval );
			if( element.options[i].value == newval )
			{
				element.selectedIndex= i;
				break;
			}
		}*/
	}
};

Draggables.addObserver({
	onStart: function(event_name, obj, e){
		if( Element.hasClassName(obj.element, 'component') )
		{
			$('save_page').disabled= false;
		}
		//component_clicked(e.target); -> cause crash on ie...
	}
});


// loading indicator
function displayloading(){
	$('loading_indicator').style.display= 'block';
}

function hideLoading(){
	$('loading_indicator').style.display= 'none';
}


// sucess/failure display
function add_display_msg(text, bgcolor){
	var orig= document.getElementsByClassName('error_display')[0];
	var div= orig.cloneNode(true);
	var top= document.getElementById('alert_container');


	div.style.display= "block";
	div.style.backgroundColor= bgcolor;
	div.childNodes.item(1).nodeValue= text;

	//Element.setOpacity(div, 1.0);

	top.insertBefore(div, orig);

	Effect.Shake(div);
}


// cookies handling
function setCookie (name, value) {
	var argv=setCookie.arguments;
	var argc=setCookie.arguments.length;
	var expires=(argc > 2) ? argv[2] : null;
	var path=(argc > 3) ? argv[3] : null;
	var domain=(argc > 4) ? argv[4] : null;
	var secure=(argc > 5) ? argv[5] : false;
	document.cookie=name+"="+escape(value)+
		((expires==null) ? "" : ("; expires="+expires.toGMTString()))+
		((path==null) ? "" : ("; path="+path))+
		((domain==null) ? "" : ("; domain="+domain))+
		((secure==true) ? "; secure" : "");
}

function getCookieVal(offset) {
	var endstr=document.cookie.indexOf (";", offset);
	if (endstr==-1)
      		endstr=document.cookie.length;
	return unescape(document.cookie.substring(offset, endstr));
}

function getCookie (name) {
	var arg=name+"=";
	var alen=arg.length;
	var clen=document.cookie.length;
	var i=0;
	while (i<clen) {
		var j=i+alen;
		if (document.cookie.substring(i, j)==arg)
                        return getCookieVal (j);
                i=document.cookie.indexOf(" ",i)+1;
                        if (i==0) break;}
	return null;
}


var Page= Class.create();
Page.prototype.initialize= function(){};
Page.prototype.updateContent= function(){};

var page= new Page();

