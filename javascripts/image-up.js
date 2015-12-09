function PopupWindow(theURL,winName,features) {
	window.open(theURL,winName,features);
}

function positionPopup(parentEle, popupEle, relativeLeft, relativeTop) {
	if(parentEle && popupEle) {
	
		if(parentEle.style.display=='none') {
			parentEle.style.display='block';
			var pos=Position.cumulativeOffset(parentEle);
			parentEle.style.display='none';
		} else {
			var pos=Position.cumulativeOffset(parentEle);
		}
		var left=(pos[0]+relativeLeft)+"px";
		var top=(pos[1]+relativeTop)+"px";
		popupEle.style.left=left;
		popupEle.style.top=top;
		
	
		
		if($(popupEle.id+"IFrame")) {
			var iFrame=$(popupEle.id+"IFrame");
			iFrame.style.left=left;
			iFrame.style.top=top;
		}
	}
}
function buildPopupIFrame(popupEle, allBrowsers) {
	if( (navigator.appName=="Microsoft Internet Explorer" || allBrowsers) && popupEle && !$(popupEle.id+"IFrame")) {
		var iFrame = document.createElement("iframe");
		iFrame.id=popupEle.id+"IFrame";
		iFrame.src="blank.html";
		iFrame.style.border="1px solid red";	
		iFrame.style.display="none";
		iFrame.style.position="absolute";
		iFrame.style.filter='progid:DXImageTransform.Microsoft.Alpha(style=0,opacity=0)';
		document.body.appendChild(iFrame);
	}
}
function togglePopup(popupEle, newState, zIndex) {
	if(popupEle) {		
		if(newState) {
			newState = (newState=="block"?"block":"none");
		}else {
			newState = (popupEle.style.display == "block" ? "none" : "block");
		}
		popupEle.style.display=newState;	
		
		var popupIFrame = $(popupEle.id+"IFrame");
		if(popupIFrame) {
			if(!popupIFrame.style.width) {
				popupIFrame.style.width=popupEle.offsetWidth+"px";
				popupIFrame.style.height=popupEle.offsetHeight+"px";
				if(!zIndex) {
					zIndex = 1500;
				}
				popupEle.style.zIndex=zIndex;
				popupIFrame.style.zIndex=popupEle.style.zIndex-1;
			}
			popupIFrame.style.display=newState;
		}
	}
}
function fixSafariEncode(s){
	var fixedString = s;
	try {
		fixedString = decodeURI(escape(fixedString));
	}
	catch(err){
	
	}
	return fixedString;
}


