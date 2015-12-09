var agent = navigator.userAgent.toLowerCase();
var major = parseInt(navigator.appVersion);
var minor = parseFloat(navigator.appVersion);

var isNN = ((agent.indexOf('mozilla') != -1) && ((agent.indexOf('spoofer') == -1) && (agent.indexOf('compatible') == -1)));
var isOPERA = agent.indexOf("opera")>-1 && window.opera;
var isIE = (agent.indexOf("msie") != -1 && !isOPERA);


document.body.style.backgroundColor="#ECEAEA";
document.body.scroll  ='no';
var watermark = 0;
var watermark_rang = 0;

function showFiles2(file, name, formname) {
	formname = formname || 'editform';
	file = document.forms[formname].elements[name].value || file;
	file = file.substring(0, file.lastIndexOf('/'));
	FmWin = window.open("ced.php?page=fm2&dir="+file+"&field="+name+"&formname="+formname, "linkUrl", "width=700, height=500, resizable=1, status=1");
	if (FmWin) FmWin.focus();
}

	
function sendValues(){
	document.getElementById('down_dir3').value = document.getElementById('info_all').DIR;
	document.getElementById('do').value = "EditSendValue";
	document.forms.editform3.submit();
}

function getTree(dir){
	document.getElementById('treeelements').innerHTML = "";
	document.getElementById('basetree').innerHTML = "";
	startLoad();
	startLoadFiles();
	loadXML('/admin/page.php?page=fm2&do=editgettree&nosdir=true&dir='+dir, 'tree');
	loadXML('/admin/page.php?page=fm2&do=editgetDirFiles&nosdir=true&dir='+dir, 'files');
}

function Delete(){
	document.getElementById('down_dir3').value = document.getElementById('info_all').DIR;
	document.forms.editform3.elements['do'].value = 'Delete';
	document.forms.editform3.submit();
}

window.onresize = function(){
	setSizes();
}

function setSizes(){
	height = document.body.offsetHeight;
	width = document.body.offsetWidth;
	if (height<200 || width<300){
		//top.window.resizeTo(600,500);
		height = 500;
		width = 600;
	}
	document.getElementById("treeelements").style.height = (height-123)+'px';
	document.getElementById("load").style.height = (height-23)+'px';
	document.getElementById('loadfile').style.height = (height-153)+'px';
	document.getElementById('loadfile').style.width = (width-253)+'px';
	
	document.getElementById('file_list').style.height = (height-152)+'px';
	document.getElementById('div_cur_id').style.width = (width-120)+'px';
	document.getElementById('current_dir').style.width = (width-120)+'px';
	document.getElementById('mainload').style.height=document.body.offsetHeight+'px';
	document.getElementById('mainload').style.width=document.body.offsetWidth+'px';
	document.getElementById('changenameForm').style.height=document.body.offsetHeight/2+'px';
	document.getElementById('changenameForm').style.width=document.body.offsetWidth/2+'px';
	document.getElementById('changenameForm').style.top=document.body.offsetHeight/4+'px';
	document.getElementById('changenameForm').style.left=document.body.offsetWidth/4+'px';	
	document.getElementById('watermarkForm').style.height=document.body.offsetHeight/2+'px';
	document.getElementById('watermarkForm').style.width=document.body.offsetWidth/2+'px';
	document.getElementById('watermarkForm').style.top=document.body.offsetHeight/4+'px';
	document.getElementById('watermarkForm').style.left=document.body.offsetWidth/4+'px';
	document.getElementById('downloadForm').style.height=document.body.offsetHeight/2+'px';
	document.getElementById('downloadForm').style.width=document.body.offsetWidth/2+'px';
	document.getElementById('downloadForm').style.top=document.body.offsetHeight/4+'px';
	document.getElementById('downloadForm').style.left=document.body.offsetWidth/4+'px';
	document.getElementById('createForm').style.height=document.body.offsetHeight/2+'px';
	document.getElementById('createForm').style.width=document.body.offsetWidth/2+'px';
	document.getElementById('createForm').style.top=document.body.offsetHeight/4+'px';
	document.getElementById('createForm').style.left=document.body.offsetWidth/4+'px';
	//document.getElementById('input_file').style.width=(document.body.offsetWidth/2-50)+'px';
	
}

function changePerm(who, value){
	div = document.getElementById('info_all');
	//document.write('/admin/page.php?page=fm2&do=changePerm&dir='+div.DIR+'&file='+div.FILE+'&who='+who+'&value='+value);
	loadXML('/admin/page.php?page=fm2&do=editchangePerm&dir='+div.DIR+'&file='+div.FILE+'&who='+who+'&value='+value, 'perm');
}
 
