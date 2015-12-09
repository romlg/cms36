// Выбрали ветку метро
function select_metro(elem){
	var arr = new Array();

	var id = elem.id;	
	id = id.substr(1);
	
	var img = document.getElementById('img_line'+id);
	img.style.display = img.style.display == 'block' ? 'none' : 'block';
	
	if (metro_center[id] != undefined) arr = arr.concat(metro_center[id]);
	if (metro_region_bottom[id] != undefined) arr = arr.concat(metro_region_bottom[id]);
	if (metro_region_top[id] != undefined) arr = arr.concat(metro_region_top[id]);

	if (arr == undefined && elem.checked) {
		alert('Для этой ветки не заданы станции метро!');
		return false;
	} else if (arr == undefined){
		return false;
	}

	select_line(arr, img.style.display);
}

// Выбор ветки вверх от кольца
function select_metro_up(elem) {
	var arr = new Array();
	var id = elem.id;
	id = id.substr(1);

	var img = document.getElementById('img_line_up'+id);
	img.style.display = img.style.display == 'block' ? 'none' : 'block';

	arr = metro_region_top[id];
	select_line(arr, img.style.display);
}

// Выбор ветки вниз от кольца
function select_metro_down(elem) {
	var arr = new Array();
	var id = elem.id;	
	id = id.substr(1);

	var img = document.getElementById('img_line_down'+id);
	img.style.display = img.style.display == 'block' ? 'none' : 'block';

	arr = metro_region_bottom[id];
	select_line(arr, img.style.display);
}

function select_line(arr, display) {
	if (arr == undefined) return;
	for (var i in arr)
	{
		checkMetro(arr[i], display);
	}
}

// Отображает или скрывает значок на странции метро
function checkMetro(m_id, display, replace) {
	var elem = document.getElementById("pp"+m_id);
	if (!elem) return false;
	if (display == undefined) display = elem.style.display == 'block' ? 'none' : 'block';
	elem.style.display = display;

	var form = document.getElementById('search_obj');

	if (elem.style.display == "block") {
	    
	    if (!document.getElementById("metro_id_" + m_id)) {
    		var hidden = document.createElement("INPUT");
    		hidden.type = "hidden";
    		hidden.name = "fld[metro][]";
    		hidden.id = "metro_id_" + m_id;
    		hidden.value = m_id;
    		form.appendChild(hidden);
	    }
	    
		metroArr[m_id]=[document.getElementById("pp"+m_id).title];
		
		if (replace == undefined) {
    		$('#metro_item'+m_id).addClass('selected');
    		$('#addM').click();
		}
		
	} else {
		try {
			form.removeChild(document.getElementById("metro_id_"+m_id));
			delete metroArr[m_id];
			
			if (replace == undefined) {
    			$('#metro_item'+m_id).addClass('selected');
    			$('#removeM').click();
			}
			
		} catch (e) {}
	}	
}

function setMO(obj) {
	if (obj.className == "") {
		obj.className = "state_on";
		document.getElementById("moscow").value = "0";
	} else {
		obj.className = "";
		document.getElementById("moscow").value = "1";
	}
}

// Выбор направления
function selectDirection(num) {
	var _line = document.getElementById('directLine'+num);
	var _link = document.getElementById('directLink'+num);
	var form = document.getElementById('search_obj');
	if (_line.className == '') {
		// Включаем линию
		_line.className = 'active';
		_link.className = 'active';
		var hidden = document.createElement("INPUT");
		hidden.type = "hidden";
		hidden.name = "fld[direction][]";
		hidden.id = "direction_id_" + num;
		hidden.value = num;
		form.appendChild(hidden);
	}
	else {
		// Выключаем линию
		_line.className = '';
		_link.className = '';
		try {
			form.removeChild(document.getElementById("direction_id_"+num));
		} catch (e) {}
	}
}

function deselectDirection(num) {
	var _line = document.getElementById('directLine'+num);
	var _link = document.getElementById('directLink'+num);
	var form = document.getElementById('search_obj');
		_line.className = '';
		_link.className = '';
		try {
			form.removeChild(document.getElementById("direction_id_"+num));
		} catch (e) {}
}