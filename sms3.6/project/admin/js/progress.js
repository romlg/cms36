function getUniqueStr(len) {
	var i, rnd, str = '', symbols = 'abcdefghijklmnopqrstuvwxyz0123456789';
	for (i = 0; i < len; i++) {
		rnd = randRange(0, symbols.length - 1);
		str += symbols.substring(rnd, rnd + 1);
	}
	return str;
}
function randRange(min, max) {
  var randomNum = Math.round(Math.random() * (max - min)) + min;
  return randomNum;
}

function openLinkInProgress(ahref) {
	progressWindow = window.showModelessDialog('dialog.php?page=progress', ahref.href, 'dialogHeight: 50px; dialogWidth: 535px; edge: sunken; center: yes; help: no; resizable: no; status: no; scroll: no;');
	//progressWindow = window.open('dialog.php?page=progress');
	return false;
}

function progressInit(pr_id) {
	if (!pr_id) {
		pr_id = '';
	}
	var progressId = getUniqueStr(8);
	var obj = document.getElementById('progressBg' + pr_id);
	if (obj)	obj.id = 'progressBg_' + progressId;
	obj = document.getElementById('progressCur' + pr_id);
	if (obj)	obj.id = 'progressCur_' + progressId;
	obj = document.getElementById('progressIframe' + pr_id);
	if (obj)	obj.id = 'progressIframe_' + progressId;
	return progressId;
}

var ProgressInterval = new Array();
function progressAddPers(progressID, addPers) {
	var progressBg = document.getElementById('progressBg_' + progressID);
	var progressCur = document.getElementById('progressCur_' + progressID);
	var curPers = progressCur.clientWidth / (progressBg.clientWidth - 2) * 100;
	if (curPers >= 100) {
		window.clearInterval(ProgressInterval[progressID]);
		return;
	}
	var newPx = progressCur.clientWidth + (progressBg.clientWidth - 2) / 100 * addPers;
	if (newPx > progressBg.clientWidth - 2) {
		newPx = progressBg.clientWidth - 2;
	}
	if (newPx < 0) {
		newPx = 0;
	}
	curPers = newPx / (progressBg.clientWidth - 2) * 100;
	progressCur.style.width = newPx;
	progressCur.innerText = Math.round(curPers) + '%';
}
function progressSetPers(progressID, setPers, setText) {
	var progressBg = document.getElementById('progressBg_' + progressID);
	var progressCur = document.getElementById('progressCur_' + progressID);
	setPers = (setPers < 0) ? 0 : setPers;
	setPers = (setPers > 100) ? 100 : setPers;
	if (setPers >= 100 && ProgressInterval[progressID]) {
		window.clearInterval(ProgressInterval[progressID]);
	}
	var newPx = (progressBg.clientWidth - 2) / 100 * setPers;
	if (newPx > progressBg.clientWidth - 2) {
		newPx = progressBg.clientWidth - 2;
	}
	progressCur.style.width = newPx;
	if (!setText) {
		setText = Math.round(setPers) + '%';
	}
	progressCur.innerText = setText;
}
function queryProgress(progressID, queryUrl) {
	var req = new JSHttpRequest();
	// Код, АВТОМАТИЧЕСКИ вызываемый при окончании загрузки.
	req.onreadystatechange = function () {
		if (req.readyState == 4) {
			if (req.responseJS) {
				progressSetPers(progressID, req.responseJS.percent, req.responseJS.text);
			}
			// Отладочная информация.
			if (req.responseText) {
				alert(req.responseText);
			}
		}
	}
	req.caching = false;
	req.open('GET', queryUrl, true);
	req.send({ progressID: progressID});
}
function progressRun(progressID, processUrl, queryUrl) {
	ProgressInterval[progressID] = window.setInterval('queryProgress("' + progressID + '", "' + queryUrl + '")', 500);
	//ProgressInterval[progressID] = window.setInterval('progressAddPers("' + progressID + '", 0.5)', 5);
	var obj = document.getElementById('progressIframe_' + progressID);
	if (obj) obj.src = processUrl + '&progressID=' + progressID;
}