function getNode(dir){
	startLoad();
	//document.write('/admin/page.php?page=fm2&do=getNode&dir='+dir);
	loadXML('/admin/page.php?page=fm2&do=editgetNode&dir='+dir, 'node');
	this.watermark = 0;
}
function getFolderInfo(dir){
	document.getElementById('info_all').FILE = '';
	//document.write('/admin/page.php?page=fm2&do=getFolderInfo&dir='+dir);
	loadXML('/admin/page.php?page=fm2&do=editgetFolderInfo&dir='+dir, 'folderinfo');
}

function openDir(dir){
	//alert(dir);
	startLoadFiles();
	loadXML('/admin/page.php?page=fm2&do=editgetDirFiles&dir='+dir, 'files');
}
function getFileInfo(file){
	dir = document.getElementById('info_all').DIR;
	document.getElementById('info_all').FILE = file;
	//document.write('/admin/page.php?page=fm2&do=getFileInfo&dir='+dir+'&file='+file);
	loadXML('/admin/page.php?page=fm2&do=editgetFileInfo&dir='+dir+'&file='+file, 'fileinfo');
}
									
function startLoad(){
    setOpacity(document.getElementById('treeelements'), 0.25, false);
	document.getElementById('load').style.visibility = "visible";
}
function stopLoad(){
    setOpacity(document.getElementById('treeelements'), 1, false);
	document.getElementById('load').style.visibility = "hidden";
}
function startMainLoad(){
	document.getElementById('mainload').style.visibility = "visible";
}
function stopMainLoad(){
	document.getElementById('mainload').style.visibility = "hidden";
}
function startLoadFiles(){
    setOpacity(document.getElementById('file_list'), 0.05, false);
	document.getElementById('loadfile').style.visibility = "visible";
}
function stopLoadFiles(){
    setOpacity(document.getElementById('file_list'), 1, false);
	document.getElementById('loadfile').style.visibility = "hidden";
}
var times = new Array();

function loadXML(query, type) {

	var del = query.indexOf("?");
	var url = query.substring(0, del);
	var pars = query.substring(del+1);
	
	if (type == 'tree')			{ var func = showTree;}
	if (type == 'node')			{ var func = showNode;}
	if (type == 'files')		{ var func = showFiles;}
	if (type == 'folderinfo')	{ var func = showInfo;}
	if (type == 'perm')			{ var func = changePermXML;}
	if (type == 'fileinfo')		{ var func = showInfo;}
		
	var myAjax = new Ajax.Request(
			url, 
			{
				method: 'post', 
				parameters: pars, 
				onComplete: func
			});
}

function showTree(originalRequest){
	var data = eval("(" + originalRequest.responseText + ")");
	insertBaseTree(data['basetree']);
	var ar = new Array();
	insertTree(data['tree'], ar);

}

function showInfo(originalRequest){
	var data = eval("(" + originalRequest.responseText + ")");
	insertInfo(data['info']);		
}

function changePermXML(originalRequest){
	var data = eval("(" + originalRequest.responseText + ")");
	if (data['error']){
		alert("У вас нет прав для изменения. \nФайл был закачен через FTP доступ.");
	}
	setPermision('', perm);		
}

function Prewiev(img, w, h){
		
	img_pr = document.getElementById('img_prewiev');
	img_ = document.getElementById('img_prew');
	img_pr.style.top = "40px";
	img_pr.style.left = "225px";
	
	
	bh = document.body.offsetHeight/2;
	bw = document.body.offsetWidth/2;

	if (h*bw/w>bh){
		img_.height = bh;
		img_.width = bh*w/h;
	} else { 
		img_.width = bw;
		img_.height = bw*h/w;
	}	
	if (img_.height>60) {img_pr.style.height = img_.height -50+'px';} else {img_pr.style.height = img_.height+'px';}
	if (img_.width>60) {img_pr.style.width = img_.width -50+'px';} else {img_pr.style.width = img_.width +'px';}
	if (this.watermark != 0){
		img_.src = img.src+'?wm=true&'+this.watermark_rang;
	} else {
		img_.src = img.src;
	}
	img_pr.style.visibility = "visible";

	this.element = img_pr;
	if (this.direction != 1){
		this.direction = 1;
		changeAlpha();
	}
}
function closePrewiev(){
	img_pr = document.getElementById('img_prewiev');
	this.element = img_pr;
	if (this.direction != -1){
		this.direction = -1;
		changeAlpha(function(){ document.getElementById('img_prew').src = "/admin/images/third/uploading.gif";});
	}
}

