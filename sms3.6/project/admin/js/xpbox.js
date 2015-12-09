var agent = navigator.userAgent.toLowerCase();
var major = parseInt(navigator.appVersion);
var minor = parseFloat(navigator.appVersion);

var isNN = ((agent.indexOf('mozilla') != -1) && ((agent.indexOf('spoofer') == -1) && (agent.indexOf('compatible') == -1)));
var isOPERA = agent.indexOf("opera")>-1 && window.opera;
var isIE = (agent.indexOf("msie") != -1 && !isOPERA);

var _oDivBody = new Array(), _oTdBody = new Array();
var _oImg = new Array(), _oImg_u = new Array(), _oImg_p = new Array();
var _bShown = new Array();
var _nPInt = new Array(), _nPCur = new Array(), _ScrollHeight = new Array();
var _nFrom = new Array(), _nTo = new Array();
var _nUInt = new Array();

var _nPCoeff = .75;
var _nPTimes = 50;
var _nPSpeed = 20;

//-- public methods
function fnShowHide(i) {
    try {
    	if (_bShown[i]) {
    		_ScrollHeight[i] = parseInt(_oDivBody[i].scrollHeight);
    		_oImg[i].src = _oImg_u[i].src;
    		fnResizeTo(0, i);
    		_bShown[i] = false;
    	}
    	else {
    		_oImg[i].src = _oImg_p[i].src;
    		fnResizeTo(_ScrollHeight[i], i);
    		_bShown[i] = true;
    	}
    } catch (e) {}
}

//-- private methods
function fnResizeTo(n, i) {
    if (_oDivBody[i].style.posHeight != undefined) _nFrom[i] = parseInt(_oDivBody[i].style.posHeight) - 1;
    else if (_oDivBody[i].scrollHeight != undefined) _nFrom[i] = parseInt(_oDivBody[i].scrollHeight) - 1;
    
	_nTo[i] = n;
	_nPCur[i] = 0;

	_oDivBody[i].style.display = 'block';
	if (_nTo[i] - _nFrom[i] == 1 || _nTo[i] - _nFrom[i] == -1)	return;

	var bDir	= _nFrom[i] < _nTo[i] ? true : false;

	if (_nPInt[i]) {
		clearInterval(_nPInt[i]);
	}
	_nPInt[i] = setInterval(new Function ("fnProceed(" + bDir + ", " + i + ");"), _nPSpeed);
}

function fnProceed(bDir, i) {
	if (_nPCur[i] > _nPTimes) {
		clearInterval(_nPInt[i]);
	}
	else {
		_nPCur[i]++;
		var coef	= Math.pow(_nPCoeff, _nPCur[i]);
		_oDivBody[i].style.height	= _nFrom[i] + Math.ceil((1 - coef) * (_nTo[i] - _nFrom[i])) + "px";
		//_oDivBody[i].style.posHeight	= _nFrom[i] + Math.ceil((1 - coef) * (_nTo[i] - _nFrom[i]));
	}
}

function fnFalse() {

	return false;
}

function fnInit(element, i) {

	element.oncontextmenu = fnFalse();

	_oTdBody[i] = element.rows[1].cells[0];
	_oDivBody[i] = _oTdBody[i].childNodes[1];
	_oDivBody[i].style.overflow = "hidden";

	_ScrollHeight[i] = _oDivBody[i].scrollHeight;

	//_oDivBody[i].style.height = _ScrollHeight[i] + "px";
    _oDivBody[i].style.posHeight = _ScrollHeight[i];

	_bShown[i] = true;

	element.rows[0].onclick = function () {
        fnShowHide(i);
	}

	_oImg[i]	= element.rows[0].cells[0].childNodes[1].rows[0].cells[2].childNodes[0];

	_oImg_u[i] = new Image();
	_oImg_u[i].src = "images/xpbox/down.gif";
	_oImg_p[i] = new Image();
	_oImg_p[i].src = "images/xpbox/up.gif";

	if (element.summary == 'hidden') {
		//_ScrollHeight[i] = parseInt(_oDivBody[i].style.height);
		_ScrollHeight[i] = _oDivBody[i].scrollHeight;
		_oImg[i].src = _oImg_u[i].src;
		//_oDivBody[i].style.height = "2px";
		_oDivBody[i].style.posHeight = 2;
		_bShown[i] = false;
	}

	_bCreated	= true;
}