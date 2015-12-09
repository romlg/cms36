	function changeView(name){
	   try {
			preview = document.forms.editform.elements['preview[\''+name+'\']'];
			if (preview) {
    			input 	= document.forms.editform.elements[name].value;
    			if (input == '' || input.substring(2,1) == ':'){
    				preview.style.display='none';
    			}
    			else {
    				if (input.length > 4){
    					preview.style.display='inline';
    				}
    			}
			}
		}
		catch(e) {}
		finally {}
	}

	var oldFriend;
	var i =0;
	function setFriend(fname,name,tab,elem_id) {
	    i++;
	    if (i>2) return;
	    form = document.forms.editform;
	    if (elem_id != ''){
	        fname = 'fld['+elem_id+']['+tab+']['+fname+']';
	    }
	    else {
	        fname = 'fld['+tab+']['+fname+']';
	    }
	    if(form.elements[fname].value == '' || (form.elements[fname].value == oldFriend & oldFriend!=form.elements[name+'[0]'].value))
	    {
	        oldFriend = form.elements[name].value;
	        var temp = form.elements[name].value;
	        if (temp.search(' - auto') == -1) {
	            form.elements[fname].value = form.elements[name].value + ' - auto';
	        } else {
	            form.elements[fname].value = form.elements[name].value;
	        }
	    }
	    //заполняем поле названия картики(смотрим 1-й input type=text)
	    try{
	        var coll = document.getElementsByTagName("INPUT");
	        if (coll!=null)
	        {
	            for (i=0; i<coll.length; i++) {
	                if (coll[i].type=="text"){
	                    //if (coll[i].name == name) continue;
	                    if (!form.elements[coll[i].name+"[0]"] && document.getElementById(coll[i].id).value==''){
	                        var re = new RegExp('((.+)\\\\)?(.+)\\.(.+)','ig');
	                        var str = oldFriend;
	                        var arr = re.exec(str);
	                        //$1 и $2-folders
	                        //$3-filename
	                        //$4-exp
	                        document.getElementById(coll[i].id).value = RegExp.$3;
	                    }
	                    break;
	                }
	            }
	        }
	    } catch(e) {}
	}

	function hideRow(id) {
		document.getElementById(id).style.display='none';
	}

	function showRow(id) {
		document.getElementById(id).style.display='inline-block';
	}

	function showImage(name) {
		window.open('/scripts/popup.php?img='+name+'&title='+name, 'image', 'width=300, height=300, resizable=1, status=0').focus();
		//window.open('/'+name, 'image', 'width=300, height=300, resizable=1, status=1').focus();
	}

	function showTreeUrl(name, formname) {
        window.open("dialog.php?page=tree/treeurl&fieldname="+name+"&formname="+formname, 'tree url', 'width=360, height=500, resizable=1, status=0').focus();
        //return false;
	}

	function showTreeId(name, formname, returnid) {
        window.open("dialog.php?page=tree/treeid&fieldname="+name+"&formname="+formname+"&returnid="+returnid, 'tree id', 'width=360, height=500, resizable=1, status=0').focus();
        //return false;
	}

	function showTree(name, formname, frame, returnid, value) {
		formname = formname || 'editform';
		name = name || 'url';
		frame = frame || 'texturl';
		returnid = returnid || 0;
		returnid = value || '';
		var url = window.showModalDialog("dialog.php?page=tree/treeurl&bars=s&fieldname="+name+"&formname="+formname+"&returnid="+returnid+"&id="+document.forms[formname].elements[name].value + "&value_field=" + value,frame,"dialogWidth:360px;dialogHeight:500px");
		if (typeof(url) == "string") {
		  if (url != null) document.forms[formname].elements[name].value=url;
		}
		else if (url) {
    		if (url[0] != null) {
    			document.forms[formname].elements[name].value=url[0];
    		}
    		if (url[1] != null && document.forms[formname].elements[value]) {
    			document.forms[formname].elements[value].value=url[1];
    		}
    	}
	}

	function showColors(color, name, previewid, formname) {
		formname = formname || 'editform';
		previewid = previewid || 'undefined';
		color = document.forms[formname].elements[name].value || color;
		color = window.showModalDialog('/admin/third/colorpicker/colorpicker.html', color, 'dialogWidth: 370px; dialogHeight:245px; help: no; status: no;');
		if (color || color == '') {
			document.forms[formname].elements[name].value = color;
			if (previewid != 'undefined') document.getElementById(previewid).style.backgroundColor = color;
		}
		return false;
	}