// Установка прозрачности
function setOpacity(elem, nOpacity, add) {
	if (isIE) {
	    try {
    		nOpacity *= 100;	
    	    var oAlpha = elem.filters['DXImageTransform.Microsoft.alpha'] || elem.filters.alpha;
    	    if (add) {
    		  if (oAlpha) oAlpha.opacity += nOpacity;
    		  else elem.style.filter += "progid:DXImageTransform.Microsoft.Alpha(opacity="+nOpacity+")";
    	    } else {
    		  if (oAlpha) oAlpha.opacity = nOpacity;
    		  else elem.style.filter += "progid:DXImageTransform.Microsoft.Alpha(opacity="+nOpacity+")";
    	    }
	    } catch (e) {}
	} else {
		try {
		    if (add) {
    			elem.style.opacity += nOpacity;
    			elem.style.MozOpacity += nOpacity;
    			elem.style.KhtmlOpacity += nOpacity;
		    } else {
    			elem.style.opacity = nOpacity;
    			elem.style.MozOpacity = nOpacity;
    			elem.style.KhtmlOpacity = nOpacity;
		    }
		} catch (e) {}
	}
}

function getOpacity(elem){
	if (isIE) {
	    var oAlpha = elem.filters['DXImageTransform.Microsoft.alpha'] || elem.filters.alpha;
	    if (oAlpha) {
	        return oAlpha.opacity/100;
	    } else {
	        //elem.style.filter += "progid:DXImageTransform.Microsoft.Alpha(opacity="+nOpacity+")";
	    }
	} else {
		try {
   			return elem.style.opacity;
		} catch (e) {}
		try {
   			return elem.style.MozOpacity;
		} catch (e) {}
		try {
   			return elem.style.KhtmlOpacity;
		} catch (e) {}
	}
}

function changeAlpha(func){
	if (this.direction<0){
		window.clearTimeout();
		setOpacity(this.element, 1, false);
		showAlpha(-15, func);
	} else if (this.direction >0){
		window.clearTimeout();
		setOpacity(this.element, 0, false);
		showAlpha(15, func);
	}
}

function showAlpha(direction, func){
    setOpacity(this.element, direction, true);
	if (getOpacity(this.element) <= 0 || getOpacity(this.element)>=1){
		if (direction<0){
			this.element.style.visibility = "hidden";
			setOpacity(this.element, 0, false);
			if (func) func();
		} else {
			setOpacity(this.element, 1, false);
		}
	} else{
		window.setTimeout('showAlpha('+direction+')',1);
	}
	
}

