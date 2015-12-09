var metroArr = new Object();
var raionArr = new Object();
var houseArr = new Array();
var starArr = new Array();
var roomArr = new Array();
var rateArr = new Array();
var cityArr = new Object();
var toggleStar = new Array(); toggleStar[1] = toggleStar[2] = toggleStar[3] = toggleStar[4] = toggleStar[5] = 0;
var showOrderBox = false;
var firstShow = false;
var favorite_items = false;

if (typeof window.q2000_rnt == 'undefined') {
    var q2000_rnt = {};
}
q2000_rnt.search = {};

q2000_rnt.search.init = function(load) {
    var self = this;

    // multiselect
    $('#selectBoxAddM .option, #selectBoxDelM .option, #selectBoxAddD .option, #selectBoxDelD .option, #selectBoxAddC .option, #selectBoxDelC .option').click(function() {
        $(this).toggleClass('selected');
    });

	$('#selectBoxAddM .selectAll, #selectBoxDelM .selectAll, #selectBoxAddD .selectAll, #selectBoxDelD .selectAll, #selectBoxDelC .selectAll').click(function() {
        $(this).toggleClass('del');
        var nameClass = $(this).attr('class');
		if (nameClass == 'selectAll del') {
            $(this).html('снять выделение');
			$(this).parent().find('.option').addClass('selected');
        }
        else {
            $(this).html('выделить все');
            $(this).parent().find('.option').removeClass('selected');
        }
    });

    $('#selectBoxAddC .selectAll').click(function() {
        $(this).toggleClass('del');
        var nameClass = $(this).attr('class');
		if (nameClass == 'selectAll del') {
            $(this).html('снять выделение');
			$(this).parent().find('.option').each(function() {
	    		var id = $(this).attr('id');
				var div_opt = document.getElementById(id);
				if (div_opt.style.display!='none'){
					div_opt.className = div_opt.className + ' selected';
				}
    		});
        }
        else {
            $(this).html('выделить все');
            $(this).parent().find('.option').removeClass('selected');
        }
    });

    $('#addM, #addD, #addC').click(function() {
    	//метро
        $('#box1M .selected').each(function(el) {
            $(this).appendTo('#box2M').removeClass('selected');
            var id = $(this).attr('id');
            id = id.substr(10);
            checkMetro(id, 'block', false);
        });
        //районы
        $('#box1D .selected').each(function(el) {
            $(this).appendTo('#box2D').removeClass('selected');
            var id = $(this).attr('id');
            id = id.substr(13);
            selectDistrict2(id, document.getElementById('district_area'+id), false);

            var pid = 'city_div'+id;
			$("#box1C div[pid='"+pid+"']").show();
		});
        //города
        $('#box1C .selected').each(function(el) {
			var id = $(this).attr('id');
			id = id.substr(9);
			selectCity(id, document.getElementById('city_item'+id), false);
			$(this).appendTo('#box2C').removeClass('selected');
		});
		$(this).parent().find('.selectAll').removeClass('del').html('выделить все');
    });

    $('#removeM, #removeD, #removeC').click(function() {
    	//метро
		$('#box2M .selected').each(function(el) {
			$(this).appendTo('#box1M').removeClass('selected');
			var id = $(this).attr('id');
			id = id.substr(10);
			checkMetro(id, 'none', false);
		});
		//районы
		$('#box2D .selected').each(function(el) {
			$(this).appendTo('#box1D').removeClass('selected');
			var id = $(this).attr('id');
			id = id.substr(13);
			selectDistrict2(id, document.getElementById('district_area'+id), false);

            var pid = 'city_div'+id;
            $("#box1C div[pid='"+pid+"']").hide();
		});
		//города
		$('#box2C .selected').each(function(el) {
			$(this).appendTo('#box1C').removeClass('selected');
			var id = $(this).attr('id');
			id = id.substr(9);
			selectCity(id, document.getElementById('city_item'+id), false);
		});
		$(this).parent().find('.selectAll').removeClass('del').html('выделить все');
    });

    $('#search_obj input[id!="addres"]').change(function() {
        self.load();
    });

	$('#search_obj select[id=city]').change(function() {
		$('#metro_block').toggle();
		$('#district_block').toggle();
	});

    $('#addMetro').click(function() {
        self.load();
    });

    $('#limit_obj').change(function() {
        self.load();
    });

    $('.goSearch').click(function() {
        self.load();
    });

    // Клик по звезде
    $('.changeStar').click(function() {
        var id = $(this).attr('id');
        id = id.substr(8,1);
        if (toggleStar[id] == 0) {
            self.changeStar1(id);
            self.load();
        } else {
            self.changeStar2(id);
            self.load();
        }
    });
    $('.changeStar2').click(function() {
        var id = $(this).attr('id');
        id = id.substr(8,1);
        if (toggleStar[id] == 0) {
            self.changeStar1(id);
        } else {
            self.changeStar2(id);
        }
    });

    $('#search_form_view').toggle(
        function(){ // Краткий вид поиска
            $('.advanced_search_field').hide();
            this.innerHTML = 'Расширенный поиск';
            document.getElementById('advanced_search').value = '0';
			self.load();
        },
        function(){ // Расширенный вид поиска
            $('.advanced_search_field').show();
            this.innerHTML = 'Краткий вид поиска';
            document.getElementById('advanced_search').value = '1';
			self.load();
        }
    );
	//Сварачиваем карту
	 $('#search_form_view1').toggle(
	  function(){ // Расширенный вид поиска
            $('.advanced_search_field1').show().css('height', '294px');
			$('.advanced_search_field1 .mapBox').css('display', 'block');
            $(this).addClass('open').text('свернуть карту');
            $('#advanced_search1').value = '1';
        },
        function(){ // Краткий вид поиска
            $('.advanced_search_field1').hide();
			$('.advanced_search_field1 .mapBox').css('display', 'none');
            $(this).removeClass('open').text('показать карту');
            $('#advanced_search1').value = '0';
        }

    );

    if (!load) {
        // Нажатие ссылки с количеством комнат
        $('.roomBtn').toggle(
            function(){
                $(this).addClass('select');
                var id = this.id;
                id = id.substr(8,1);
                roomArr[id] = 1;
                document.getElementById('room').value = roomArr.toString();
            },
            function() {
                $(this).removeClass('select');
                var id = this.id;
                id = id.substr(8,1);
                roomArr[id] = 0;
                document.getElementById('room').value = roomArr.toString();
            }
        );
    	$('#search_obj .city_link').click(function() {
    	    if ($(this).hasClass('select')) return;

    	    var id = $(this).attr('id');
    	    id = id.substr(8,1);
    	    document.forms['search_obj'].elements['fld[city]'].value = id;

    	    $('#searchMetroLink').toggle();
    		$('#searchRaionLink').toggle();

    		$(this).addClass('select');
    		$('#citylink'+(id == 1 ? 2 : 1)).removeClass('select');
    	});

		$("#search-price").slider({
			range: true,
			min: min_price,
			max: max_price,
			step: 5000,
			values: [price_from, price_to],
			slide: function(event, ui) {
				$("#price-from").val(q2000_rnt.search.number_format(ui.values[0], 0, '.', ' '));
				$("#price-to").val(q2000_rnt.search.number_format(ui.values[1], 0, '.', ' ') );
			},
			change: function(event, ui) {self.load()}
		});
		$("#price-from").val(q2000_rnt.search.number_format($("#search-price").slider("values", 0), 0, '.', ' '));
		$("#price-to").val(q2000_rnt.search.number_format($("#search-price").slider("values", 1), 0, '.', ' '));

		$("#search-total_area").slider({
			range: true,
			min: min_total_area,
			max: max_total_area,
			step: 1,
			values: [total_area_from, total_area_to],
			slide: function(event, ui) {
				$("#total-area-from").val(ui.values[0]);
				$("#total-area-to").val(ui.values[1]);
			},
			change: function(event, ui) {self.load()}
		});
		$("#total-area-from").val($("#search-total_area").slider("values", 0));
		$("#total-area-to").val($("#search-total_area").slider("values", 1));
    } else {
	    //Бегунки
		$("#search-price").slider({
			range: true,
			min: min_price,
			max: max_price,
			step: 5000,
			values: [price_from, price_to],
			slide: function(event, ui) {
				$("#price-from").val(q2000_rnt.search.number_format(ui.values[0], 0, '.', ' '));
				$("#price-to").val(q2000_rnt.search.number_format(ui.values[1], 0, '.', ' ') );
			},
			change: function(event, ui) {self.load()}
		});
		$("#price-from").val(q2000_rnt.search.number_format($("#search-price").slider("values", 0), 0, '.', ' '));
		$("#price-to").val(q2000_rnt.search.number_format($("#search-price").slider("values", 1), 0, '.', ' '));

		$("#search-storey").slider({
			range: true,
			min: min_storey,
			max: max_storey,
			step: 1,
			values: [min_storey, max_storey],
			slide: function(event, ui) {
				$("#storey-from").val(ui.values[0]);
				$("#storey-to").val(ui.values[1]);
			},
			change: function(event, ui) {self.load()}
		});
		$("#storey-from").val($("#search-storey").slider("values", 0));
		$("#storey-to").val($("#search-storey").slider("values", 1));

		$("#search-storeys-number").slider({
			range: true,
			min: min_storeys_number,
			max: max_storeys_number,
			step: 1,
			values: [min_storeys_number, max_storeys_number],
			slide: function(event, ui) {
				$("#storeys-number-from").val(ui.values[0]);
				$("#storeys-number-to").val(ui.values[1]);
			},
			change: function(event, ui) {self.load()}
		});
		$("#storeys-number-from").val($("#search-storeys-number").slider("values", 0));
		$("#storeys-number-to").val($("#search-storeys-number").slider("values", 1));

		$("#search-total_area").slider({
			range: true,
			min: min_total_area,
			max: max_total_area,
			step: 1,
			values: [total_area_from, total_area_to],
			slide: function(event, ui) {
				$("#total-area-from").val(ui.values[0]);
				$("#total-area-to").val(ui.values[1]);
			},
			change: function(event, ui) {self.load()}
		});
		$("#total-area-from").val($("#search-total_area").slider("values", 0));
		$("#total-area-to").val($("#search-total_area").slider("values", 1));

		$("#search-live-area").slider({
			range: true,
			min: min_living_area,
			max: max_living_area,
			step: 1,
			values: [min_living_area, max_living_area],
			slide: function(event, ui) {
				$("#live-area-from").val(ui.values[0]);
				$("#live-area-to").val(ui.values[1]);
			},
			change: function(event, ui) {self.load()}
		});
		$("#live-area-from").val($("#search-live-area").slider("values", 0));
		$("#live-area-to").val($("#search-live-area").slider("values", 1));

		$("#search-kitchen-area").slider({
			range: true,
			min: min_kitchen_area,
			max: max_kitchen_area,
			step: 1,
			values: [min_kitchen_area, max_kitchen_area],
			slide: function(event, ui) {
				$("#kitchen-area-from").val(ui.values[0]);
				$("#kitchen-area-to").val(ui.values[1]);
			},
			change: function(event, ui) {self.load()}
		});
		$("#kitchen-area-from").val($("#search-kitchen-area").slider("values", 0));
		$("#kitchen-area-to").val($("#search-kitchen-area").slider("values", 1));
    }
};

