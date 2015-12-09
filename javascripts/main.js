var agent = navigator.userAgent.toLowerCase();
var major = parseInt(navigator.appVersion);
var minor = parseFloat(navigator.appVersion);

var isNN = ((agent.indexOf('mozilla') != -1) && ((agent.indexOf('spoofer') == -1) && (agent.indexOf('compatible') == -1)));
var isNN4 = (isNN && (major == 4));
var isNN6 = (isNN && (major >= 5));

var isOPERA = agent.indexOf("opera")>-1 && window.opera;
var isIE4 = (agent.indexOf("msie") != -1 && !isOPERA);

var flash_version = 0; // Версия флеш-проигрывателя
var fz=0;
if (isIE4){
	ie = 1;
	for (var i=3; i<7; i++){
		try {
			if (eval("new ActiveXObject('ShockwaveFlash.ShockwaveFlash."+i+"')")) flash_version = i;
		}
		catch (e) {}
	}
}
if ((isOPERA || isNN || isNN4 || isNN6) && (navigator.plugins)){
	for (var i=0; i<navigator.plugins.length; i++){
		if (navigator.plugins[i].name.indexOf("Flash")> -1){
			fz = parseInt(navigator.plugins[i].description.charAt(16));
			if (fz > flash_version) flash_version=fz;
		}
	}
}

//-------------------------------------------------------------------//
function rusoft() {
  window.open('http://www.rusoft.ru');
}

function showMsg(text) {
document.piki = text;
}



function ShowHTTP(href, display, target, style, title) {
	if (display == '') display = href;
	if (target == '') target = '_blank';
	document.writeln('<a href="'+href+'" target="'+target+'" title="'+title+'" style="'+style+'">'+display+'</a>');
}

function ShowMail(user, domain1, domain2) {
	var email = user+'&#64;'+domain1+'&#46;'+domain2;
	if (ShowMail.arguments[3]) name = ShowMail.arguments[3];
	else name = email;
	document.writeln('<a href="mailto:'+email+'">'+name+'</a>');
}

function openImage(image) {
	if (image) window.open("/popup.php?img="+image, "popupimage", "scrollbars=no, resizable=1, width=100, height=100").focus();
	return false;
}

function openPhoto(id) {
	if (id) window.open("/popupf?id="+id, "popup", "scrollbars=1, resizable=1, width=700, height=450").focus();
}

function imgOpen(imgURL,imgWidth,imgHeight,Title) {
	var imgWndw=window.open('','_blank','width='+imgWidth+',height='+
	imgHeight+',toolbar=no,menubar=no,location=no,status=no,'+
	'resizable=yes,scrollbars=no');
	var imgTitle=(Title)?Title:imgURL+": "+imgWidth+'x'+imgHeight;
	with (imgWndw.document){
		open();
		write('<ht'+'ml><he'+'ad><ti'+'tle>'+imgTitle+'</ti'+'tle>'+
		'</he'+'ad><bo'+'dy leftmargin="0" '+
		' topmargin="0" '+
		'rightmargin="0" bottommargin="0" marginwidth="0" '+
		'marginheight="0"><img src="'+imgURL+'" width="'+imgWidth+
		'" height="'+imgHeight+'" border="0" alt="'+imgTitle+
		'"></bo'+'dy></ht'+'ml>');
		close();
	}
	return false
}

function OpenPopup(src) {
	if (arguments[1]) popup_name = arguments[1];
	else popup_name = 'popup';
	if (arguments[2]) popup_width = arguments[2];
	else popup_width = 500;
	if (arguments[3]) popup_height = arguments[3];
	else popup_height = 400;
	window.open(src, popup_name, 'toolbar=no,location=no,status=no,menubar=no,resizable=yes,directories=no,scrollbars=yes,width='+popup_width+',height='+popup_height).focus();
	return false
}

function SetCookie(sName, sValue){
	document.cookie = sName + "=" + escape(sValue) + "; expires=Fri, 31 Dec 2070 23:59:59 GMT; path=/;";
}

function changeImage(id, img) {
	document.getElementById(id).src = img;
}