function insertInfo(data){
	img_div = document.getElementById('info_img');
	if (data['imgsize'] == undefined && data['file'] == undefined){
		img_div.innerHTML = "<img src=\"/admin/images/third/folder_50.gif\" width=\"60px\">";
	}
	if (data['imgsize'] == undefined && data['file'] != undefined){
		img_div.innerHTML = "<img src=\"/admin/images/third/system.gif\" width=\"60px\">";
	}
	if (data['imgsize'] != undefined && data['file'] != undefined){
		if (data['imgsize']['a1']*63/data['imgsize']['a0']>'80'){
			size = "height=\"80px\"";
		} else { 
			if (data['imgsize']['a0']<63){
				size = "width=\""+data['imgsize']['a0']+"px\"";
			} else {
				size = "width=\"63px\"";
			}
		}
		img_div.innerHTML = "<img src=\""+data['dir']+data['file']+"\" "+size+" id=\"img_iden\">";
		//alert(document.getElementById('img_iden').src);
		document.getElementById('img_iden').onmouseover = function(){
			Prewiev(this, data['imgsize']['a0'], data['imgsize']['a1']);
		}
	}

	div = document.getElementById('info_all');
	div.DIR = data['dir'];
	if (div.childNodes.length!=0){
	    myRemoveNode(div.childNodes[0], true);
	}
	var table = document.createElement('table');
	table.cellPadding = "1";
	table.cellSpacing = "1";
	table.style.backgroundColor = "#000000";
	table.id = "info_";
	
	tr = table.insertRow(0);
	tr.vAlign="top";
	td1 = tr.insertCell(-1);
	td1.style.textAlign = "right";
	td1.style.fontWeight = "bold";
	td1.style.backgroundColor = "#ffffff";
	td1.innerHTML = "дата&nbsp;создания:";
	td2 = tr.insertCell(-1);
	td2.style.paddingLeft = "5px";
	td2.style.backgroundColor = "#ffffff";
	td2.innerHTML = data['filectime'];	
	td1.style.fontSize="10px";
	td2.style.fontSize="10px";

	tr = table.insertRow(0);
	tr.vAlign="top";
	td1 = tr.insertCell(-1);
	td1.style.textAlign = "right";
	td1.style.fontWeight = "bold";
	td1.style.backgroundColor = "#ffffff";
	td1.innerHTML = "дата&nbsp;изменения:";
	td2 = tr.insertCell(-1);
	td2.style.paddingLeft = "5px";
	td2.style.backgroundColor = "#ffffff";
	td2.innerHTML = data['fileatime'];	
	td1.style.fontSize="10px";
	td2.style.fontSize="10px";
	
	if (data['imgsize'] != undefined){
		tr = table.insertRow(0);
		tr.vAlign="top";
		td1 = tr.insertCell(-1);
		td1.style.textAlign = "right";
		td1.style.fontWeight = "bold";
		td1.style.backgroundColor = "#ffffff";
		td1.innerHTML = "размеры:";
		td2 = tr.insertCell(-1);
		td2.style.paddingLeft = "5px";
		td2.style.backgroundColor = "#ffffff";
		td2.innerHTML = data['imgsize']['a0']+'x'+data['imgsize']['a1']+' px';
		td1.style.fontSize="10px";
		td2.style.fontSize="10px";
		
		tr = table.insertRow(0);
		tr.vAlign="top";
		td1 = tr.insertCell(-1);
		td1.style.textAlign = "right";
		td1.style.fontWeight = "bold";
		td1.style.backgroundColor = "#ffffff";
		td1.innerHTML = "глубина цвета:";
		td2 = tr.insertCell(-1);
		td2.style.paddingLeft = "5px";
		td2.style.backgroundColor = "#ffffff";
		td2.innerHTML = data['imgsize']['bits']+' bits';
		td1.style.fontSize="10px";
		td2.style.fontSize="10px";
	}
	
	if (data['size'] != undefined){
		tr = table.insertRow(0);
		tr.vAlign="top";
		td1 = tr.insertCell(-1);
		td1.style.textAlign = "right";
		td1.style.fontWeight = "bold";
		td1.style.backgroundColor = "#ffffff";
		td1.innerHTML = "размер:";
		td2 = tr.insertCell(-1);
		td2.style.paddingLeft = "5px";
		td2.style.backgroundColor = "#ffffff";
		td2.innerHTML = data['size'];
		td1.style.fontSize="10px";
		td2.style.fontSize="10px";
	}
	
	
	tr = table.insertRow(0);
	tr.vAlign="top";
	td1 = tr.insertCell(-1);
	td1.style.textAlign = "right";
	td1.style.fontWeight = "bold";
	td1.style.backgroundColor = "#ffffff";
	td1.innerHTML = "название:";
	td2 = tr.insertCell(-1);
	td2.style.paddingLeft = "5px";
	td2.style.backgroundColor = "#ffffff";
	if (data['current_dir'] != undefined){
		td2.innerHTML = data['current_dir'];	
	} else {
		td2.innerHTML = data['file'];
	}
	td1.style.fontSize="10px";
	td2.style.fontSize="10px";
	
	div.appendChild(table);
	
	setPermision(data['dir'], data['permission']);
}

function setPermision(dir, perm){
	for (var i in perm){
		if (i=='toJSONString') continue;
		if (i!='type'){
			for (var j in perm[i]){
				if (j=='toJSONString') continue;
				if (perm[i][j] == 1){
					document.getElementById(i+j).checked = true;
				} else {
					document.getElementById(i+j).checked = false;
				}
			}
		}
	}
}

function showNode(originalRequest){
	var data = eval("(" + originalRequest.responseText + ")");
	document.getElementById('current_dir').value = data['current_dir'];
	if (data['node'] == undefined){
		alert("Ошибка загрузки!");
	}
	insertNode(data["node"]);
}

function showFiles(originalRequest){
	var data = eval("(" + originalRequest.responseText + ")");
	document.getElementById('current_dir').value = data['current_dir'];
	if (data['files'] == undefined){
		alert("Ошибка загрузки!");
	}
	insertFiles(data['files']);
	
	this.watermark = 0;	
}