q2000_rnt.search.changeStar1 = function(id) {
    $('#star'+id).attr('src', '/images/one_star_a.gif');
    starArr[id] = 1;
    document.getElementById('rate').value = starArr.toString();
    toggleStar[id] = 1;
};
q2000_rnt.search.changeStar2 = function(id) {
    $('#star'+id).attr('src', '/images/one_star.gif');
    starArr[id] = 0;
    document.getElementById('rate').value = starArr.toString();
    toggleStar[id] = 0;
};

/**
 * Загрузка
 */
q2000_rnt.search.load = function(show_favorite) {
	var self = this;
	var limit = document.getElementById("limit_obj").value;
	var sort = document.getElementById("sort_obj").value;
	var sort_d = document.getElementById("sort_d_obj").value;
	if (show_favorite) {
		var furl = '&favorite=1';
		firstShow = true;
	} else {
		var furl = '';
	}
    $.ajax({
        type: "POST",
		url: "search_rnt_result"+'?limit='+encodeURIComponent(limit)+'&sort='+encodeURIComponent(sort)+'&sort_d='+encodeURIComponent(sort_d)+furl,
        dataType: "json",
		data: $('#search_obj').serialize(),

        timeout: 50000,

        beforeSend: function(){
			document.getElementById("popupLoad").style.display = 'block';
        },

        success: function(data) {
			if (show_favorite) {
				favorite_items = true;
				document.getElementById("print").style.display = 'inline';
				document.getElementById("show_o").style.display = 'inline';
			} else {
				favorite_items = false;
				document.getElementById("print").style.display = 'none';
				document.getElementById("show_o").style.display = 'none';
			}
			self._onSuccess(data);
        },

        error: function(request, status, errorT) {
//		alert("XMLHttpRequest="+request.responseText+"\ntextStatus="+status+"\nerrorThrown="+errorT);
        	$('#listBoxDataTable').html('Произошел сбой. Запрос не может быть выполнен. Повторите попытку.');
        }
    });
};

