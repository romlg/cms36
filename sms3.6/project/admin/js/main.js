// min width ie6
function minWidth() {
	var winWidth = window.innerWidth || (document.documentElement && document.documentElement.clientWidth) || document.body.clientWidth;
	
	if (winWidth < 1000) {
		$('div.wrap, div.footer').css('width', '980px');
	}
	else {
		$('div.wrap, div.footer').css('width', 'auto');
	}
}

// file input
function fileInput() {
	var file = $('div.elemBox.file input[type="file"]');
	var inputText = file.parent().next().children('input[type="text"]');
	
	file.change(function () {
		var fileVal = $(this).val();
		inputText.attr('value', fileVal);
	});
}

// input hint
function inputHint() {
	var $input = $('.test input[type="text"]');
	
	$(window).load(function () {
    	$input.attr('value', 'введите фразу...')
    });
	
	var $inputVal = $input.val();
	
	$input.focus(function () {
		if ( $(this).val() == 'введите фразу...' ) {
			$(this).attr('value', '');
		}
		$(this).removeClass('blur');
	});
	
	$input.blur(function () {
		if ( $(this).val() == '' ) {
			$(this).attr('value', 'введите фразу...');
			$(this).addClass('blur');
		}
	});
}

// добавление класса каждому второму елементу (можно любому элементу)
function secondClass() {
	$('.test li:nth-child(2n)').addClass('testClass');
}

// add hover class
function hover() {
	$('.testBlock').hover(
		function () {
			$(this).addClass('hover');
		},
		function () {
			$(this).removeClass('hover');
		}
	);
}

// add last class
function lastClass() {
	$('.test li').last().addClass('last');
}

// add first class
function firstClass() {
	$('.test li').first().addClass('first');
}


// т.к. последние версии Opera и Chrome не поддерживают showModalDialog, то сделана замена window.open + postMessage
// это единая функция приёма postMessage от всех popup окон на сайте
// сообщениебудет приходить вида sendObject = {title: 'Тестовое сообщение', value: 5000};
// и реакция на действие будет осуществляться в функции ниже в зависимости от принятых данныхы
function receiveMessage(event) {
    // доверяем сообщениям только с нашего домена
    if (event.origin !== window.location.protocol+"//"+window.location.hostname)
        return;

    var data = event.data;
    if (data) {
        var str = 'Пришли неверные данные';
//            if (data.title && data.value) {
//                str = 'Сообщение:' + data.title + '. Значение объекта:' + data.value;
//                $('#message').text(str);
//            }

        // это запрос на подтверждение удаления элементов на списочной странице
        if (data.title=='deleteItems' && data.value) {
            var par = data.value.split("#");
            deleteItemsConfirm(par[0], par[1]);
        }
    }
}

window.addEventListener("message", receiveMessage, false);