var extensions = new Array();
	extensions['ipx'] = 'ipx.gif';
	extensions['jpeg'] = 'jpeg.gif';
	extensions['jpg'] = 'jpg.gif';
	extensions['mp3'] = 'mp3.gif';
	extensions['pdf'] = 'pdf.gif';
	extensions['png'] = 'png.gif';
	extensions['psd'] = 'psd.gif';
	extensions['rar'] = 'rar.gif';
	extensions['swf'] = 'swf.gif';
	extensions['txt'] = 'txt.gif';
	extensions['xls'] = 'xls.gif';
	extensions['zip'] = 'zip.gif';
	extensions['php'] = 'icon.php.gif';
	extensions['php3'] = 'icon.php.gif';
	extensions['php4'] = 'icon.php.gif';
	extensions['avi'] = 'avi.gif';
	extensions['doc'] = 'doc.gif';
	extensions['exe'] = 'exe.gif';
	extensions['gif'] = 'gif.gif';
	extensions['htm'] = 'htm.gif';
	extensions['html'] = 'htm.gif';
	extensions['tpl'] = 'htm.gif';
	extensions['tmpl'] = 'htm.gif';
	extensions['xxx'] = 'xxx.gif';
	extensions['docx'] = 'docx.gif';
	extensions['xlsx'] = 'xlsx.gif';
	
function getExt(ext){
	ext = ext.toLowerCase();
	if (this.extensions[ext] != null){
		return "/admin/images/icons/"+this.extensions[ext];
	} else {
		return "/admin/images/icons/"+this.extensions['xxx'];
	}
}

function myRemoveNode(node, removeChildren) {
    if (node.removeNode) node.removeNode(true);
    else {
        if (Boolean(removeChildren))
            return node.parentNode.removeChild(node);
        else {
            var r = document.createRange();
            r.selectNodeContents(node);
            return node.parentNode.replaceChild(r.extractContents(),node);
        }
    }
}

function insertFiles(rows){
	div = document.getElementById('file_list');
	table = isIE ? div.childNodes[0] : div.childNodes[1];

	//очищаем таблицу
	len = table.rows.length;
	for (var i=1;i<len;i++){
	    if (table.rows[i]) {
	        myRemoveNode(table.rows[i], true);
	        i=i-1;
	    }
	}

	flag = 0;
	color = "#FFFFFF";
	for (var i=0; i<rows.length; i++){
		
		if (color == "#EEEDED"){
			color = "#FFFFFF";
		} else {
			color = "#EEEDED";
		}
		
		flag = 1;
		tr = table.insertRow(table.rows.length);
		tr.vAlign ="top";
		tr.OPENFILE = rows[i]['name'];
		tr.onclick = function(){
			if (this.cells[0].childNodes[0].checked == false){
				this.cells[0].childNodes[0].checked = true;
				getFileInfo(this.OPENFILE);
			} else {
				this.cells[0].childNodes[0].checked = false;
				getFileInfo(this.OPENFILE);
			}
		}
		tr.onmouseover = function(){
			this.COLOR = this.style.backgroundColor
			this.style.backgroundColor = "#DAECFF";
		}
		tr.onmouseout = function(){
			this.style.backgroundColor = this.COLOR;
		}
		tr.style.backgroundColor = color;
		tr.style.cursor = "pointer";
		td = tr.insertCell(-1);
		//td1.style.backgroundColor = "#ffffff";
		td.style.textAlign = "center";
		td.innerHTML = "<input type=\"checkbox\" name='ids[]' value=\""+rows[i]['id']+"\" onclick=\"if (this.checked == true){this.checked=false;}else{this.checked=true;}\">";

			
		td1 = tr.insertCell(-1);
		//td1.style.backgroundColor = "#ffffff";
		td1.style.textAlign = "center";
		if (!rows[i]['ext']) rows[i]['ext'] = 'xxx';
		td1.innerHTML = "<img src=\""+getExt(rows[i]['ext'])+"\">";
		
		td2 = tr.insertCell(-1);
		//td2.style.backgroundColor = "#ffffff";
		td2.style.paddingLeft = "5px";
		td2.innerHTML = rows[i]['name'];
		
		td3 = tr.insertCell(-1);
		//td3.style.backgroundColor = "#ffffff";
		td3.style.paddingLeft = "5px";
		td3.style.textAlign = "right";
		td3.innerHTML = rows[i]['size'];

	}
	if (flag==0){
		tr = table.insertRow(table.rows.length);
		tr.vAlign ="top";
		td1 = tr.insertCell(-1);
		td1.colSpan = '4';
		td1.style.backgroundColor = "#EEEDED";
		td1.style.textAlign = "center";
		td1.style.height = "50px";
		//td1.style.vAlign = "middle";
		td1.innerHTML = "<br>Пустая директория";
	}
	stopLoadFiles();
}

