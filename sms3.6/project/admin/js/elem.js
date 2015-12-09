var agent = navigator.userAgent.toLowerCase();
var major = parseInt(navigator.appVersion);
var minor = parseFloat(navigator.appVersion);

var isNN = ((agent.indexOf('mozilla') != -1) && ((agent.indexOf('spoofer') == -1) && (agent.indexOf('compatible') == -1)));
var isOPERA = agent.indexOf("opera")>-1 && window.opera;
var isIE = (agent.indexOf("msie") != -1 && !isOPERA);

//var toc_line = new Image; toc_line.src='images/tree/toc_line.gif';
//var toc_closed_whole = new Image; toc_closed_whole.src='images/tree/toc_closed_whole.gif';
var oItem, bNoDeAct, elemName, elemId;

// вызов был при клике на checkbox для скрытия или отображения action кнопок
function elemActions(name, activate) {
    tabs = document.getElementsByTagName('TR');
	//eval('var actions = window.frames.cnt.A' + name + activate + ';');
	eval('var actions = A' + name + activate + ';');
	if (!actions) {
		//alert('Error: var A'+name+activate+' is undefined')
		return;
	}
	for (i = 0; i < tabs.length; i++) {
	    if (tabs[i].className != 'actions') continue;
		var c = 0;
		for (j = 1; j < tabs[i].cells.length - 1; j += 2) {
			if (actions[c] == null) {
				continue;
			}
			//tabs[i].cells(j).filters[0].apply();
			if (actions[c]) {
				tabs[i].cells[j].style.display = isIE ? 'block' : 'table-cell';
				tabs[i].cells[j + 1].style.display = isIE ? 'block' : 'table-cell';
			}
			else {
				tabs[i].cells[j].style.display = 'none';
				tabs[i].cells[j + 1].style.display = 'none';
			}
			//tabs[i].cells(j).filters[0].play();
			c++;
		}
	}
}

// не найдена точка вызова
function elemSwap(name, pid, move, frame) {
	if (!frame) {
		frame = 'cnt';
	}
	document.all[frame].src = 'page.php?page=' + name + '&do=EditPriority&pid=' + pid + '&move=' + move + '&id[' + cnt.ID + ']=' + cnt.ID;
}

// не найдена точка вызова
function elemDelete(name, pid) {
	document.all.cnt.src = 'page.php?page=' + name + '&do=Delete&pid=' + pid + '&id[' + cnt.ID + ']=' + cnt.ID;
}

function expandNode(Elem, Id) {
	var tagDiv = document.getElementById('div_' + Elem + '_' + Id);
	var tagLoad = document.getElementById('load_' + Elem + '_' + Id);
	var tagA = document.getElementById('link_' + Elem + '_' + Id);

	tagA.setAttribute('expanded', 0);

	if (tagDiv.getAttribute('loaded') == 1) {
		var d, s;
		var img = document.getElementById('img_' + Elem + '_' + Id);
		if (tagLoad.style.display == '') {
			d = 'none';
			s = img.src.replace(/opened/g, 'closed');
		}
		else {
			d	= '';
			s	= img.src.replace(/closed/g, 'opened');
			tagA.setAttribute('expanded', 1);
		}
		tagLoad.style.display = d;
		img.src = s;

		return false;
	}
	if (!tagLoad) {
		return false;
	}

	tagLoad.className = 'vis';
	tagA.setAttribute('expanded', 1);

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

function elemExpand() {
	if (oItem.getAttribute('expanded') != 0) {
		return;
	}
	oItem.click();
}

function elemActivate(item) {
	elemId = item.getAttribute('elemId');
	elemName = item.getAttribute('elemName');
	oItem = item;
	bNoDeAct = 0;

	divs = document.getElementsByTagName('A');
	for (i = 0; i < divs.length; i++) {
		if (divs[i].className == 'open') {
			divs[i].className = '';
		}
	}
	oItem.className = 'open';

	// actions
}

function elemDeactivate() {
	return;

	if (bNoDeAct) {
		return;
	}
	elemId = 0;
	elemName = '';
	oItem.className = '';
	oItem = null;

	// actions
}

// Возвращает массив id выбранных в списке
function getSelectedItems(this_formname) {
    var ret = new Array();

    if (document.forms.length > 0) {
        if (this_formname == null) {
            var form = document.forms['editform'];
        } else {
            var form = document.forms[this_formname];
        }
        var j=0;
        for (var i=0; i < form.elements.length; i++) {
            var elem = form.elements[i];
            if (elem.type == 'checkbox' && elem.checked) {
                if (elem.value != 'on') {
                    ret[j] = elem.value;
                    j++;
                }
            }
        }
    }
    return ret;
}

function createItem() {
    location.href = '/admin/editor.php?page=' + thisname + '&id=0';
}

function editItem(id) {
	if (id == null) {
        id = getSelectedItems();
	}
	location.href = '/admin/editor.php?page=' + thisname + '&id=' + id;
}

function deleteItems(formname, hide) {
    formname = formname || 'editform';

    hide = (hide != null) ? hide : 1;
    if (hide < 1) method = hide < 0 ? 'EditVisible_1' : 'EditVisible0';
    else method = 'Delete';

    var wnd = window.open('dialog.php?page='+thisname+'&do=showconfirm&formname='+formname+'&method='+method, 'deleteItems', "width=420,height=230,resizable,scrollbars=yes,status=1")
}

function deleteItemsConfirm(formname, method) {
    document.forms[formname].elements['do'].value = method;
    document.forms[formname].submit();
}


//Копирование объектов
copyItems = function () {
    id = getSelectedItems();
    if (id != '') {
        if (confirm("Копировать выбранные объекты?")) {
            $('#cnt').html("<iframe src='/admin/editor.php?page="+thisname+"&do=copyobjects&ids="+id+"' width=0 height=0 border=0 style='visibility:hidden'></iframe>");
        }
    } else {
        alert ("Вы не выбрали объект для копирования.");
    }
}

// вызов actions.tmpl
callFunct = function(node, link, arguments, multiaction) {
    var cS = link.split('.');
    var myCall = window;

    for(var i=0; i<cS.length; i++) {
        if (typeof myCall[cS[i]] != 'undefined') {
            myCall = myCall[cS[i]];
        }
    }

    if (myCall && typeof myCall == 'function') {
        var id = getSelectedItems();
        if (multiaction == false) {
            if (id.length > 1) {
                alert ('Для этого действия необходимо выбрать один элемент.');
            } else {
                return myCall.apply(node, arguments);
            }
        } else {
            return myCall.apply(node, arguments);
        }
    } else {
        alert ("Не найдена функция для вызова.");
    }
}

// вызов workingcopy.php
function elemActionsHide() {
	tabs = document.getElementsByName('actions');
	for (i = 0; i < tabs.length; i++) {
		for (j = 1; j < tabs[i].cells.length - 1; j += 2) {
			tabs[i].cells[j].style.display = 'none';
			tabs[i].cells[j + 1].style.display = 'none';
		}
	}
}