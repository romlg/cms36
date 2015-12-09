var agent = navigator.userAgent.toLowerCase();
var major = parseInt(navigator.appVersion);
var minor = parseFloat(navigator.appVersion);

var isNN = ((agent.indexOf('mozilla') != -1) && ((agent.indexOf('spoofer') == -1) && (agent.indexOf('compatible') == -1)));
var isOPERA = agent.indexOf("opera")>-1 && window.opera;
var isIE = (agent.indexOf("msie") != -1 && !isOPERA);

function addEventListener(element, strEvent, callback){
    if (element.addEventListener) 
        element.addEventListener(strEvent, callback, false);
    else
        element.attachEvent("on" + strEvent, callback);
}
    
function fireClick(element){
    if( document.createEvent ) {
        var evObj = document.createEvent('MouseEvents');
        evObj.initEvent( 'click', true, false );
        element.dispatchEvent(evObj);
    } else if( document.createEventObject ) {
        element.fireEvent('onclick');
    }
}

var toc_line = new Image;
toc_line.src = 'images/tree/toc_line.gif';
var toc_closed_whole = new Image;
toc_closed_whole.src = 'images/tree/toc_closed_whole.gif';
var itemId, itemPid, oItem, oDragSrc, iTID, bNoDeAct, oTrg, DragStatus;
var toolTip = new toolTip('toolTip', 100);

function swapItems(dir) {
	if (itemId && dir) {
		document.getElementById('toc').src = 'page.php?page=tree&do=swapItems&src='+itemId+'&move='+dir;
	}
}

function copyItem() {
	targetId = window.showModalDialog('dialog.php?page=tree/node_id', '', 'dialogWidth:360px; dialogHeight:400px;status:no;scroll:no;help:no;');
	if (itemId && targetId) {
		document.getElementById('toc').src = 'page.php?page=tree&do=copy&src_id=' + itemId + '&trg_id=' + targetId;
	}
}

function moveItem() {
	targetId = window.showModalDialog('dialog.php?page=tree/node_id', '', 'dialogWidth:360px; dialogHeight:400px;status:no;scroll:no;help:no;');
	if (itemId && targetId) {
		document.getElementById('toc').src = 'page.php?page=tree&do=move&src_id=' + itemId + '&trg_id=' + targetId;
	}
}

function expandNode(nId, preserve) {
	preserve = preserve || false;
	var oDiv = document.getElementById('div_' + nId);
	var oLoad = document.getElementById('load_' + nId);
	var oLink = document.getElementById('link_' + nId);
	
	if (!oLoad) return false; 

	oLink.setAttribute('expanded', 0);

	if (oDiv.getAttribute('loaded') == 1) {
		var d, s;
		var img = document.getElementById('img_' + nId);
		if (oLoad.style.display == '' && (oLink.getAttribute('active') || preserve)) {
			d = 'none';
			s = img.src.replace(/opened/g, 'closed');
		}
		else {
			d = '';
			s = img.src.replace(/closed/g, 'opened');
			oLink.setAttribute('expanded', 1);
		}
		oLoad.style.display = d;
		img.src = s;

		//if (oItem.className != 'open') oItem.className = 'open';
		//else oItem.className = '';

		return false;
	}

	if (!oLoad) {
		return false;
	}
	oLoad.className= 'vis';
	//if (oItem.className != 'open') oItem.className = 'open';
	//else oItem.className = '';

	oLink.setAttribute('expanded', 1);
	return true;
}

function reloadNode(pid) {
	var oParentDiv = document.getElementById('div_' + pid);
	if (!oParentDiv) {
		return;
	}

	focusItem();
	oParentDiv.setAttribute('loaded', 0);
	fireClick(oParentDiv.lastChild);

	var href = oParentDiv.childNodes[4] != undefined ? oParentDiv.childNodes[4].href : oParentDiv.childNodes[3].href;
	document.getElementById('toc').src = href;
}