//закрывает папку() кнопка минус
function closeNode(table_id){
	startLoad();
	table = document.getElementById(table_id);
	len = table.rows[0].cells.length;
	
	openDir(table.rows[0].cells[len-3].DIR);
	getFolderInfo(table.rows[0].cells[len-3].DIR);

	
	var div = document.getElementById('treeelements');
	count = div.childNodes.length;
	//alert(len);
	for (i=0;i<count;i++){
		cid = div.childNodes[i].id;
		//alert(cid);
		if (cid == table_id){
			for (j=i+1;j<count;j++){
				try{
					attach = div.childNodes[j];
					if (attach.rows[0].cells.length>len){
						div.removeChild(attach);
						j--;
					} else {
						break;
					}
				} catch(e) {break; }
			}
			break;
		}
	}
	cell = table.rows[0].cells[table.rows[0].cells.length-3];
	if (table.LAST == 1){
		cell.innerHTML = "<img src=\"/admin/images/tree/toc_closed_last.gif\">";
		cell.onclick = function(){
            getNode(this.DIR);
		};
	} else {
		cell.innerHTML = "<img src=\"/admin/images/tree/toc_closed_whole.gif\">";
		cell.onclick = function(){
            getNode(this.DIR);
		};
	}
	stopLoad();
}


function insertNode(node){
	for (i in node){
		if (i == 'toJSONString') continue;
		var base = i;
		var count = document.getElementById(base).rows[0].cells.length-3;
		openDir(document.getElementById(base).rows[0].cells[count].DIR);
		getFolderInfo(document.getElementById(base).rows[0].cells[count].DIR);
		var temp = node[base];
		for (var j=0; j<temp.length; j++){
			//alert(node[base][j]['name']);
			el = node[base][j];
			var table = document.createElement('table');
			table.cellPadding = "0";
			table.cellSpacing = "0";
			table.id = "folder_"+el['dir_id'];
			tr = table.insertRow(0);
			
			last = "whole";
			if (el['is_last'] == 1){
				last = "last";
			}
			if (el['next'] == 1){
				if (el['attach'] != null){
					type = "opened";
				} else {
					type = "closed";
				}
			} else {
				type = "leaf";
			}
			
			for (m=0; m< count;m++){
				td = tr.insertCell(-1);
				td.innerHTML = document.getElementById(base).rows[0].cells[m].innerHTML;
			} 
			td = tr.insertCell(-1);
			if (document.getElementById(base).rows[0].cells[count].LAST == 0){
				td.innerHTML = "<img src=\"/admin/images/tree/toc_line.gif\">";
				document.getElementById(base).rows[0].cells[count].innerHTML = "<img src=\"/admin/images/tree/toc_opened_whole.gif\">";
				document.getElementById(base).rows[0].cells[count].onclick = function(){
					closeNode(document.getElementById(base).id);
				};
			} else {
				td.innerHTML = "<img src=\"/admin/images/s.gif\" width='16'>";
				document.getElementById(base).rows[0].cells[count].innerHTML = "<img src=\"/admin/images/tree/toc_opened_last.gif\">";
				document.getElementById(base).rows[0].cells[count].onclick = function(){
					closeNode(document.getElementById(base).id);
				};
			}
			
			td1 = tr.insertCell(-1);
			td1.LAST = el['is_last'];
			td1.NEXT = el['next'];
			td1.style.cursor = "pointer";
			td1.DIR = el['dir']+"&level="+el['level']+"&name=folder_"+el['dir_id'];
			td1.innerHTML = "<img src=\"/admin/images/tree/toc_"+type+"_"+last+".gif\">";
			if (type == "closed"){
				td1.onclick = function(){
					getNode(this.DIR);
				};
			}
			td2 = tr.insertCell(-1);
			td3 = tr.insertCell(-1);
			td2.innerHTML = "<img src=\"/admin/images/icons/folder.gif\">";
			td2.style.cursor = "pointer";
			td2.onclick = function(){
				openDir(this.parentNode.cells[this.parentNode.cells.length-3].DIR);
				getFolderInfo(this.parentNode.cells[this.parentNode.cells.length-3].DIR);
			}
			td3.style.paddingLeft = '5px';
			td3.style.whiteSpace = "nowrap";
			td3.style.cursor = "pointer";
			td3.innerHTML = el['name']; 
			td3.onclick = function(){
				openDir(this.parentNode.cells[this.parentNode.cells.length-3].DIR);
				getFolderInfo(this.parentNode.cells[this.parentNode.cells.length-3].DIR);
			}
			treeelements.insertBefore(table, document.getElementById(base));
			document.getElementById(base).swapNode(document.getElementById("folder_"+el['dir_id']));
		}
	}
	stopLoad();
}

if (!isIE)
Node.prototype.swapNode = function (node) {
  var nextSibling = this.nextSibling;
  var parentNode = this.parentNode;
  node.parentNode.replaceChild(this, node);
  parentNode.insertBefore(node, nextSibling);  
}


