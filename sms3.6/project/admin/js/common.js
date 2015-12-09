/*****************************
**   Common class methods
******************************/

function matchClass( objNode, strCurrClass ) {
	return ( objNode && objNode.className.length && objNode.className.match( new RegExp('(^|\\s+)(' + strCurrClass + ')($|\\s+)') ) );
}

/*****************************
**     Nodes functions
******************************/


function getElementsByClassName(objParentNode, strNodeName, strClassName){
	var nodes = objParentNode.getElementsByTagName(strNodeName);
	if(!strClassName){
		return nodes;	
	}
	var nodesWithClassName = [];
	for(var i=0; i<nodes.length; i++){
		if(matchClass( nodes[i], strClassName )){
			//nodesWithClassName.push(nodes[i]);
			nodesWithClassName[nodesWithClassName.length] = nodes[i];
		}	
	}
	return nodesWithClassName;
}

function getParentByClassName(element, className){
	var currentElement = element;
	while(currentElement.parentNode && !matchClass(currentElement.parentNode, className)){
		currentElement = currentElement.parentNode;
		if (currentElement.tagName.toLowerCase() == 'body') {
			return null;
			break;
		}
	}
	return currentElement.parentNode;
}

/*****************************
**   AJAX
******************************/

/*
	url - откуда загружаем
	ajaxCallBackFunction - что вызываем по завершении загрузки
	params - параметры в виде объекта или массива
	callObject - методом какого объекта является ajaxCallBackFunction (если это метод, а не глобальная фунция)
*/


function ajaxGet(
		url,
		ajaxCallBackFunction,
		params,
		callObject,
		ajaxCallBackErrorFunction) {
	// branch for native XMLHttpRequest object
	if (window.XMLHttpRequest) {
		var ajaxObject = new XMLHttpRequest();
		ajaxObject.onreadystatechange = function(){
			ajaxHandler(
				ajaxObject,
				ajaxCallBackFunction,
				params,
				callObject,
				ajaxCallBackErrorFunction);
		};
		ajaxObject.open("GET", url, true);
		ajaxObject.send(null);
	// branch for IE/Windows ActiveX version
	} else if (window.ActiveXObject) {
		var ajaxObject = new ActiveXObject("Microsoft.XMLHTTP");
		if (ajaxObject) {
			ajaxObject.onreadystatechange = function(){
				ajaxHandler(
					ajaxObject,
					ajaxCallBackFunction,
					params,
					callObject,
					ajaxCallBackErrorFunction);
			};
			ajaxObject.open("GET", url, true);
			ajaxObject.send();
		}
	}
}

function ajaxPost(
		url,
		data,
		ajaxCallBackFunction,
		params,
		callObject,
		ajaxCallBackErrorFunction) {
	var ajaxObject = null;
	
	if (window.XMLHttpRequest) { // branch for native XMLHttpRequest object
		ajaxObject = new XMLHttpRequest();
	} else if (window.ActiveXObject) { // branch for IE/Windows ActiveX version
		var ajaxObject = new ActiveXObject("Microsoft.XMLHTTP");
	}
	if(ajaxObject){
		ajaxObject.onreadystatechange = function(){
			ajaxHandler(
				ajaxObject,
				ajaxCallBackFunction,
				params,
				callObject,
				ajaxCallBackErrorFunction);
		}
		ajaxObject.open("POST", url, true);
		ajaxObject.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		ajaxObject.setRequestHeader("Content-length", data.length);
		ajaxObject.setRequestHeader("Connection", "close");
		ajaxObject.send(data);	
	}
}


function ajaxHandler(
		ajaxObject,
		ajaxCallBackFunction,
		params,
		callObject,
		ajaxCallBackErrorFunction){
	// only if req shows "complete"
	if (ajaxObject.readyState == 4) {
		// only if "OK"
		if (ajaxObject.status == 200) {
			// ...processing statements go here...
			ajaxCallBackFunction.call(callObject, ajaxObject, params);
		} else {
			if(ajaxCallBackErrorFunction){
				ajaxCallBackErrorFunction.call(callObject, ajaxObject);	
			} else {
				alert("",/*("Возникла ошибка в получении XML данных:<br />" + ajaxObject.statusText)*/ 'Упс! Что-то пошло не так. Попробуйте еще раз.', false, 'error');
			}
		}
	}
}

function ajaxLoadPost(url, data, ajaxCallBackFunction, callObject, params, ajaxCallBackErrorFunction) {
	var ajaxObject = null;

	if (window.XMLHttpRequest) { // branch for native XMLHttpRequest object
		ajaxObject = new XMLHttpRequest();
	} else if (window.ActiveXObject) { // branch for IE/Windows ActiveX version
		var ajaxObject = new ActiveXObject("Microsoft.XMLHTTP");
	}
	if(ajaxObject){
		ajaxObject.onreadystatechange = function(){
			ajaxLoadHandler(ajaxObject, ajaxCallBackFunction, callObject, params, ajaxCallBackErrorFunction);
		}
		ajaxObject.open("POST", url, true);
		ajaxObject.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		ajaxObject.setRequestHeader("Content-length", data.length);
		ajaxObject.setRequestHeader("Connection", "close");
		ajaxObject.send(data);
	}
}
function ajaxLoadHandler(ajaxObject, ajaxCallBackFunction, callObject, params, ajaxCallBackErrorFunction){
	// only if req shows "complete"
	if (ajaxObject.readyState == 4) {
		// only if "OK"
		if (ajaxObject.status == 200) {
			// ...processing statements go here...
			ajaxCallBackFunction.call(callObject, ajaxObject, params);
		} else {
			if(ajaxCallBackErrorFunction){
				ajaxCallBackErrorFunction.call(callObject, ajaxObject);	
			} else {
				alert("",("Возникла ошибка в получении XML данных:<br />" + ajaxObject.statusText), true, 'error');
			}
		}
	}
}

if(!Function.prototype.call) { // emulating 'call' function for browsers not supporting it (IE5)
	Function.prototype.call = function() {
		var oObject = arguments[0];
		var aArguments = [];
		var oResult;       
		oObject.fFunction = this;
		for (var i = 1; i < arguments.length; i++) {
			aArguments[aArguments.length] = 'arguments[' + i + ']';         
		}
		eval('oResult = oObject.fFunction(' + aArguments.join(',') + ')');
		oObject.fFunction = null;
		return oResult;
	}
};

function getCharCode(ev) {
	if (ev.charCode) var charCode = ev.charCode;
	else if (ev.keyCode) var charCode = ev.keyCode;
	else if (ev.which) var charCode = ev.which;
	else var charCode = 0;
	return charCode;
}

if (window.ActiveXObject) window.ie = window[window.XMLHttpRequest ? 'ie7' : 'ie6'] = true;
else if (document.childNodes && !document.all && !navigator.taintEnabled) window.webkit = window[window.xpath ? 'webkit420' : 'webkit419'] = true;
else if (document.getBoxObjectFor != null) window.gecko = true;



var isIE  = (navigator.appVersion.indexOf("MSIE") != -1) ? true : false;
var isWin = (navigator.appVersion.toLowerCase().indexOf("win") != -1) ? true : false;
var isOpera = (navigator.userAgent.indexOf("Opera") != -1) ? true : false;

function moveCaretToEnd(inputObject)
{
    if (inputObject.createTextRange)
    {
        var r = inputObject.createTextRange();
        r.collapse(false);
        r.select();
    }
    else if (inputObject.selectionStart)
    {
        var end = inputObject.value.length;
        inputObject.setSelectionRange(end,end);
        inputObject.focus();
    }
}