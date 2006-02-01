
var lastselected= null;


function component_clicked(target_div)
{
	if( target_div.obj != lastselected )
	{
		if( lastselected != null )
		{
			//lastselected._div.className= "component";
			Element.addClassName(lastselected._div, 'component_selected');
			lastselected.savePropertyPanel();
			lastselected.updateContent();
		}

		hidePropertyPanels();
		$('panel_' + target_div.obj._class_name).style.display= "block";

		lastselected= target_div.obj;
		lastselected.fillPropertyPanel();
		//lastselected._div.className= "component component_selected";
		Element.addClassName(lastselected._div, 'component_selected');
	}
}


// fix to prevent selection bug under ie
if (document.all)
{
  document.onselectstart= function(){
  	return false;
  };
}

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