function insertTree(tree, ar){
	getFolderInfo(document.getElementById('baseDir').DIR);
	var treeelements = document.getElementById('treeelements');
	for (var i=0; i< tree.length; i++){
		var table = document.createElement('table');
		table.cellPadding = "0";
		table.cellSpacing = "0";
		table.id = "folder_"+tree[i]['dir_id'];
		tr = table.insertRow(0);

		ar[tree[i]['level']-1] = 0;
		
		last = "whole";
		if (tree[i]['is_last'] == 1){
			last = "last";
			ar[tree[i]['level']-1] = 1;
		}
		for (j=0; j< (tree[i]['level']-1);j++){
			td = tr.insertCell(-1);
			if (ar[j] == 0){
				td.innerHTML = "<img src=\"/admin/images/tree/toc_line.gif\">";
			} else {
				td.innerHTML = "<img src=\"/admin/images/s.gif\" width='16'>";
			}
		}
		
		if (tree[i]['next'] == 1){
			if (tree[i]['attach'] != null){
				type = "opened";
			} else {
				type = "closed";
			}
		} else {
			type = "leaf";
		}
		td1 = tr.insertCell(-1);
		td1.LAST = tree[i]['is_last'];
		td1.NEXT = tree[i]['next'];
		td1.DIR = tree[i]['dir']+"&level="+tree[i]['level']+"&name=folder_"+tree[i]['dir_id'];
		td1.innerHTML = "<img src=\"/admin/images/tree/toc_"+type+"_"+last+".gif\">";
		td1.style.cursor = "pointer";
		if (type == "closed"){
			td1.onclick = function(){
				getNode(this.DIR);
			};
		} else if (type == "opened"){
			td1.onclick = function(){
				closeNode(this.id);
			};
		}
		td2 = tr.insertCell(-1);
		td3 = tr.insertCell(-1);
		td2.innerHTML = "<img src=\"/admin/images/icons/folder.gif\">";
		td2.style.cursor = "pointer";
		td2.onclick = function(){
			openDir(this.parentNode.cells[this.parentNode.cells.length-3].DIR);
			getFolderInfo(this.parentNode.cells[this.parentNode.cells.length-3].DIR);
		}
		td3.style.paddingLeft = '5px';
		td3.innerHTML = tree[i]['name']; 
		td3.style.whiteSpace = "nowrap";
		td3.style.cursor = "pointer";
		td3.onclick = function(){
			openDir(this.parentNode.cells[this.parentNode.cells.length-3].DIR);
			getFolderInfo(this.parentNode.cells[this.parentNode.cells.length-3].DIR);
		}
		treeelements.appendChild(table);
		if (tree[i]['attach'] != null){
			insertTree(tree[i]['attach'], ar);
		}
	}
	stopLoad();
}

function insertBaseTree(base){
	var baseTree = document.getElementById('basetree');
	var table = document.createElement('table');
	table.cellPadding = "0";
	table.cellSpacing = "0";
	tr = table.insertRow(0);
	td1 = tr.insertCell(-1);
	td1.id = 'baseDir';
	td1.DIR = base['dir'];
	td1.innerHTML = "<img src=\"/admin/images/icons/folder.gif\">";
	td1.style.cursor = "pointer";
	td1.onclick = function(){
		openDir(base['dir']);
		getFolderInfo(base['dir']);
	}
	td2 = tr.insertCell(-1);
	td2.style.paddingLeft = '5px';
	td2.innerHTML = base['name'];
	td2.style.cursor = "pointer";
	td2.onclick = function(){
		openDir(base['dir']);
		getFolderInfo(base['dir']);
	}
	baseTree.appendChild(table);
	//alert(baseTree.outerHTML);
}



function XMLtoArray(response, ar){
	if(!response) return;
	if(!response.childNodes) return;

	var count = response.childNodes.length;

	for (var i=0; i<count; i++){
		var name = response.childNodes.item(i).nodeName;
		if (response.childNodes.item(i).childNodes.item(0)){
			var type = response.childNodes.item(i).childNodes.item(0).nodeType;
		} else {type = 2;}
		
		if (type == 1){
			ar[name] = new Array();
			XMLtoArray(response.childNodes.item(i), ar[name]);
		} else if (type == 3) {
			ar[name] = response.childNodes.item(i).childNodes.item(0).text;
		} else {
			ar[name] = '';
		}
	}
}
function showChangenameForm(){
	this.element = document.getElementById('changenameForm');
	this.element.style.visibility = "visible";
	
	if (document.getElementById('info_all').FILE){
		document.getElementById('file_name').value = document.getElementById('info_all').FILE;
	} else {
		document.getElementById('file_name').value = this.getFolder(document.getElementById('info_all').DIR);
	}

	if (this.direction != 1){
		this.direction = 1;
		changeAlpha();
	}
}
function showWatermarkForm(){
	this.element = document.getElementById('watermarkForm');
	this.element.style.visibility = "visible";
	
	if (this.direction != 1){
		this.direction = 1;
		changeAlpha();
	}
}
function getFolder(name){
	s = name.lastIndexOf("/", name.length-2);
	e = name.lastIndexOf("/");
	return name.substring(s + 1, e);
}