function itemExpand() {
	if (oTrg.parentElement && oTrg.parentElement.getAttribute('expanded') == 0) {
	    fireClick(oTrg.parentElement);
		//oTrg.parentElement.click();
	}
}

function itemDragStart(item) {
	window.event.dataTransfer.effectAllowed = 'all';
	oDragSrc = window.event.srcElement;
	itemActivate(oDragSrc);
}

function itemDragOver() {
	 // Рисуем ToolTip если надо
	if (DragStatus) {
		toolTip.View();
	}
	window.event.returnValue = false;
}

function itemDragLeave() {
	 // Обнуляем все переменные (вышли из элемента)
	window.clearTimeout(iTID);
	window.status = '';
	DragStatus = '';
	toolTip.Hide();
	window.event.returnValue = false;
}

function itemDragEnter() {
	var oEvent = window.event;
	var oData = oEvent.dataTransfer;

	// tree auto expand
	oTrg = oEvent.srcElement;
	iTID = window.setTimeout('itemExpand()', 1500);

	var oTrgA = oTrg.parentElement;
	var iTrgId = oTrgA.getAttribute('aid');
	var iTrgPid = oTrgA.getAttribute('pid');
	var iSrcId = oDragSrc.getAttribute('aid');
	var iSrcPid = oDragSrc.getAttribute('pid');

	// Определяем действие
	if (iTrgPid == iSrcPid && iTrgId != iSrcId && !oEvent.shiftKey && !oEvent.ctrlKey) {
		oData.dropEffect = 'link';
		DragStatus = 'swap';
		Ttip = 'Swap Elements';
	}
	else if (oEvent.ctrlKey) {
		oData.dropEffect = 'move';
		DragStatus = 'move';
		Ttip = 'Move Here';
	}
	else if (oEvent.shiftKey) {
		oData.dropEffect = 'copy';
		DragStatus = 'copy';
		Ttip = 'Copy Here';
	}
	else {
		oData.dropEffect = 'none';
		DragStatus = '';
		Ttip = '';
	}
	// Рисуем подсказки (на стутус и в ToolTip)
	window.status = Ttip;
	if (DragStatus) {
		toolTip.View('&nbsp;&nbsp;&nbsp;' + Ttip);
	}
	oEvent.returnValue = false;
}

function itemDrop() {
	var oTrgA = window.event.srcElement.parentElement;
	var iTrgId = oTrgA.getAttribute('aid');
	var iSrcPid = oTrgA.getAttribute('pid');
	var iSrcId = oDragSrc.getAttribute('aid');

	// Выполняем Swap
	if (DragStatus == 'swap') {
		// removes childs, set loaded=0, reload
		oParentDiv = document.getElementById('div_' + iSrcPid);
		oParentLoad = document.getElementById('load_' + iSrcPid);
		if (oParentLoad) {
			while (oParentLoad.firstChild) oParentLoad.firstChild.removeNode(true);
			oParentLoad.innerHTML = document.getElementById('loading').innerHTML;
		}
		// actually send swap request to server
		document.getElementById('toc').src = 'page.php?page=tree&do=swap&src=' + iSrcId + '&trg=' + iTrgId + '&pid=' + iSrcPid;
	// Выполняем Copy и Move
	}
	else if (DragStatus == 'copy' || DragStatus == 'move') {
		document.getElementById('toc').src = 'page.php?page=tree&do=' + DragStatus + '&src_id=' + iSrcId + '&trg_id=' + iTrgId;
	}
	// Завершение
	window.status = '';
	toolTip.Hide();
	window.event.returnValue = false;
}

