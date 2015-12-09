var loading_window;
var lw_timeout;
var _loaded;
function Loading(show) {
	if (loading_window) {
		loading_window.close();
		loading_window = false;
	}
	if (show == 1 && !_loaded) {
		loading_window = window.showModelessDialog('dialog.php?page=loading', '', 'dialogHeight: 100px; dialogWidth: 250px; edge: raised; center: yes; help: no; resizable: no; status: no; scroll: no;');
	}
}