q2000_rnt.search._onSuccess = function(data) {
    var self = this;
    var p;
    var area = $('#loadArea2');

	if (typeof data.tableClass != 'undefined') {
		$('#tablClass').html(data.tableClass);
    }

	if (typeof data.xml_objects != 'undefined') {
		initMap(data.xml_objects);
	}

	if (typeof data.table != 'undefined') {
		$('#listBoxDataTable').html(data.table);
	}

	if (typeof data.searchResultText != 'undefined') {
		$('#searchResultText').html(data.searchResultText);
	}

	if (typeof data.favoriteLink != 'undefined') {
		$('#favoriteLink').html(data.favoriteLink);
	} else {
		$('#favoriteLink').html("<a href=\"javascript:void(0);\" onClick=\"q2000_rnt.search.addToFavorite('add');\">Добавить выделенные объекты в избранное</a>");
	}

	if (typeof data.show_f != 'undefined') {
		$('#show_f').html(data.show_f);
	}

    document.getElementById("page_obj").value = '0';
	document.getElementById("popupLoad").style.display = 'none';

	q2000_rnt.search.checkObject();

	if (!firstShow) {
        $('.advanced_search_field').show();
        $('#search_form_view').html('Краткий вид поиска');
		$('.advanced_search_field').each(function(el) {$(this).show();});
        document.getElementById('advanced_search').value = '1';
		self.load();
		firstShow = true;
	}

};