function itemActivate(item) {
	bNoDeAct = 1;
	itemsDeact();
	bNoDeAct = 0;

	oItem = item;

	itemId = item.getAttribute('aid');
	itemPid = item.getAttribute('pid');

	tabs = window.parent.document.getElementsByTagName('TR');
	for (i = 0; i < tabs.length; i++) {
	    if (tabs[i].className != 'actions') continue;
		for (j = 1; j < tabs[i].cells.length - 3; j += 2) {
			tabs[i].cells[j].style.display = isIE ? 'block' : 'table-cell';
			tabs[i].cells[j + 1].style.display = isIE ? 'block' : 'table-cell';
		}
	}

	//oItem.className = 'open';

	var oLink = document.getElementById('link_' + itemId);
	if (oLink) {
		oLink.className = 'selectedItem';
		// tricky, buggy
		window.setTimeout('var oLink = document.getElementById(\'link_\' + itemId);if(oLink)oLink.setAttribute(\'active\', 1);', 100);
	}
}

function itemDeactivate() {
	//itemsDeact();
}

function itemsDeact() {

	var oLink = document.getElementById('link_' + itemId);
	if (oLink) {
		oLink.className = 'deselectedItem';
		oLink.setAttribute('active', 0);
	}

	if (bNoDeAct) {
		return;
	}

	itemId = 0;
	itemPid = 0;

	tabs = window.parent.document.getElementsByName('actions');
	for (i = 0; i<tabs.length; i++) {
		for (j = 1; j < tabs[i].cells.length - 3; j += 2) {
			tabs[i].cells(j).style.display = 'none';
			tabs[i].cells(j + 1).style.display = 'none';
		}
	}
	//oItem.className = '';
	oItem = null;
}

function itemDblClick(item) {
	if (item) {
	    fireClick(item);
		//item.click();
	}
	editItem();
}

//
// Actions
//

function createItem(event) {
	if (event.ctrlKey) {
		window.showModalDialog('dialog.php?page=tree&do=editform&id='+itemId, '', 'dialogWidth:400px; dialogHeight:220px;');
	}
	else {
		window.showModalDialog('dialog.php?page=tree&do=editform&pid='+itemId, '', 'dialogWidth:400px; dialogHeight:220px;');
	}
}

function editItem() {
	if (itemId && itemId > 0) {
		window.open('ed.php?page=tree&id='+itemId, '_blank', 'resizable=1, status=1, width=800, height=600');
	}
}

function deleteItem() {
	window.showModalDialog('dialog.php?page=tree&do=delete&id='+itemId, '', 'dialogWidth:320px; dialogHeight:180px;');
}

function showRecycle() {
	window.showModalDialog('dialog.php?page=ced/recycle', '', 'dialogWidth:400px; dialogHeight:400px;');
}

/*function EditPriority(move) {
	if (oItem) {
		iPid = oItem.getAttribute('pid');
		document.getElementById('toc').src = 'page.php?page=tree&do=EditPriority&src='+itemId+'&move='+move+'&pid='+iPid;
	}
	else {
		alert('Select item first');
	}
}*/

function focusItem() {
	/*if (oItem) {
		oItem.setActive();
	}*/
	var oLink = document.getElementById('link_' + itemId);
	if (oLink && !oLink.getAttribute('expanded')) {
	    fireClick(oLink);
		//oLink.click();
	}
}

function _preview(id, dir) {
	tagA = document.getElementById('div_'+id).lastChild;
	var pid = tagA.getAttribute('pid');
	if (!pid || pid == tagA.getAttribute('aid')) return Array(dir, pid);
	else {
		dir.push(tagA.getAttribute('page'));
		_preview(pid, dir)
	}
	return Array(dir, pid);
}

function Preview(id, array_site) {
	dir = new Array('');
	id = id || itemId;
	ret = _preview(id, dir);
	dir = ret[0];
	root_id = ret[1];
	site = '';
	if (array_site != null && array_site[root_id] != null)
		site = array_site[root_id];
	dir.push('');
	if (site != '')
		window.open('http://' + site + dir.reverse().join('/'), 'Preview').focus();
	else
		window.open(dir.reverse().join('/'), 'Preview').focus();
}