function getCoordinates(elementeg) {
	var coordinateLeft = 0;
	var coordinateTop = 0;
	while(elementeg.offsetParent) {
		coordinateLeft += elementeg.offsetLeft;
		coordinateTop += elementeg.offsetTop;
		elementeg = elementeg.offsetParent;
	}
	return {x: coordinateLeft, y: coordinateTop}	
}

function showImage(e, src, el) {
	var coordinates =  getCoordinates(el);
	 $('img_cont').style.border = '1px solid #ababab'
	 $('img_cont').style.position = 'absolute';
	 $('img_cont').style.zIndex = 5000;	 
	
	var img = $('image').firstChild;
	Element.show('image_loading');
	img.src = src;
	loaded = false;
	$('img_cont').style.top =  coordinates.y + "px";
	Event.observe(img, 'load', function() {
			$('img_cont').style.left = coordinates.x - img.width - 1 + "px";
			Element.hide('image_loading');
			Element.show('image');
			loaded = true;
	}
	)
}


function hideImage() {
	Element.hide('image');
	 $('img_cont').style.border = '0px';
}


 function ShowMail(user, domain1, domain2) {
	var email = user+'&#64;'+domain1+'&#46;'+domain2;
	if (ShowMail.arguments[3]) name = ShowMail.arguments[3];
	else name = email;
	document.writeln('<a href="mailto:'+email+'">'+name+'</a>');
}
function hideSelect(){
	var elements = document.getElementsByTagName('select');
	for (var i in elements){
		if (elements[i].style == undefined) continue;
		elements[i].style.visibility = 'hidden';
	}	
}
function showSelect(){
	var elements = document.getElementsByTagName('select');
	for (var i in elements){
		if (elements[i].style == undefined) continue;
		elements[i].style.visibility = 'visible';
	}	
}


function paramsNode(id, last){
	if (document.getElementById('param_'+id).style.display == "none"){
		document.getElementById('param_'+id).style.display = "block";
		if (last) document.getElementById('img_'+id).src = '/images/toc_opened_' + last + '.gif';
	} else {
		document.getElementById('param_'+id).style.display="none";
		if (last) document.getElementById('img_'+id).src = '/images/toc_closed_' + last + '.gif';
	}
}

function openNode(id){
	document.getElementById('node_'+id).style.display = 'block';
	var img = document.getElementById('img_'+id);
	r = img.src.lastIndexOf('closed');
	img.src = img.src.substring(0,r)+'opened'+img.src.substring(r+6);
	img.parentElement.onclick = function(){
		closeNode(img.id.substring(img.id.lastIndexOf('_')+1));
	}
}

function closeNode(id){
	document.getElementById('node_'+id).style.display = 'none';
	var img = document.getElementById('img_'+id);
	r = img.src.lastIndexOf('opened');
	img.src = img.src.substring(0,r)+'closed'+img.src.substring(r+6);
	img.parentElement.onclick = function(){
		openNode(img.id.substring(img.id.lastIndexOf('_')+1));
	}
}



var myTimer = 0;


function mouseCoords(e){
  var x = 0, y = 0;

  if (!e) e = window.event;

  if (e.pageX || e.pageY)
  {
    x = e.pageX;
    y = e.pageY;
  }
  else if (e.clientX || e.clientY)
  {
    x = e.clientX + (document.documentElement.scrollLeft || document.body.scrollLeft) - document.documentElement.clientLeft;
    y = e.clientY + (document.documentElement.scrollTop || document.body.scrollTop) - document.documentElement.clientTop;
  }

  return {"x":x, "y":y};
}

function getPageScroll() {
  var x,y;
  if (self.pageYOffset) {// all except Explorer
    x = self.pageXOffset;
    y = self.pageYOffset;
  } else if (document.documentElement && document.documentElement.scrollTop) {// Explorer 6 Strict
    x = document.documentElement.scrollLeft;
    y = document.documentElement.scrollTop;
  } else if (document.body) {// all other Explorers
    x = document.body.scrollLeft;
    y = document.body.scrollTop;
  }
  return {x: x, y: y};
}

function openUrl(url){
	//SetCookie('PHPSESSID',this.sid);
	window.open(url, "_blank", "").focus();	
}
function openGUrl(url){
	window.open("http://"+url, "_blank", "").focus();	
}
function openSupport(id){
	//SetCookie('PHPSESSID',this.sid);
	window.location = "?id="+id;
}


var products = new Array();




function checkAllCompare(form, checked) {
	if (!form) return false;
	for (var i in form.elements) {
		if (form.elements[i]) {
			if (form.elements[i].type == 'checkbox') {
				form.elements[i].checked = checked;
			}
		}
	}
}


function openImage(image, ev) {
    var ev = window.event || ev;
    if (ev == undefined) {
	   if (image) window.open("/popup.php?img="+image, "popupimage", "scrollbars=0,resizable=0,width=100,height=100,location=0,menubar=0,status=0,toolbar=0").focus();
	   return false;
    }
    var obj = ev.srcElement || ev.target;
    if (obj.tagName == 'IMG') obj = obj.parentNode;
    return hs.expand(obj);
}



// swith color theme
function SetCookie(sName, sValue){
	document.cookie = sName + "=" + escape(sValue) + "; expires=Fri, 31 Dec 2070 23:59:59 GMT; path=/;";
}

function GetCookie(sName) {
	// cookies are separated by semicolons
	var aCookie = document.cookie.split("; ");
	for (var i=0; i < aCookie.length; i++) {
		// a name/value pair (a crumb) is separated by an equal sign
		var aCrumb = aCookie[i].split("=");
		if (sName == aCrumb[0]) {
		  return unescape(aCrumb[1]);
		}
	}
	// a cookie with the requested name does not exist
	return null;
}

// file input
function fileInput() {
	var file = $('div.elemBox.file input[type="file"]');
	var inputText = file.parent().next().children('input[type="text"]');

	file.change(function () {
		var fileVal = $(this).val();
		inputText.attr('value', fileVal);
	});
}