function insertFlash(v, fl, gif, lnk, w, h, id){
	if (flash_version >= v) {
		document.write('<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" width="' + w + '" height="' + h + '" id="flash' +  id + '" align="center"><param name="allowScriptAccess" value="sameDomain" /><param name="movie" value="' + fl + '"><param name="quality" value="high" /><param name="wmode" value="transparent" /><param name="bgcolor" value="#ffffff" /><embed src="' + fl + '" wmode="transparent" quality="high" width="' + w + '" height="' + h + '"></embed></object>');
	} else if (gif != '') {
		document.write("<a href='"+lnk+"'><img src='"+gif+"' width='" + w + "' height='" + h + "' border=0></a>");
	}
}

// --------------------------------------------------
// ---- Отображение галереи на странице объекта -----
// --------------------------------------------------
var i=0;
var timer;
var old_page = 0;
// --- Клик на кнопку Вперед/Назад
function showGallery(count, mode, rotate){
	i=i+mode;
	if (rotate) {
		if (i>=count) i=0;
		if (i<0) i=count-1;
	} else {
		if (i>=count) i=count-1;
		if (i<0) i=0;
	}
	if (i>=0 && (i-1)<count) {
		if (document.getElementById('object_image') && par[i]) {
			document.getElementById('object_image').innerHTML = par[i];
		}
	}
	if (i==0) document.getElementById('link_prev').innerHTML = '<span class="back"><span class="arrows">&lt;&lt;</span> назад</span>';
	else document.getElementById('link_prev').innerHTML = '<a href="javascript:" onclick="showGallery(' + count + ',-1); return false;" class="back"><span class="arrows">&lt;&lt;</span> назад</a>';

	if ((i+1)>=count) document.getElementById('link_next').innerHTML = '<span class="next">вперед <span class="arrows">&gt;&gt;</span></span>';
	else document.getElementById('link_next').innerHTML = '<a href="javascript:" onclick="showGallery(' + count + ',1); return false;" class="next">вперед <span class="arrows">&gt;&gt;</span></a>';

	document.getElementById("page"+i).innerHTML = '<span>' + (i+1) + '</span>';
	if (old_page != "-1") document.getElementById("page"+old_page).innerHTML = '<a href="javascript:" onclick="SelectImage(' + old_page + ', ' + count + '); return false;">' + (old_page+1) + '</a>';
	old_page = i;

	return false;
}
// --- Клие на превью
function SelectImage(num, count){
	if (this.timer) stopGallery();
	showGallery(count, num-this.i, false);
}
// --- Клик на кнопку Play
function playGallery(count, timeout){
	// Надо вызывать функцию showGallery с какой-то задержкой
	this.timer = window.setInterval("showGallery("+count+", 1, true)", timeout);
	document.getElementById('play').src = '/images/controls_stop_active.gif';
	document.getElementById('play_link').onclick = function(){stopGallery(count); return false;};
}
// --- Клик на кнопку Stop
function stopGallery(count){
	window.clearInterval(this.timer);
	document.getElementById('play').src = '/images/controls_play_active.gif';
	document.getElementById('play_link').onclick = function(){playGallery(count); return false;};
}