function showPopupBox(popupElement, e, popupResetCallback, isModal){
	if ( typeof popupElement == 'string' ){
		popupElement = document.getElementById(popupElement);
	}
	
	movePopupBox(popupElement, e);
	popupElement.style.visibility="visible";
	if ( isModal && popupResetCallback ){
		document.getElementById('popupBoxModalBackground').onclick = function (modalE){
			popupResetCallback(popupElement, modalE);
			popupElement.style.visibility = 'hidden';
			document.getElementById('popupBoxModalBackground').style.visibility = 'hidden';
		};
		document.getElementById('popupBoxModalBackground').style.visibility = 'visible';
	}
}
function hidePopupBox(popupElement){
	if ( typeof popupElement == 'string' ){
		popupElement = document.getElementById(popupElement);
	}
	popupElement.style.visibility="hidden";
	document.getElementById('popupBoxModalBackground').style.visibility = 'hidden';
}
function movePopupBox(popupElement, e){
	var xcoord = popupBoxOffsetFromMouse[0];
	var ycoord = popupBoxOffsetFromMouse[1];
	var docwidth = getDocWidth();
	var docheight = getDocHeight();
	if (typeof e != "undefined"){
	
		var x = 0;
		var y = 0;	
		
		if(e.pageX || e.pageY) {
			x = e.pageX;
			y = e.pageY;
		} else if (e.clientX || e.clientY) {
			x = e.clientX;
			y = e.clientY;
		
		
		}
		
				
		if (docwidth - x < popupElement.offsetWidth + xcoord){
			xcoord = x - xcoord - popupElement.offsetWidth;
		} else {
			xcoord += x;
		} 
					
		if (docheight + getTrueBody().scrollTop - y < popupElement.offsetHeight + ycoord){
			ycoord = y - ycoord - popupElement.offsetHeight;
		} else {
			ycoord += y;
		}
	} else if (typeof window.event != "undefined"){
	
		if (docwidth - event.clientX < popupElement.offsetWidth + xcoord){
			xcoord = event.clientX - xcoord - popupElement.offsetWidth + getTrueBody().scrollLeft;
		} else {
			xcoord += event.clientX + getTrueBody().scrollLeft;
		}
		if (docheight - event.clientY < popupElement.offsetHeight + ycoord){
			ycoord = event.clientY - ycoord - popupElement.offsetHeight + getTrueBody().scrollTop;
		} else {
			ycoord += event.clientY + getTrueBody().scrollTop;
		}
	}	else {
		return;
	}
	
	popupElement.style.left = xcoord+"px"
	popupElement.style.top = ycoord+"px"
}
var SIDE_TOP = 1;
var SIDE_BOTTOM = 2;
var SIDE_LEFT = 4;
var SIDE_RIGHT = 8;
var QUADRANT_TOP_LEFT = SIDE_TOP | SIDE_LEFT;
var QUADRANT_TOP_RIGHT = SIDE_TOP | SIDE_RIGHT;
var QUADRANT_BOTTOM_LEFT = SIDE_BOTTOM | SIDE_LEFT;
var QUADRANT_BOTTOM_RIGHT = SIDE_BOTTOM | SIDE_RIGHT;
function whichQuadrant(pageX, pageY, pointX, pointY){
	var quadrant = 0;
	var halfX = pageX/2;
	var halfY = pageY/2;
	if ( pointX < halfX ){
		quadrant |= SIDE_LEFT;
	} else {
		quadrant |= SIDE_RIGHT;
	}
	if ( pointY < halfY ){
		quadrant |= SIDE_TOP;
	} else {
		quadrant |= SIDE_BOTTOM;
	}
	return quadrant;
}
function getDocWidth(){
	alert(getTrueBody().scrollLeft+getTrueBody().clientWidth);
	return (document.all? getTrueBody().scrollLeft+getTrueBody().clientWidth : pageXOffset+window.innerWidth-15);

}
function getDocHeight(){
	return (document.all? Math.min(getTrueBody().scrollHeight, getTrueBody().clientHeight) : Math.min(window.innerHeight));
}
function getTrueBody(){
	return ( (!window.opera && document.compatMode && document.compatMode!="BackCompat") || window.opera)? document.documentElement : document.body
}

var offsetfrommouse=[30,-170];
var displayduration=0;
var currentimageheight = 270;
if (document.getElementById || document.all){
	document.write('<div id="trailimageid">');
	document.write('</div>');
}
function gettrailobj(){
if (document.getElementById)
return document.getElementById("trailimageid").style
else if (document.all)
return document.all.trailimagid.style
}
function gettrailobjnostyle(){
if (document.getElementById)
return document.getElementById("trailimageid")
else if (document.all)
return document.all.trailimagid
}
function truebody(){
return (!window.opera && document.compatMode && document.compatMode!="BackCompat")? document.documentElement : document.body
}

function showtrail(imagename,title,width){
	height = currentimageheight;
	document.onmousemove=followmouse;
	newHTML = '<div style="padding: 5px; background-color: #FFF; border: 1px solid #b1b1b1; width:404px; position:relative;"><div class="arrowDrop"></div>';
	newHTML = newHTML + '<div align="center" style="padding: 8px 2px 8px 2px;">';
	newHTML = newHTML + '<img src="' + imagename + '"';
	newHTML = newHTML + ' width="' + width + '"';
	newHTML = newHTML + ' border="0" /></div>';
	newHTML = newHTML + '<span>' + title + '</span>';
	newHTML = newHTML + '</div>';
	gettrailobjnostyle().innerHTML = newHTML;
	gettrailobj().display="inline";
}