q2000_rnt.search.openDiv = function (id) {
    document.getElementById(id).style.display = 'block';
};

q2000_rnt.search.closeDiv = function (id) {
    document.getElementById(id).style.display = 'none';
};



q2000_rnt.search.addDistricts = function() {
    var self = this;
    var raionArrLength = 0;
    var cityArrLength = 0;
    var raionListCount = document.getElementById('raionListCount');
    raionListCount.innerHTML = '';

	for (var i in raionArr) {
		raionArrLength = raionArrLength + 1;
	}
	for (var i in cityArr) {
		cityArrLength = cityArrLength + 1;
	}
	if ((raionArrLength+cityArrLength) > 0){
		raionListCount.innerHTML = '<a href="javascript:void(0);" onClick="q2000_rnt.search.openDiv(\'searchRaion\');">Выбрано '+(raionArrLength+cityArrLength)+' объектов</a>';
	} else {
		raionListCount.innerHTML = '';
	}
};

q2000_rnt.search.clearDistricts = function() {
    var self = this;
    var form = document.getElementById('search_obj');
    for (var i in cityArr) {
        if (document.getElementById("city_mo_id_"+i)) {form.removeChild(document.getElementById("city_mo_id_"+i));}
		$('#city_item'+i).addClass('selected');
        delete cityArr[i];
	}
	$('#removeC').click();

    for (var i in raionArr) {
        document.getElementById('name'+i).style.display = 'none';
        if (document.getElementById("raion_id_"+i)) {form.removeChild(document.getElementById("raion_id_"+i));}
		$('#district_item'+i).addClass('selected');
        delete raionArr[i];
	}
	$('#removeD').click();
	self.addDistricts();
    self.load();
    return false;
};

q2000_rnt.search.addMetro = function() {
    var self = this;
    var metroArrLength = 0;
    var metroListCount = document.getElementById('metroListCount');
    metroListCount.innerHTML = '';
	for (var i in metroArr) {
		metroArrLength = metroArrLength + 1;
	}
	if (metroArrLength > 0) {
		metroListCount.innerHTML = '<a href="javascript:void(0);" onClick="q2000_rnt.search.openDiv(\'searchMetro\');">Выбрано '+metroArrLength+' объектов</a>';
	} else {
		metroListCount.innerHTML = '';
	}
};