function showGalleryNewBuild(count, mode, rotate){
	i=i+mode;
	if (rotate) {
		if (i>=count) i=0;
		if (i<0) i=count-1;
	} else {
		if (i>=count) i=count-1;
		if (i<0) i=0;
	}
	offset = 3; // Так как у нас не одна фото, а три
	if (i>=0 && (i-1)<count) {
		for (j=0; j<offset; j++) {
			if (document.getElementById('object_image'+j) && par[i*offset+j]) {
				document.getElementById('object_image'+j).innerHTML = par[i*offset+j];
			} else if (document.getElementById('object_image'+j)) {
				document.getElementById('object_image'+j).innerHTML = '';
			}
		}
	}
	if (i==0) document.getElementById('link_prev').innerHTML = '<span class="back"><span class="arrows">&lt;&lt;</span> назад</span>';
	else document.getElementById('link_prev').innerHTML = '<a href="javascript:" onclick="showGalleryNewBuild(' + count + ',-1); return false;" class="back"><span class="arrows">&lt;&lt;</span> назад</a>';

	if ((i+1)>=count) document.getElementById('link_next').innerHTML = '<span class="next">вперед <span class="arrows">&gt;&gt;</span></span>';
	else document.getElementById('link_next').innerHTML = '<a href="javascript:" onclick="showGalleryNewBuild(' + count + ',1); return false;" class="next">вперед <span class="arrows">&gt;&gt;</span></a>';

	document.getElementById("page"+i).innerHTML = '<span>' + (i+1) + '</span>';
	if (old_page != "-1") document.getElementById("page"+old_page).innerHTML = '<a href="javascript:" onclick="SelectImageNewBuild(' + old_page + ', ' + count + '); return false;">' + (old_page+1) + '</a>';
	old_page = i;

	return false;
}
function SelectImageNewBuild(num, count){
	if (this.timer) stopGallery();
	showGalleryNewBuild(count, num-this.i, false);
}

// Установка прозрачности
function setOpacity(nOpacity, elem) {
	if (isIE4) {
		nOpacity *= 100;
	    var oAlpha = elem.filters['DXImageTransform.Microsoft.alpha'] || elem.filters.alpha;
		if (oAlpha) oAlpha.opacity = nOpacity;
		else elem.style.filter += "progid:DXImageTransform.Microsoft.Alpha(opacity="+nOpacity+")";
	} else {
		try {
			elem.style.opacity = nOpacity;
			elem.style.MozOpacity = nOpacity;
			elem.style.KhtmlOpacity = nOpacity;
		} catch (e) {}
	}
}

// Добавить или удалить один район
function selectDistrict(district_id, district_name, ao_id, ao_name, flag) {
	var table = document.getElementById('selectedDistrictsTable');
	if (flag == true) {
		// Добавить
		var li = document.createElement('li');
		li.id = 'li_' + district_id;
		li.innerHTML = district_name + '(<a href="#" onclick="selectDistrict(\''+ district_id + '\', \'' + district_name + '\', \''+ ao_id + '\', \'' + ao_name + '\', false); return false">удалить</a>)';

		var ao = document.getElementById('ul_'+ao_id);

		if (!ao) {
			// Еще нет такого АО
			var ul = document.createElement('ul');
			ul.id = 'ul_'+ao_id;
			ul.appendChild(li);
			var p = document.createElement('p');
			p.className = 'selectedDstrTitle';
			var td = document.createElement('td');
			td.id = 'td_' + ao_id;
			td.appendChild(p);
			td.appendChild(ul);
			if (!table.rows || table.rows.length < 1) {
				// В таблице еще нет строк
				var tr = table.insertRow(-1);
				tr.id = 'tr_' + table.rows.length;
				p.innerHTML = '<strong>' + ao_name + '</strong>:  (<a href="#" onclick="deleteAO(\''+ ao_id + '\', \'' + tr.id + '\'); return false">удалить</a>)';
				tr.appendChild(td);
			} else {
				// Считаем, сколько колонок в последней строке, чтобы знать, добавлять еще колонку или сразу новую строку
				var last_tr = table.rows[table.rows.length-1];
				var count_td = last_tr.cells.length;
				if (count_td < 3) {
					p.innerHTML = '<strong>' + ao_name + '</strong>:  (<a href="#" onclick="deleteAO(\''+ ao_id + '\', \'' + last_tr.id + '\'); return false">удалить</a>)';
					last_tr.appendChild(td);
				} else {
					var tr = table.insertRow(-1);
					tr.id = 'tr_' + table.rows.length;
					p.innerHTML = '<strong>' + ao_name + '</strong>:  (<a href="#" onclick="deleteAO(\''+ ao_id + '\', \'' + tr.id + '\'); return false">удалить</a>)';
					tr.appendChild(td);
				}
			}
		} else {
			// АО есть, просто добавляем в него район
			var ul = document.getElementById('ul_'+ao_id);
			ul.appendChild(li);
		}
	} else {
		// Удалить
		var ul = document.getElementById('ul_'+ao_id);
		var li = document.getElementById('li_' + district_id);
		ul.removeChild(li);
		uncheckDistrict(district_id);
		if (!ul.childNodes.length) {
			var tr_id = ul.parentNode.parentNode.id;
			deleteAO(ao_id, tr_id);
		}
	}

	if (table.rows.length > 0) {
		document.getElementById('mainTitle').innerHTML = 'Вы выбрали округа Москвы:';
	} else {
		document.getElementById('mainTitle').innerHTML = 'Вы выбрали округа Москвы: не выбрано';
	}
}