function hideChangenameForm(){
	this.element = document.getElementById('changenameForm');
	if (this.direction != -1){
		this.direction = -1;
		changeAlpha();
	}
}
function hideWatermarkForm(){
	this.element = document.getElementById('watermarkForm');
	if (this.direction != -1){
		this.direction = -1;
		changeAlpha();
	}
}
function showDownloadFrom(){
	this.element = document.getElementById('downloadForm');
	this.element.style.visibility = "visible";
	if (this.direction != 1){
		this.direction = 1;
		changeAlpha();
	}
}
function hideDownloadFrom(){
	this.element = document.getElementById('downloadForm');
	if (this.direction != -1){
		this.direction = -1;
		changeAlpha();
	}
}
function showCreateFrom(){
	this.element = document.getElementById('createForm');
	this.element.style.visibility = "visible";
	if (this.direction != 1){
		this.direction = 1;
		changeAlpha();
	}
}
function hideCreateFrom(){
	this.element = document.getElementById('createForm');
	if (this.direction != -1){
		this.direction = -1;
		changeAlpha();
	}
}

function verifyImage(filename){
	//alert(filename);
	dot = filename.lastIndexOf('.');
	ext = filename.substr(dot+1,filename.length-dot).toLowerCase();
	table = document.getElementById('down_table');
	if (table.rows.length>3){
		len = table.rows.length;
		for (i=2;i<len-1;i++){
		    myRemoveNode(table.rows[2], true);
		}
	}
	if (ext == 'jpg' || ext == 'jpeg' || ext == 'gif' || ext == 'png'){
		tr = table.insertRow(table.rows.length-1);
		td = tr.insertCell(-1);
		td.style.textAlign = "center";
		td.style.fontFamily = "Tahoma";
		//td.style.fontSize = "90%";
		td.style.padding = "0px 5px 0px 4px";
		td.innerHTML = "<b>уменьшение размеров</b>";		
		tr = table.insertRow(table.rows.length-1);
		td = tr.insertCell(-1);
		td.style.textAlign = "center";
		td.style.fontFamily = "Tahoma";
		//td.style.fontSize = "90%";
		td.style.padding = "0px 5px 0px 4px";
		td.innerHTML = "высота: <input type=\"text\" name=\"height\" value=\"0\" onkeyup=\"if (this.value>1024){this.value=1024;}\" style=\"text-align:right;width:40px;font-family: Tahoma;border: 1px solid #9C9C9C;font-size: 90%;padding: 0px 5px 0px 4px;\">&nbsp;px";	
		tr = table.insertRow(table.rows.length-1);
		td = tr.insertCell(-1);
		td.style.textAlign = "center";
		td.style.fontFamily = "Tahoma";
		//td.style.fontSize = "90%";
		td.style.padding = "0px 5px 0px 4px";
		td.innerHTML = "ширина: <input type=\"text\" name=\"width\" value=\"0\" onkeyup=\"if (this.value>1024){this.value=1024;}\" style=\"text-align:right;width:40px;font-family: Tahoma;border: 1px solid #9C9C9C;font-size: 90%;padding: 0px 5px 0px 4px;\">&nbsp;px";		
		if (this.iswatermark_working == 1){
			tr = table.insertRow(table.rows.length-1);
			td = tr.insertCell(-1);
			td.style.textAlign = "center";
			td.style.fontFamily = "Tahoma";
			//td.style.fontSize = "90%";
			td.style.padding = "0px 5px 0px 4px";
			td.innerHTML = "наложить водяной знак: <input type=\"checkbox\" name=\"watermark\" value=\"1\">";		
		}
	
	}
}

function verifyText(text){
	if (text.value.length>100){
		text.value = text.substr(0,100);
	}
}

function overloadWaterMark(){
	document.getElementById('down_dir3').value = document.getElementById('info_all').DIR;
	document.forms.editform3.elements['do'].value = 'editwaterMark';
	document.forms.editform3.elements['watermark'].value = document.forms.watermarkFormf.elements['file_name'].value;
	document.forms.editform3.submit();
	return false;
}