q2000_rnt.search.setMetroType = function(val) {
	var self = this;
	var time_way1, time_way2, time_from, time_to;

	document.getElementById("metro_time_type").value = val;

	time_way1 = document.getElementById("time_way_1");
	time_way2 = document.getElementById("time_way_2");
	if (val=='1'){
		if (time_way1.className=='timeWay car'){
			time_way1.className='timeWay carActive';
		} else {
			time_way1.className='timeWay car';
			document.getElementById("metro_time_type").value = '0';
		}
		time_way2.className='timeWay walk';
	}
	if (val=='2'){
		if (time_way2.className=='timeWay walk'){
			time_way2.className='timeWay walkActive';
		} else {
			time_way2.className='timeWay walk';
			document.getElementById("metro_time_type").value = '0';
		}
		time_way1.className='timeWay car';
	}

	time_from = document.getElementById("metro_time_from").value;
	time_to = document.getElementById("metro_time_to").value;
	if (time_from || time_to) {
	    self.load();
	}
    return false;
}

q2000_rnt.search.clearMetro = function() {
    var self = this;
    var form = document.getElementById('search_obj');
    for (var i in metroArr) {
        document.getElementById("pp"+i).style.display = 'none';
        form.removeChild(document.getElementById("metro_id_"+i));
        $('#metro_item'+i).addClass('selected');
        delete metroArr[i];
    }
    $('#removeM').click();
    self.addMetro();
    self.load();
    return false;
};

q2000_rnt.search.clearMetroType = function() {
	document.getElementById("metro_time_from").value = '';
	document.getElementById("metro_time_to").value   = '';
	document.getElementById("metro_time_type").value = '';
}

q2000_rnt.search.clearPrice = function() {
	document.getElementById("price-from").value = '';
	document.getElementById("price-to").value   = '';
	//document.getElementById("price_type").value = '';
}

q2000_rnt.search.clearStorey = function() {
	document.getElementById("storey-from").value = '';
	document.getElementById("storey-to").value   = '';
	document.getElementById('storey_first').checked=false;
	document.getElementById('storey_last').checked=false;
}

q2000_rnt.search.clearStoreysNumber = function() {
	document.getElementById("storeys-number-from").value = '';
	document.getElementById("storeys-number-to").value   = '';
}

q2000_rnt.search.clearHouseType = function() {
    for (var i in houseArr) {
    	document.getElementById("house_type_"+i).checked=false;
    	delete houseArr[i];
	}
	document.getElementById("house_list").innerHTML = '';
}

q2000_rnt.search.houseTypeAct = function(id) {
	var obj = document.getElementById("house_type_"+id);
	if (obj.checked){
		houseArr[id]=[document.getElementById("house_type_"+id).title];
	} else {
		delete houseArr[id];
	}
}

q2000_rnt.search.houseTypeGetList = function() {
    var list = document.getElementById('house_list');
    list.innerHTML = '';
    if (houseArr.length > 0) {
        for (var i in houseArr) {
            var li = document.createElement('LI');
            li.innerHTML = houseArr[i];
            list.appendChild(li);
        }
	}
}

q2000_rnt.search.rateAct = function(id) {
	var obj = document.getElementById("rate_"+id);
	if (obj.checked){
		rateArr[id]=[document.getElementById("rate_"+id).title];
	} else {
		delete rateArr[id];
	}
	var self=this;
	self.load();
}

q2000_rnt.search.rateGetList = function() {
    var list = document.getElementById('rate_list');
    list.innerHTML = '';
    if (rateArr.length > 0) {
        for (var i in rateArr) {
            var li = document.createElement('LI');
            li.innerHTML = rateArr[i];
            list.appendChild(li);
        }
	}
}

q2000_rnt.search.clearArea = function() {
	document.getElementById("total-area-from").value	= '';
	document.getElementById("total-area-to").value		= '';
	document.getElementById("live-area-from").value		= '';
	document.getElementById("live-area-to").value		= '';
	document.getElementById("kitchen-area-from").value	= '';
	document.getElementById("kitchen-area-to").value	= '';
}