// Удалить весь АО
function deleteAO(ao_id, tr_id) {
	var table = document.getElementById('selectedDistrictsTable');
	if (tr_id == '') {
		for (i=0; i<table.rows.length; i++) {
			for (j=0; j<table.rows[i].cells.length; j++) {
				if (table.rows[i].cells[j].id == 'td_' + ao_id) {
					tr_id = 'tr_' + (i + 1);
					break;
				}
			}
		}
	}
	var tr = document.getElementById(tr_id);
	if (!tr) return;
	var td = document.getElementById('td_' + ao_id);
	try {
		tr.removeChild(td);
		var div = document.getElementById('d'+ao_id);
		if (isIE4) {
			var _table = div.childNodes[1].childNodes[4];
		} else {
			var _table = div.childNodes[3].childNodes[5];
		}
		for (var i=0; i<_table.rows.length; i++) {
			for (j=0; j<_table.rows[i].cells.length; j++) {
				var child = _table.rows[i].cells[j].childNodes[0];
				if (child && child.nodeName != null && child.nodeName == 'INPUT') {
					child.checked = false;
				}
			}
		}
		if (tr.cells.length < 1) {
			for (var i=0; i < table.rows.length; i++) {
				if (table.rows[i].id == tr_id) {
					table.deleteRow(i);
					break;
				}
			}
		}
	} catch (e) {}
	if (table.rows.length < 1) {
		document.getElementById('mainTitle').innerHTML = 'Вы выбрали округа Москвы: не выбрано';
	}
}

function selectDistrict2(id, obj, replace) {
    var form = document.getElementById('search_obj');
    var nameObj = document.getElementById('name'+id);

    if (obj.className == '') {

		if (!document.getElementById("raion_id_" + id)) {
    		var hidden = document.createElement("INPUT");
    		hidden.type = "hidden";
    		hidden.name = "fld[raion][]";
    		hidden.id = "raion_id_" + id;
    		hidden.value = id;
    		form.appendChild(hidden);
		}

		raionArr[id]=[document.getElementById("name"+id).childNodes[1].nodeValue];

        if (replace == undefined) {
    		$('#district_item'+id).addClass('selected');
    		$('#addD').click();
        }

		nameObj.style.display = 'block';
	    if (nameObj.className=='nameBox' || nameObj.className=='nameBox left'){
			nameObj.className = nameObj.className + ' select';
		}
		obj.className = 'selectedDistrict';
    }
    else {
        try {
            nameObj.style.display = 'block';
			if (id=='233' || id=='180' || id=='234'){
				nameObj.className = 'nameBox left';
			} else {
				nameObj.className = 'nameBox';
			}

            if (replace == undefined) {
                $('#district_item'+id).addClass('selected');
                $('#removeD').click();
            }
    		obj.className = '';
    		delete raionArr[id];
    		form.removeChild(document.getElementById("raion_id_"+id));
        } catch (e) {}
    }
}
function onmouseoverDistrict2(id, obj) {
    if (obj.className != 'selectedDistrict') {
		document.getElementById('name'+id).style.display = 'block';
    }
//    var nameObj = document.getElementById('name'+id);
//    if (nameObj.className=='nameBox' || nameObj.className=='nameBox left'){
//		nameObj.className = nameObj.className + ' select';
//	}
}
function onmouseoutDistrict2(id, obj) {
	if (obj.className != 'selectedDistrict') {
		document.getElementById('name'+id).style.display = 'none';
	}
//	var nameObj = document.getElementById('name'+id);
//	if (id=='233' || id=='180' || id=='234'){
//		nameObj.className = 'nameBox left';
//	} else {
//		nameObj.className = 'nameBox';
//	}
}