/*
* автоматическое заполнение мета контента
*/
	function generateData(id) {
		url = '/admin/page.php?page=tree&do=editgenerateMeta&id='+id;
		loadXMLDoc(url);
	}

	function loadXMLDoc(url) {
		if (window.XMLHttpRequest) {
			req = new XMLHttpRequest();
			req.onreadystatechange = showGenerateData;
			req.open("GET", url);
			req.send(null);
		} else if (window.ActiveXObject) {
			req = new ActiveXObject("Microsoft.XMLHTTP");
			if (req) {
				req.onreadystatechange = showGenerateData;
				req.open("POST", url, true);
				req.send();
			}
		}
	}

	function showGenerateData(){
		if (req.readyState == 4) {
			if (req.status == 200) {
				try{
					response = req.responseXML.documentElement;
					var rows = new Array();
					XMLtoArray(response, rows);
					document.getElementById('fld[title]').innerHTML = rows['headers'].title;
					document.getElementById('fld[keywords]').innerHTML = rows.headers.keywords;
					document.getElementById('fld[description]').innerHTML = rows.headers.description;
				} catch (e) {
				}
			}
		}
	}

	function XMLtoArray(response, ar){
		if(!response) return;
		if(!response.childNodes) return;

		var count = response.childNodes.length;

		for (var i=0; i<count; i++){
			var name = response.childNodes.item(i).nodeName;
			if (name == '#text') continue;
			var item = response.childNodes.item(i);
			if (item.childNodes.item(0)){
				var type = item.childNodes.item(0).nodeType;
			} else {type = 2;}

			if (type == 1){
				ar[name] = new Array();
				XMLtoArray(item, ar[name]);
			} else if (type == 3) {
				ar[name] = item.childNodes.item(0).nodeValue;
			} else {
				ar[name] = '';
			}
		}
	}


	///////////////////////////////
	//
	// Функции для tab-ов
	//
	///////////////////////////////

	function blockOpenClose() {
    	if ($('dl.block dt')) $('dl.block dt').click(function(){
    		$(this).toggleClass('close').end();
    		$(this).next('dd').toggleClass('close');
    		$.cookie($(this).attr('id'), $(this).next('dd').hasClass('close'));
    	});
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

    // min width ie6
    function minWidth2() {
    	var d = document;
    	var winIE = (navigator.userAgent.indexOf('Opera')==-1 && (d.getElementById && d.documentElement.behaviorUrns)) ? true : false;

    	function bodySize() {
    		if(winIE && d.documentElement.clientWidth) {
    			sObj = d.getElementsByTagName('body')[0].style;
    			sObj.width = (d.documentElement.clientWidth < 500) ? '500px' : '100%';
    		}
    	}

    	function init() {
    		if(winIE) { bodySize(); }
    	}
    	onload = init;
    	if(winIE) { onresize = bodySize; }
    }

    // tsb switch
    function tabsSwitch() {
    	if ($('dl.block dt'))
    	$('dl.tabs dt').click(function(){
    	    if (!$(this).attr('id')) return;
            $(this).siblings().removeClass('select').end().next('dd').andSelf().addClass('select');
    		var href = location.href;
    		if (href.search(/#/) == -1) {
    		  location.href += '#' + $(this).attr('id');
    	    }
    	    else {
    	        var pos = href.search(/#/);
    	        var tab_id = href.substr(pos+1);
    	        if (tab_id != $(this).attr('id')) {
    	            location.href = href.substr(0, pos) + '#' + $(this).attr('id');
    	        }
    	    }
    	    try {
    	       var page = getParamFromURL('page');
    	       var id = getParamFromURL('id');
    	       $.cookie(page+id+'_tab', $(this).attr('id'));
    	    } catch (e) {}
    	});
    }

    function currentTabLoad() {
        var href = location.href;
        var pos;
        if ((pos = href.search(/#/)) != -1) {
            var tab_id = href.substr(pos+1);
            if (document.getElementById(tab_id)) {
                $('#'+tab_id).click();
            }
        }
        else {
            // пытаемся найти закладку в куках
            var tab_id;
            var page = getParamFromURL('page');
            var id = getParamFromURL('id');
            tab_id = $.cookie(page+id+'_tab');
            if (document.getElementById(tab_id)) {
                $('#'+tab_id).click();
            }
        }
    }

    // находит все четные строки в таблице и ставит им (строкам) class="odd"
    function tableRow() {
    	$('table.list tr:odd').addClass('odd');
    }

    function getParamFromURL(param_name) {
        var tmp = new Array();      // два вспомагательных
        var tmp2 = new Array();     // массива
        var param = new Array();

        var ret = "";
        var get = location.search;  // строка GET запроса
        if (get == '') return ret;

        tmp = (get.substr(1)).split('&');   // разделяем переменные
        for(var i=0; i < tmp.length; i++)
        {
            tmp2 = tmp[i].split('=');       // массив param будет содержать
            param[tmp2[0]] = tmp2[1];       // пары ключ(имя переменной)->значение
        }

        for (var key in param)
        {
            if (key == param_name) return param[key];
        }
        return ret;
    }

    function frame_button(obj) {
        obj_init = $(obj).attr('init');
        if (typeof(obj_init)=='undefined') {
        	$(obj).fancybox({
                'type'          : 'iframe',
                'width'         : '50%',
                'height'        : 500,
                'centerOnScroll': true,
                'autoScale'     : false,
                'transitionIn'	: 'none',
                'transitionOut'	: 'none',
                'hideOnOverlayClick' : false
        	});
        	$(obj).attr('init', 1);
            $(obj).click();
        }
    }

    function show_preview(obj, id) {
        obj_href = document.getElementById(id).value;
        obj_init = $(obj).attr('init');
        if (typeof(obj_init)=='undefined') {
        	$(obj).fancybox({
        	    'href' : obj_href,
                'centerOnScroll': true,
                'autoScale'     : false,
                'transitionIn'	: 'none',
                'transitionOut'	: 'none',
                'hideOnOverlayClick' : false
        	});
        	$(obj).attr('init', 1);
            $(obj).click();
        }
    }

	function showFiles(file, name, formname) {
		formname = formname || 'editform';
		file = document.forms[formname].elements[name].value || file;
		file = file.substring(0, file.lastIndexOf('/'));
//		FmWin = window.open("dialog.php?page=fmr&dir="+file+"&field="+name+"&do=select&formname="+formname, "linkUrl", "width=900, height=600, resizable=1, scrollbars=1, status=1");
//		if (FmWin) FmWin.focus();
        window.KCFinder = {
            callBack: function(url) {
                document.getElementById(name).value = url;
                window.KCFinder = null;
            }
        };
        window.open('./third/kcfinder/browse.php?langCode=ru&dir='+file, 'kcfinder_textbox',
            'status=0, toolbar=0, location=0, menubar=0, directories=0, resizable=1, scrollbars=0, width=900, height=600'
        );
	}

	function getQuoteJqueryString (str) {
        var searchArr = new Array();
        var replaceArr = new Array();

        searchArr[1] = "\\!";
        searchArr[2] = '\\"';
        searchArr[3] = "\\#";
        searchArr[4] = "\\$";
        searchArr[5] = "\\%";
        searchArr[6] = "\\&";
        searchArr[7] = "\\'";
        searchArr[8] = "\\(";
        searchArr[9] = "\\)";
        searchArr[10] = "\\*";
        searchArr[11] = "\\+";
        searchArr[12] = "\\,";
        searchArr[13] = "\\.";
        searchArr[14] = "\\/";
        searchArr[15] = "\\:";
        searchArr[16] = "\\;";
        searchArr[17] = "\\<";
        searchArr[18] = "\\=";
        searchArr[19] = "\\>";
        searchArr[20] = "\\?";
        searchArr[21] = "\\@";
        searchArr[22] = "\\[";
        searchArr[23] = "\\]";
        searchArr[24] = "\\^";
        searchArr[25] = "\\`";
        searchArr[26] = "\\{";
        searchArr[27] = "\\|";
        searchArr[28] = "\\}";
        searchArr[29] = "\\~";

        replaceArr[1] = "\\\\!";
        replaceArr[2] = '\\\\"';
        replaceArr[3] = "\\\\#";
        replaceArr[4] = "\\\\$";
        replaceArr[5] = "\\\\%";
        replaceArr[6] = "\\\\&";
        replaceArr[7] = "\\\\'";
        replaceArr[8] = "\\\\(";
        replaceArr[9] = "\\\\)";
        replaceArr[10] = "\\\\*";
        replaceArr[11] = "\\\\+";
        replaceArr[12] = "\\\\,";
        replaceArr[13] = "\\\\.";
        replaceArr[14] = "\\\\/";
        replaceArr[15] = "\\\\:";
        replaceArr[16] = "\\\\;";
        replaceArr[17] = "\\\\<";
        replaceArr[18] = "\\\\=";
        replaceArr[19] = "\\\\>";
        replaceArr[20] = "\\\\?";
        replaceArr[21] = "\\\\@";
        replaceArr[22] = "\\\\[";
        replaceArr[23] = "\\\\]";
        replaceArr[24] = "\\\\^";
        replaceArr[25] = "\\\\`";
        replaceArr[26] = "\\\\{";
        replaceArr[27] = "\\\\|";
        replaceArr[28] = "\\\\}";
        replaceArr[29] = "\\\\~";

        for (var i in searchArr) {
	       str = str.replace(new RegExp(searchArr[i], "g"), replaceArr[i]);
        }

        return str;
	}

function getTreeLinkHtml(id, fld, path) {
    $('#' + fld).val(id);
    $.ajax({
        url        : '/admin/?page=tree&do=showTreeLinkHtml',
        type       : 'POST',
        data       : {id : id, fld : fld, path : path},
        dataType   : 'json',
        success    : function(data) {
            if (data['html'] != 'undefined') {
                $('#treeid_' + fld).html(data['html']);
                $('#input_treeid_link_' + fld).hide();
            } else {
                $('#input_treeid_link_' + fld).show();
            }
            show_depends_fields();
        },
        error      : function() {
        }
    });
}

function getTreeLinksHtml(ids, fld, path) {
    $('#' + fld).val(ids);
    $.ajax({
        url        : '/admin/?page=tree&do=showTreeLinksHtml',
        type       : 'POST',
        data       : {ids : ids, fld : fld, path : path},
        dataType   : 'json',
        success    : function(data) {
            if (data['html'] != 'undefined') {
                $('#treecheck_' + fld).html(data['html']);
            }
            show_depends_fields();
        },
        error      : function() {
        }
    });
}

function reset_selectid(fld) {
    $('#treeid_' + fld).html('');
    $('#' + fld).val('');
    $('#input_treeid_link_' + fld).show();
    show_depends_fields();
}

function remove_from_treecheck(id, fld, link) {
    var ids = $('#' + fld).val();
    ids = ids.split(",");
    var idsr = [];
    var j = 0;
    for (var i=0; i < ids.length; i++) {
        if (ids[i] != id) {
            idsr[j++] = ids[i];
        }
    }
    $('#' + fld).val(idsr.join(','));
    $(link).parent().remove();
    show_depends_fields();
}

jQuery.expr[':'].regex = function(elem, index, match) {
    var matchParams = match[3].split(','),
        validLabels = /^(data|css):/,
        attr = {
            method: matchParams[0].match(validLabels) ?
                        matchParams[0].split(':')[0] : 'attr',
            property: matchParams.shift().replace(validLabels,'')
        },
        regexFlags = 'ig',
        regex = new RegExp(matchParams.join('').replace(/^\s+|\s+$/g,''), regexFlags);
    return regex.test(jQuery(elem)[attr.method](attr.property));
}

function show_depends_fields() {
    var cur_name = '';
    var form = $("#editform");
    form.find(":regex(name, ^fld*)").each(function(){
        cur_name = $(this).attr('name');
        cur_name = cur_name.replace(new RegExp("\\[", 'g'), "\\[");
        cur_name = cur_name.replace(new RegExp("\\]", 'g'), "\\]");
        if ($(this).val()) {
            $('.if_show_' + cur_name).show();
        } else {
            $('.if_show_' + cur_name).hide();
        }
    });
}

$(document).ready(function(){
    show_depends_fields();
});