function hidetrail(){
	gettrailobj().innerHTML = " ";
	gettrailobj().display="none"
	document.onmousemove=""
	gettrailobj().left="-500px"
}
function showtraill(text,zice){
//	height = currentimageheight;
	document.onmousemove=show_hint;
	newHTML = '<div style="padding: 1px; font-size:' + zice + 'px; background-color: #FFF; border: 1px solid #b1b1b1; width:100px; position:relative;"><div class="arrowDrop"></div>';
//	newHTML = newHTML + '<div align="center" style="padding: 5px 2px 5px 1px;">';
//	newHTML = newHTML + '<img src="' + imagename + '"';
//	newHTML = newHTML + ' width="' + width + '"';
//	newHTML = newHTML + ' border="0" /></div>';
    newHTML = text;
//	newHTML = newHTML + '<span>' + title + '</span>';
	newHTML = newHTML + '</div>';
	gettrailobjnostyle().innerHTML = newHTML;
	gettrailobj().display="inline";
}

function hidetraill(){
	gettrailobj().innerHTML = " ";
	gettrailobj().display="none"
	document.onmousemove=""
	gettrailobj().left="0px"
}
function followmouse(e){
	var xcoord=offsetfrommouse[0]
	var ycoord=offsetfrommouse[1]
	var docwidth=document.all? truebody().scrollLeft+truebody().clientWidth : pageXOffset+window.innerWidth-15
	var docheight=document.all? Math.min(truebody().scrollHeight, truebody().clientHeight) : Math.min(window.innerHeight)

	if (typeof e != "undefined"){
		if (docwidth - e.pageX < 380){
			xcoord = e.pageX - xcoord - 400;
		} else {
			xcoord += e.pageX;
		}
		if (docheight - e.pageY < (currentimageheight + 110)){
		
			if ( document.body ){
				scrollTop = Math.max(truebody().scrollTop, document.body.scrollTop,document.documentElement.scrollTop);
			} else {
				scrollTop = truebody().scrollTop;
			}
			ycoord += e.pageY - Math.max(0,(110 + currentimageheight + e.pageY - docheight - scrollTop));
		} else {
			ycoord += e.pageY;
		}
	} else if (typeof window.event != "undefined"){
		if (docwidth - event.clientX < 380){
			xcoord = event.clientX + truebody().scrollLeft - xcoord - 400;
		} else {
			xcoord += truebody().scrollLeft+event.clientX
		}
		if (docheight - event.clientY < (currentimageheight + 110)){
			ycoord += event.clientY + truebody().scrollTop - Math.max(0,(110 + currentimageheight + event.clientY - docheight));
		} else {
			ycoord += truebody().scrollTop + event.clientY;
		}
	}
	if(ycoord < 0) { ycoord = ycoord*-1; }
	gettrailobj().left=xcoord-100+"px"
	gettrailobj().top=ycoord+163+"px"
}



document.getElementById("block").style.display = 'none';

function show(text, evt) {
evt = (evt) ? evt : event;
    if (evt) {
var elem = document.getElementById("block");
  elem.innerHTML = text;
  elem.style.display = 'block';
  elem.style.position = 'absolute';
  var coords = getEventCoords(evt);
  elem.style.left = coords.left;
  elem.style.top = coords.top;
    }
}
function getEventCoords(evt) {
    var coords = {left:1, top:1};
    if (evt.pageX) {
        coords.left = evt.pageX;
        coords.top = evt.pageY;
    } else if (evt.clientX) {
        coords.left = evt.clientX + document.body.scrollLeft - document.body.clientLeft;
        coords.top = evt.clientY + document.body.scrollTop - document.body.clientTop;
        if (document.body.parentElement && document.body.parentElement.clientLeft) {
            var bodParent = document.body.parentElement;
            coords.left += bodParent.scrollLeft - bodParent.clientLeft;
            coords.top += bodParent.scrollTop - bodParent.clientTop;
        }
    }
    return coords;
}
function hide() {
 var elem = document.getElementById("block").style;
 elem.display = 'none';
}