// Снять галочку с района
function uncheckDistrict(district_id){
	document.getElementById('dstr' + district_id).checked = false;
}

function selectCity(id, obj, replace) {
    var form = document.getElementById('search_obj');

    if (obj.className == 'option selected') {
		if (!document.getElementById("city_mo_id_" + id)) {
			var hidden = document.createElement("INPUT");
			hidden.type = "hidden";
			hidden.name = "fld[city_mo][]";
			hidden.id = "city_mo_id_" + id;
			hidden.value = id;
			form.appendChild(hidden);

			cityArr[id]=[document.getElementById("city_item"+id).childNodes[0].nodeValue];
	        if (replace == undefined) {
				$('#city_item'+id).addClass('selected');
				$('#addC').click();
	    	}
		}
    }
    else {
	    try {
            if (replace == undefined) {
                $('#city_item'+id).addClass('selected');
                $('#removeC').click();
            }
    		obj.className = 'option';

			delete cityArr[id];
			form.removeChild(document.getElementById("city_mo_id_"+id));
    	} catch (e) {}
	}

}

// min width ie6
function minWidth() {
	var d = document;
	var winIE = (navigator.userAgent.indexOf('Opera')==-1 && (d.getElementById && d.documentElement.behaviorUrns)) ? true : false;

	function bodySize() {
		if(winIE && d.documentElement.clientWidth) {
			sObj = d.getElementsByTagName('body')[0].style;
			sObj.width = (d.documentElement.clientWidth < 1000) ? '1000px' : '100%';
		}
	}

	function init() {
		if(winIE) { bodySize(); }
	}
	onload = init;
	if(winIE) { onresize = bodySize; }
}

// show hide map
function showHideMap() {
	$('div.showHideMap a').toggle(
		function ()	{
			$(this).addClass('open').text('свернуть карту');
			$(this).parent('.showHideMap').next('.mapBox').addClass('open');
		},
		function ()	{
			$(this).removeClass('open').text('показать карту');
			$(this).parent('.showHideMap').next('.mapBox').removeClass('open');
		}
	);
}

// search stars hover
function starDesc() {
	$('div.stars img').hover(
		function () {
			var imgClass = $(this).attr('class');
			if (imgClass == 'descOne') {
				$('div.descOne').css('display', 'block');
			}
			if (imgClass == 'descTwo') {
				$('div.descTwo').css('display', 'block');
			}
			if (imgClass == 'descThree') {
				$('div.descThree').css('display', 'block');
			}
			if (imgClass == 'descFour') {
				$('div.descFour').css('display', 'block');
			}
			if (imgClass == 'descFive') {
				$('div.descFive').css('display', 'block');
			}
		},
		function () {
			$('div.starDescription div').css('display', 'none');
		}
	);
}

// show login
function showLogin() {
	$('.login a.enter').click(
		function ()	{
			$(this).parent('.login').children('.shadowBox').css('display', 'block');
		}
	);
}

// hide shadow
function hideShadow() {
	$('div.shadowBox div.close').click(
		function ()	{
			$(this).parent('.shadowBox').css('display', 'none');
		}
	);

	$('div.shadowBox a.cancel').click(
		function ()	{
			$(this).parent().parent('.shadowBox').css('display', 'none');
			return false;
		}
	);
}

// параметры для выравнивания всплывающих блоков
function popupValue(obj) {
	if (obj == 'width') {
		return window.innerWidth || (document.documentElement && document.documentElement.clientWidth) || document.body.clientWidth;
	}
	if (obj == 'height') {
		return window.innerHeight || (document.documentElement && document.documentElement.clientHeight) || document.body.clientHeight;
	}
	if (obj == 'top') {
		return window.pageYOffset || (document.documentElement && document.documentElement.scrollTop) || document.body.scrollTop;
	}
}

function popupInfo() {
	var top = popupValue('top');
	var width = popupValue('width');

	$('div.dropBoxInfo').css({ top: top+240, left: width/2-4 });
}