q2000_rnt.search.clearSearch = function() {
	document.getElementById('addres').value='';
	document.getElementById('fldroom1').value="";
	document.getElementById('fldroom2').value="";
	document.getElementById('fldroom3').value="";
	document.getElementById('fldroom4').value="";
	document.getElementById('fldroom5').value="";
	document.getElementById('fldroom_link1').className="";
	document.getElementById('fldroom_link2').className="";
	document.getElementById('fldroom_link3').className="";
	document.getElementById('fldroom_link4').className="";
	document.getElementById('fldroom_link5').className="five";
	q2000_rnt.search.clearRate(true);
	q2000_rnt.search.clearMetro();
	q2000_rnt.search.clearPrice();
	q2000_rnt.search.clearStorey();
	q2000_rnt.search.clearStoreysNumber();
	q2000_rnt.search.clearHouseType();
	q2000_rnt.search.clearArea();
	document.getElementById('ipoteka').checked=false;
	document.getElementById('credit').checked=false; q2000_rnt.search.load();
	$('#search_obj input[type=checkbox]').each(function() {this.checked=false;});
	this.load();
}

q2000_rnt.search.clearRate = function(home) {
    var self = this;
    for (var i in rateArr) {
    	document.getElementById("rate_"+i).checked=false;
    	delete rateArr[i];
	}
	if (!home) {
		document.getElementById("rate_list").innerHTML = '';
		self.load();
	}
}

q2000_rnt.search.tableSort = function(field) {
	var self = this;
	var sort_obj	= document.getElementById("sort_obj").value;
	var sort_d_obj	= document.getElementById("sort_d_obj").value;

	if (sort_obj==field) {
		if (sort_d_obj=='asc') sort_d_obj = 'desc';
		else sort_d_obj = 'asc';
	} else {
		sort_obj = field;
		sort_d_obj = 'asc';
	}

	document.getElementById("sort_obj").value = sort_obj;
	document.getElementById("sort_d_obj").value = sort_d_obj;
	self.load();
}

q2000_rnt.search.selectAllToFavorite = function() {
	var obj;
	var chkd;
	obj = document.getElementsByName("fld[obj_check][]");
	chkd = document.getElementById("obj_check").checked;
	if (obj.length > 0) {
		for (var i in obj) {
			if (obj[i].value != 'undefined') obj[i].checked=chkd;
		}
	}

	q2000_rnt.search.checkObject();
}

q2000_rnt.search.addToFavorite = function(doit) {
    var self = this;
	$.ajax({
		type: "POST",
		url: "search_add_to_favorite"+"?doit="+encodeURIComponent(doit),
		dataType: "json",
		data: $("input:checked").serialize(),

        beforeSend: function(){
			document.getElementById("popupLoad").style.display = 'block';
        },

		success: function(data) {
			if (typeof data.favorite_count != 'undefined') {
				$('#show_f').html(data.favorite_count);
				if (data.favorite_count=="") showOrderBox=false;
			}

			document.getElementById("popupLoad").style.display = 'none';
		},
		timeout: 50000
	});
	if (doit=='delete') {
		self.load(true);
	}
}

q2000_rnt.search.addToFavoriteObject = function(doit, id) {
    var self = this;
	$.ajax({
		type: "POST",
		url: "search_rnt_add_to_favorite?id="+id+"&doit="+encodeURIComponent(doit),

        beforeSend: function(){
			document.getElementById("popupLoad").style.display = 'block';
        },

		success: function(data) {
			if (typeof data.favorite_count != 'undefined') {
				$('#show_f').html(data.favorite_count);
				if (data.favorite_count=="") showOrderBox=false;
			}
			if (typeof data.favorite_img != 'undefined') {
				$('#favorite_'+id).html(data.favorite_img);
			}

			document.getElementById("popupLoad").style.display = 'none';

			if (doit!='add' && favorite_items) {
				self.load(true);
			}
		},
		timeout: 50000
	});
}

q2000_rnt.search.number_format = function(number, decimals, dec_point, thousands_sep) {
	// Thanks by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
	var i, j, kw, kd, km;
	// input sanitation & defaults
	if( isNaN(decimals = Math.abs(decimals)) ){decimals = 2;}
	if( dec_point == undefined ){dec_point = ",";}
	if( thousands_sep == undefined ){thousands_sep = ".";}

	i = parseInt(number = (+number || 0).toFixed(decimals)) + "";

	if( (j = i.length) > 3 ){j = j % 3;}
	else {j = 0;}

	km = (j ? i.substr(0, j) + thousands_sep : "");
	kw = i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands_sep);
	kd = (decimals ? dec_point + Math.abs(number - i).toFixed(decimals).replace(/-/, 0).slice(2) : "");
	return km + kw + kd;
}