function popupMap() {
	var top = popupValue('top');
	var width = popupValue('width');

	$('div.shadowBox.map').css({ top: top+110, left: width/2-350 });
}

function popupMapMetro() {
	var width = popupValue('width');

	$('div.shadowBox.map.metro').css({ top: 110, left: width/2-350 });
}

function popupMapRegion() {
	var width = popupValue('width');

	$('div.shadowBox.mapRegion').css({ top: 110, left: width/2-382 });
}

function popupHomeType() {
	var top = popupValue('top');
	var width = popupValue('width');

	$('div.shadowBox.homeType').css({ top: top+240, left: width/2-150 });
}

// district moscow
var selected_stations={};
var selected_districts={};
function districtMoscow(obj) {
	switch(obj) {
		case "district_szao" : okrug=1; break;
		case "district_sao"  : okrug=2; break;
		case "district_svao" : okrug=3; break;
		case "district_zao"  : okrug=4; break;
		case "district_cao"  : okrug=5; break;
		case "district_vao"  : okrug=6; break;
		case "district_uzao" : okrug=7; break;
		case "district_uao"  : okrug=8; break;
		case "district_uvao" : okrug=9; break;
		default: return;
	}
	for(k in okrug_binds[okrug]) {
		if(okrug_binds[okrug][k] in selected_stations) is_selected=true; else is_selected=false;
		if(!is_selected) {
			selected_stations[okrug_binds[okrug][k]]=true;
			selected_districts[okrug]=true;
			checkMetro(okrug_binds[okrug][k]);
		}
		else {
			var using=false;
			for(i in selected_districts) {
				if(i==okrug) continue;
				for(j=0;j<okrug_binds[i].length;j++) {
					if(okrug_binds[okrug][k]==okrug_binds[i][j]) {
						using=true;
						break;
					}
				}
				if(using) break;
			}
			if(!using) {
				delete selected_stations[okrug_binds[okrug][k]];
				delete selected_districts[okrug];
				checkMetro(okrug_binds[okrug][k]);
			}
		}
	}

	var pic = obj;
	var disp = $('#'+obj).css('display');

	if (disp == 'none') {
		$('#'+obj).css('display', 'block');
	}
	if (disp == 'block') {
		$('#'+obj).css('display', 'none');
	}
}

function addFile(id, name){
	var newfile = "<input type='file' name='fld["+name+"][]' /> &nbsp;&nbsp;"+
	"&nbsp;<a href='javascript:void(0);' onclick='DeleteRow(this)'>x</a>";

	var myTable = document.getElementById(id);
	var myRow = myTable.insertRow(-1);
	var cell0 = myRow.insertCell(0).innerHTML = newfile;
}

function DeleteRow(item) {
	if (confirm('Вы действительно хотите удалить эту строку ?')) {
		var ix= item.parentNode.parentNode.rowIndex;
		item.parentNode.parentNode.parentNode.deleteRow(ix);
	}
}

function checkLogin(){
	if (document.getElementById('loginform_login').value == "") {
		alert('Вы должны указать свой логин.');
		document.getElementById('loginform_login').focus();
		return false;
	}
	window.location = "/cabinet/forget_password?login="+document.getElementById('loginform_login').value;
	return false;
}

function checkLoginMain(){
	if (document.getElementById('loginformmain_login').value == "" || document.getElementById('loginformmain_login').value == "e-mail") {
		alert('Вы должны указать свой логин.');
		document.getElementById('loginformmain_login').focus();
		return false;
	}
	window.location = "/cabinet/forget_password?login="+document.getElementById('loginformmain_login').value;
	return false;
}

function resetChecked() {
	var obj;
	if(document.getElementById('prolongation')) {
		var form_name = document.getElementById('prolongation');
		obj = form_name.getElementsByTagName("input");
		var el = document.getElementById('obj_check');
		if(el.checked) {
			for(var i = 0; i < obj.length; i++) {
				obj[i].checked = "checked";
			}
		} else {
			for(var i = 0; i < obj.length; i++) {
				obj[i].checked = null;
			}
		}
	}
}