q2000_rnt.search.checkObject = function (){
	if (showOrderBox) return true;
	var obj;
	var chkd, str;
	obj = document.getElementsByName("fld[obj_check][]");
	chkd = false;
	if (obj.length > 0) {
		for (var i in obj) {
			str = str + 'i='+i+' obj[i]='+obj[i].checked+'; ';
			if (obj[i].value!='undefined') {
				if (obj[i].checked) chkd = true;
			}
		}
	}
	if (chkd) document.getElementById("sendOrderBox").style.display = 'inline';
	else {
		document.getElementById("sendOrderBox").style.display = 'none';
		document.getElementById("sendOrder").style.display = 'none';
	}
}

q2000_rnt.search.sendObj = function() {
    var self = this;
	$.ajax({
		type: "POST",
		url: "send_order_rnt",
		dataType: "json",
		data: $('#send_obj, input:checked').serialize(),

		success: function(data) {
			if (typeof data.error_form != 'undefined') {
				q2000_rnt.search.closeDiv('sendOrderMsg');
				$('#orderFormError').html(data.error_form);
				document.getElementById("orderFormMsg").innerHTML = '';
			}
			if (typeof data.msg_form != 'undefined') {
				q2000_rnt.search.closeDiv('sendOrder');
				document.getElementById("orderFormError").innerHTML = '';
				q2000_rnt.search.openDiv('sendOrderMsg');
				$('#orderFormMsg').html(data.msg_form);
			}
		},
		timeout: 50000
	});
}

q2000_rnt.search.setRooms = function(clicked) {
	var fldroom=document.getElementById('fldroom'+clicked);
	var fldroom_link=document.getElementById('fldroom_link'+clicked);
	if(fldroom.value=="") {
		fldroom.value=clicked;
		if(clicked==5) fldroom_link.className="select five";
		else fldroom_link.className="select";
	}
	else {
		fldroom.value="";
		if(clicked==5) fldroom_link.className="five";
		else fldroom_link.className="";
	}
	var self=this;
	self.load();
}

q2000_rnt.search.addHouseTypes = function() {
    var self = this;
    var list = document.getElementById('house_list');
    list.innerHTML = '';
    if(houseArr.length > 0) {
        for(var i in houseArr) {
            var li = document.createElement('LI');
            li.innerHTML = houseArr[i];
            list.appendChild(li);
        }
    }
};

q2000_rnt.search.showMap = function (ii) {
	var lat, lng, address, name;
    //document.getElementById('object_map'+ii+'').style.display = 'block';
	if (GBrowserIsCompatible()) {
		mapObject = new GMap2(document.getElementById("googleObjectMap"+ii+""));
	//	mapObject.addControl(new GMapTypeControl());
		mapObject.addControl(new GLargeMapControl());
		mapObject.setCenter(new GLatLng(55.75, 37.62), 10);

		var geocoder = new GClientGeocoder();

		lat = parseFloat(markers[ii].getAttribute("lat"));
		lng = parseFloat(markers[ii].getAttribute("lng"));

		var latlng = new GLatLng(lat,lng);
		var label  = "<div class='forMapBlock'>";
	//	label += "<img class='pic' src='/images/pi_nophoto_big.gif' alt='' />";
		label += "<img class='ratePic' src='/images/star_left_"+markers[ii].getAttribute("stars")+".gif' width='95' height='17' alt='' />";
		label += "<p>"+markers[ii].getAttribute("name")+"</p>";
		label += "<p style='clear:right;'><strong>"+markers[ii].getAttribute("address")+"</strong></p>";
		label += "<p>"+markers[ii].getAttribute("total_area")+"\/"+markers[ii].getAttribute("living_area")+"\/"+markers[ii].getAttribute("kitchen_area")+" кв.м</p>";
		label += "<p>"+markers[ii].getAttribute("price_rub")+" руб. <a href='obj_search/object/"+markers[ii].getAttribute("obj_id")+"/'>подробнее</a></p>";
		label += "</div>";

		var tinyIconNew = new GIcon();
 tinyIconNew.image = "/images/marker_green.png"; // путь к иконке
 tinyIconNew.shadow = "http://labs.google.com/ridefinder/images/mm_20_shadow.png"; // к тени (если она вам нужна)
 tinyIconNew.iconSize = new GSize(20, 34); //размеры иконки
 tinyIconNew.shadowSize = new GSize(20, 34); // размеры тени
 tinyIconNew.iconAnchor = new GPoint(6, 20); // "центр" иконки
 tinyIconNew.infoWindowAnchor = new GPoint(5, 1); // точка привязки инфоокна
 markerOptions = { icon:tinyIconNew };


		mapObject.setCenter(latlng, 14);
		var markerObject = new GMarker(latlng, markerOptions);

		mapObject.addOverlay(markerObject);
	//	markerObject.openInfoWindowHtml(label);
	}
};
q2000_rnt.search.showMapGoogle = function (ii) {
	var lat, lng, address, name;
	document.getElementById('object_mapmouse').style.display = 'block';
	if (GBrowserIsCompatible()) {
		mapObject = new GMap2(document.getElementById("googleObjectMapmouse"));
		mapObject.addControl(new GMapTypeControl());
		mapObject.addControl(new GLargeMapControl());
		mapObject.setCenter(new GLatLng(55.75, 37.62), 10);

		var geocoder = new GClientGeocoder();

		lat = parseFloat(markers[ii].getAttribute("lat"));
		lng = parseFloat(markers[ii].getAttribute("lng"));

		var latlng = new GLatLng(lat,lng);
		var label  = "<div class='forMapBlock'>";
	//	label += "<img class='pic' src='/images/pi_nophoto_big.gif' alt='' />";
		label += "<img class='ratePic' src='/images/star_left_"+markers[ii].getAttribute("stars")+".gif' width='95' height='17' alt='' />";
		label += "<p>"+markers[ii].getAttribute("name")+"</p>";
		label += "<p style='clear:right;'><strong>"+markers[ii].getAttribute("address")+"</strong></p>";
		label += "<p>"+markers[ii].getAttribute("total_area")+"\/"+markers[ii].getAttribute("living_area")+"\/"+markers[ii].getAttribute("kitchen_area")+" кв.м</p>";
		label += "<p>"+markers[ii].getAttribute("price_rub")+" руб. <a href='obj_search/object/"+markers[ii].getAttribute("obj_id")+"/'>подробнее</a></p>";
		label += "</div>";
var tinyIconNew = new GIcon();
 tinyIconNew.image = "/images/marker_green.png"; // путь к иконке
 tinyIconNew.shadow = "http://labs.google.com/ridefinder/images/mm_20_shadow.png"; // к тени (если она вам нужна)
 tinyIconNew.iconSize = new GSize(20, 34); //размеры иконки
 tinyIconNew.shadowSize = new GSize(20, 34); // размеры тени
 tinyIconNew.iconAnchor = new GPoint(6, 20); // "центр" иконки
 tinyIconNew.infoWindowAnchor = new GPoint(5, 1); // точка привязки инфоокна
 markerOptions = { icon:tinyIconNew };
		mapObject.setCenter(latlng, 14);
		var markerObject = new GMarker(latlng, markerOptions);

		mapObject.addOverlay(markerObject);
		markerObject.openInfoWindowHtml(label);
	}
};

q2000_rnt.search.openTrQuery = function (){

$(document).ready(function(){

    // this part must be done in css, not here!
    // show first row
	    $('#report tr:first-child').show().
	    // and hide all tips
	        siblings(':nth-child(2n+1)').hide();

	    $('#report tr:odd').unbind().bind('mouseenter', function() {
	        // row
	        var self = $(this),
	        // tip
	            next = self.next();

	        // if tip already visible -- don't do anything
	        if (next.is(':visible')) {
	            return;
        }

	        // instead of show/hide, much better add/remove special class
	        // show tip
	        next.show().
	        // and hide all another tips
	            siblings(':not(:nth-child(2n), :first-child)').hide();

	        // don't know, what is that
	        self.find('.arrownext').toggleClass('up');
	    }).
	    // no need to do eq(0) -- why?
	    triggerHandler('mouseenter');
	});
};

$(document).ready(function() {
    var href = location.href;
    if (href.search(/search_rnt/) != -1) {
        q2000_rnt.search.init(true);
    } else {
        q2000_rnt.search.init(false);
    }
});