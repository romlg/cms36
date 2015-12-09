var autosuggestClass = function (field, submitType, isMultiplySuggestionsOn, symbol, dirs, callback) {

    field.attr('autocomplete', 'off');

    if (isMultiplySuggestionsOn && !symbol) {
        this.isMultiplySuggestionsOn = isMultiplySuggestionsOn;
        this.symbol = [','];
    }
    else if (!isMultiplySuggestionsOn) {
        this.isMultiplySuggestionsOn = false;
        this.symbol = '';
    }
    else if (isMultiplySuggestionsOn && symbol) {
        this.isMultiplySuggestionsOn = true;
        this.symbol = symbol;
    }
    if (!submitType) {
        submitType = 'search';
    }

    if (callback) this.callback = callback;

    var _this = this;
    this.position = 0;
    this.currValues = [];
    this.currIds = [];

    this.lastValue = '';
    this.newValue = '';

    this.params = {};

    this.ajaxUrl = dirs;

    // Используемые в форме classNames
    this.classNames = {
        holder:'js-autosuggest-holder', //родитель поля ввода и поля вывода результатов поиска
        field:'js-autosuggest-field', //поле ввода
        output:'js-autosuggest-output'    //родитель полей вывода результатов
    };

    // Имена xml нодов и соотвествующие им имена функций-обработчиков
    this.xmlResponse = {
        nodata:{
            node:'nodata',
            handler:null
        },

        error:{
            node:'error',
            handler:'xmlResponseHandler_error'
        },

        item:{
            node:'item',
            handler:'xmlResponseHandler_output'
        }
    };


    // Выводимый html
    this.htmlResponse = {
        item:'div',
        value:'em'
    };

    if (isOpera || window.webkit) {
        field.keypress(function (ev) {
            if (_this.isActionKey(ev)) return;
            var targ = null;
            if (ev.target) targ = ev.target;
            else if (ev.srcElement) targ = ev.srcElement;
            if (targ.nodeType == 3) // defeat Safari bug
                targ = targ.parentNode;
            setTimeout(function () {
                _this.onChange(targ, submitType);
            }, 30);
        });
    }

    field.keydown(function (ev) {
        ev = ev || window.event;
        if (_this.isActionKey(ev)) {
            _this.onKeyUp(ev);
        }
    });

    field.keyup(function (ev) {
        ev = ev || window.event;
        if (_this.isActionKey(ev)) return;
        if (!isOpera && !window.webkit) {
            var targ = null;
            if (ev.target) targ = ev.target;
            else if (ev.srcElement) targ = ev.srcElement;
            if (targ.nodeType == 3) // defeat Safari bug
                targ = targ.parentNode;
            setTimeout(function () {
                _this.onChange(targ, submitType);
            }, 30);
        }
    });
};

// Переводим xml дерево в объект
autosuggestClass.prototype.parseXML = function (xmlObj) {
    var xmlNodes = {};

    for (prop in this.xmlResponse) {
        xmlNodes[prop] = xmlObj.getElementsByTagName(this.xmlResponse[prop].node);
    }
    return xmlNodes;
};

autosuggestClass.prototype.onKeyUp = function (ev) {
    ev = ev || window.event;
    return this.action(ev);
};

autosuggestClass.prototype.onChange = function (field, submitType) {
    this.processQuotes(field);
    this.requestSuggests(field, submitType);
};

autosuggestClass.prototype.isActionKey = function (ev) {
    switch (getCharCode(ev)) {
        case 13: // Enter
        case 27: // Escape
        case 40: // Down
        case 38: // Up
            return true;
            break;
    }
}

autosuggestClass.prototype.action = function (ev) {
    switch (getCharCode(ev)) {
        case 13:    //если нажата клавиша Enter
            if (!jQuery(this.output).is('.hidden')) {
                if (this.setValue()) {
                    if (!document.all)
                        ev.preventDefault();
                    else
                        ev.returnValue = false;
                }
            }
            return true;
            break;
        case 27: // Escape
            if (!jQuery(this.output).is('.hidden')) {
                jQuery(this.output).addClass('hidden');
            }
            return true;
            break;
        case 40:    //если нажата клавиша "Вниз"
            this.makeSteps('down');
            return true;
            break;
        case 38:    //если нажата клавиша "Вверх"
            this.makeSteps('up');
            return true;
            break;
    }
    return false;
}

autosuggestClass.prototype.processQuotes = function (field) {
    this.isQuoteOpened = false;
    this.isQuoteClosedPreviously = false;
    this.noIndexedString = '';
    if (field.value.indexOf('"') != -1) {
        var quotesResults = field.value.match(/"/g);
    }
    if (quotesResults && quotesResults.length % 2 == 0) {
        this.isQuoteOpened = false;
        if (field.value.match(/"$|"\n|"\r\n/)) {
            this.isQuoteClosedPreviously = true;
        }
        else {
            this.isQuoteClosedPreviously = false;
        }
    }
    else if (quotesResults && quotesResults.length % 2 != 0) {
        this.isQuoteOpened = true;
    }

    var words_quotes, words, words_length, k;
    if (this.isMultiplySuggestionsOn == true) { //если включена опция множественных подсказок
        if (this.isQuoteOpened == true || this.isQuoteClosedPreviously == true) { //если открыта кавычка
            words_quotes = field.value.split('"');
            this.isQuoteClosedPreviously == true ? this.newValue = words_quotes[words_quotes.length - 2] : this.newValue = words_quotes[words_quotes.length - 1]
            this.newValue = this.newValue.replace(/(^\s+)|(\n)/g, '');
            words = field.value.split(this.symbol);
            words_length = words.length - 1;
            for (k = 0; k < words_length; k++) {
                if (k == 0) {
                    this.noIndexedString += words[k];
                }
                else {
                    this.noIndexedString += this.symbol + words[k];
                }
            }
        } else {
            words = field.value.split(this.symbol);
            this.newValue = words[words.length - 1].replace(/(^\s+)|(\n)/g, '');
            words_length = words.length - 1;
            for (k = 0; k < words_length; k++) {
                if (k == 0) {
                    this.noIndexedString += words[k];
                }
                else {
                    this.noIndexedString += this.symbol + words[k];
                }
            }
        }
    }
    else {
        if (this.isQuoteOpened == true) {
            words_quotes = field.value.split('"');
            this.newValue = words_quotes[words_quotes.length - 1].replace(/(^\s+)|(\n)/g, '');
        }
        else {
            this.newValue = field.value;
        }
    }
}

autosuggestClass.prototype.requestSuggests = function (field, submitType) {
    this.holder = this.holder || getParentByClassName(field, this.classNames.holder);
    this.output = this.output || getElementsByClassName(this.holder, '*', this.classNames.output)[0];
    if (this.newValue == '') {
        jQuery(this.output).addClass('hidden');
        this.output.innerHTML = '';
        this.lastValue = this.newValue;
        return false;
    }
    else {
        jQuery(this.output).removeClass('hidden');
    }
    if (jQuery(this.holder).is("." + this.classNames.isLoading) || this.lastValue == this.newValue) {
        return false;
    }
    var data = 'letters=' + encodeURIComponent(this.newValue) + '&type=' + submitType;
    this.params = {
        holder:this.holder,
        field:field,
        string:this.newValue,
        output:this.output,
        noIndexedString:this.noIndexedString
    };
//    ajaxLoadPost(this.ajaxUrl, data, this.sendDataOnload, this, this.params);
    ajaxGet(this.ajaxUrl + "&" + data, this.sendDataOnload, this.params, this);
    //jQuery(this.holder).addClass(this.classNames.isLoading);
    this.lastValue = this.newValue;
}

// Чтение ответа сервера на пересылку  всех данных формы и обработка выданных ошибок
autosuggestClass.prototype.sendDataOnload = function (ajaxObj, params) {
    //jQuery(params.holder).removeClass(this.classNames.isLoading);
    if (ajaxObj && ajaxObj.responseXML) {
        var xmlObj = ajaxObj.responseXML;
        var xmlNodes = this.parseXML(xmlObj);

        for (prop in xmlNodes) {
            if (this[this.xmlResponse[prop].handler]) {
                this[this.xmlResponse[prop].handler](xmlNodes, params);
            }
        }
    }
};

// Вывод данных
autosuggestClass.prototype.xmlResponseHandler_output = function (xmlNodes, params) {
    var _this = this;
    //jQuery(params.output).removeClass('hidden');
    this.position = 0;
    this.params.output.innerHTML = '';
    if (xmlNodes.item && xmlNodes.item.length) {
        for (var i = 0; i < xmlNodes.item.length; i++) {
            var item = document.createElement(this.htmlResponse.item);
            var color = '';
            try {
                if (xmlNodes.item[i].getAttribute('color')) color = xmlNodes.item[i].getAttribute('color');
            }
            catch (e) {
            }
            var html = xmlNodes.item[i].firstChild.data;
            this.currValues[i] = xmlNodes.item[i].firstChild.data;
            this.currIds[i] = xmlNodes.item[i].getAttribute('_id');
            var searchString = new RegExp('(' + params.string + ')', 'gi');
            var replaceString = '<' + this.htmlResponse.value + '>' + '$1' + '</' + this.htmlResponse.value + '>';
            html = html.replace(searchString, replaceString);
            if (color) {
                html = '<span style="color: ' + color + '">' + html + '</span>';
            }
            params.output.appendChild(item);
            item.innerHTML = html;
        }

        params.field.onfocus = function () {
            //jQuery(params.output).removeClass('hidden');
        }

        params.field.onblur = function (ev) {

            if (!ev) {
                ev = window.event;
            }

            var targ;
            if (!ev) ev = window.event;
            if (ev.target) targ = ev.target;
            else if (ev.srcElement) targ = ev.srcElement;
            if (targ.nodeType == 3) // defeat Safari bug
                targ = targ.parentNode;

            if (targ != params.field && targ.parentNode != params.output) {
                jQuery(params.output).addClass('hidden');
            }
        }

        document.onclick = function (ev) {

            var targ = (typeof event !== 'undefined') ? event.srcElement : ev.target;

            if (targ != params.field && targ.parentNode != params.output) {
                jQuery(params.output).addClass('hidden');
            }
        }
        this.mouseHandler();
    }
};

// устанавливаем значение
autosuggestClass.prototype.setValue = function () {
    var result = false;
    var replacedValue;
    if (this.params.output && this.params.output.getElementsByTagName(this.htmlResponse.item)[0]) {
        var result_items = this.params.output.getElementsByTagName(this.htmlResponse.item);
        var result_items_length = result_items.length;
        for (var i = 0; i < result_items.length; i++) {
            if (result_items[i].className == 'active') {
                if (this.isMultiplySuggestionsOn == true) {
                    if (this.isQuoteOpened == true || this.isQuoteClosedPreviously == true) {
                        replacedValue = this.params.string.replace(this.params.string, this.currValues[this.position - 1]);
                        if (this.params.noIndexedString != '') {
                            this.params.field.value = this.params.noIndexedString + this.symbol + this.spacer + '"' + replacedValue;
                            if (this.isQuoteClosedPreviously == true) {
                                this.params.field.value += '"';
                                result = true;
                            }
                        }
                        else {
                            this.params.field.value = '"' + replacedValue;
                            result = true;
                            if (this.isQuoteClosedPreviously == true) {
                                this.params.field.value += '"';
                            }
                        }
                    }
                    else {
                        replacedValue = this.params.string.replace(this.params.string, this.currValues[this.position - 1]);
                        if (this.params.noIndexedString != '') {
                            this.params.field.value = this.params.noIndexedString + this.symbol + this.spacer + replacedValue + ", ";
                            result = true;
                        }
                        else {
                            this.params.field.value = replacedValue + ", ";
                            result = true;
                        }
                    }
                }
                else {
                    if (this.isQuoteOpened == true || this.isQuoteClosedPreviously == true) {
                        this.params.field.value = '"' + this.currValues[this.position - 1];
                        result = true;
                    }
                    else {
                        this.params.field.value = this.currValues[this.position - 1];
                        result = true;
                    }
                }

                jQuery(this.params.output).addClass('hidden');

                this.params.output.innerHTML = '';

                if (this.params.field.value && this.params.field.value.match(/\r\n$/)) {
                    this.params.field.value = this.params.field.value.replace(/\r\n$/, '');
                }

                else {
                    this.params.field = this.currValues[i];
                    jQuery(this.params.output).addClass('hidden');
                    this.params.output.innerHTML = '';
                }
            }
        }
    }
    return result;
};

// навигация по результатам запроса с помощью клавиатуры
autosuggestClass.prototype.makeSteps = function (direction) {
    var replacedValue;
    if (this.params.output.getElementsByTagName(this.htmlResponse.item)[0]) {
        var result_items = this.params.output.getElementsByTagName(this.htmlResponse.item);
        var result_items_length = result_items.length;
        for (var i = 0; i < result_items.length; i++) {
            result_items[i].className = '';
        }
        this.spacer = '';
        this.symbol == ',' ? this.spacer = ' ' : this.spacer = '';
        if (direction == 'down' && this.position <= result_items_length - 1) {
            result_items[this.position].className = 'active';
            if (this.isMultiplySuggestionsOn == true) {
                if (this.isQuoteOpened == true || this.isQuoteClosedPreviously == true) {
                    replacedValue = this.params.string.replace(this.params.string, this.currValues[this.position]);
                    if (this.params.noIndexedString != '') {
                        this.params.field.value = this.params.noIndexedString + this.symbol + this.spacer + '"' + replacedValue;
                        if (this.isQuoteClosedPreviously == true) {
                            this.params.field.value += '"';
                        }
                    }
                    else {
                        this.params.field.value = '"' + replacedValue;
                        if (this.isQuoteClosedPreviously == true) {
                            this.params.field.value += '"';
                        }
                    }
                }
                else {
                    replacedValue = this.params.string.replace(this.params.string, this.currValues[this.position]);
                    if (this.params.noIndexedString != '') {
                        this.params.field.value = this.params.noIndexedString + this.symbol + this.spacer + replacedValue;
                    }
                    else {
                        this.params.field.value = replacedValue;
                    }
                }
            }
            else {
                if (this.isQuoteOpened == true || this.isQuoteClosedPreviously == true) {
                    this.params.field.value = '"' + this.currValues[this.position];
                    if (this.isQuoteClosedPreviously == true) {
                        this.params.field.value += '"';
                    }
                }
                else {
                    this.params.field.value = this.currValues[this.position];
                }
            }
            this.position++;
        }
        else if (direction == 'up' && this.position > 1) {
            result_items[this.position - 2].className = 'active';
            if (this.isMultiplySuggestionsOn == true) {
                if (this.isQuoteOpened == true || this.isQuoteClosedPreviously == true) {
                    replacedValue = this.params.string.replace(this.params.string, this.currValues[this.position - 2]);
                    if (this.params.noIndexedString != '') {
                        this.params.field.value = this.params.noIndexedString + this.symbol + this.spacer + '"' + replacedValue;
                        if (this.isQuoteClosedPreviously == true) {
                            this.params.field.value += '"';
                        }
                    }
                    else {
                        this.params.field.value = '"' + replacedValue;
                        if (this.isQuoteClosedPreviously == true) {
                            this.params.field.value += '"';
                        }
                    }
                }
                else {
                    replacedValue = this.params.string.replace(this.params.string, this.currValues[this.position - 2]);
                    if (this.params.noIndexedString != '') {
                        this.params.field.value = this.params.noIndexedString + this.symbol + this.spacer + replacedValue;
                    }
                    else {
                        this.params.field.value = replacedValue;
                    }
                }
            }
            else {
                if (this.isQuoteOpened == true || this.isQuoteClosedPreviously == true) {
                    this.params.field.value = '"' + this.currValues[this.position - 2];
                    if (this.isQuoteClosedPreviously == true) {
                        this.params.field.value += '"';
                    }
                }
                else {
                    this.params.field.value = this.currValues[this.position - 2];
                }
            }
            this.position--;
        }
        else if (this.position > result_items_length - 1) {
            this.position = 0;
            result_items[this.position].className = 'active';
            if (this.isMultiplySuggestionsOn == true) {
                if (this.isQuoteOpened == true || this.isQuoteClosedPreviously == true) {
                    replacedValue = this.params.string.replace(this.params.string, this.currValues[this.position]);
                    if (this.params.noIndexedString != '') {
                        this.params.field.value = this.params.noIndexedString + this.symbol + this.spacer + '"' + replacedValue;
                        if (this.isQuoteClosedPreviously == true) {
                            this.params.field.value += '"';
                        }
                    }
                    else {
                        this.params.field.value = '"' + replacedValue;
                        if (this.isQuoteClosedPreviously == true) {
                            this.params.field.value += '"';
                        }
                    }
                }
                else {
                    replacedValue = this.params.string.replace(this.params.string, this.currValues[this.position]);
                    if (this.params.noIndexedString != '') {
                        this.params.field.value = this.params.noIndexedString + this.symbol + this.spacer + replacedValue;
                    }
                    else {
                        this.params.field.value = replacedValue;
                    }
                }
            }
            else {
                if (this.isQuoteOpened == true || this.isQuoteClosedPreviously == true) {
                    this.params.field.value = '"' + this.currValues[this.position];
                    if (this.isQuoteClosedPreviously == true) {
                        this.params.field.value += '"';
                    }
                }
                else {
                    this.params.field.value = this.currValues[this.position];
                }
            }
            this.position++;
        }
        else if (direction == 'up' && this.position <= 1) {
            this.position = result_items_length - 1;
            result_items[this.position].className = 'active';
            if (this.isMultiplySuggestionsOn == true) {
                if (this.isQuoteOpened == true || this.isQuoteClosedPreviously == true) {
                    replacedValue = this.params.string.replace(this.params.string, this.currValues[this.position]);
                    if (this.params.noIndexedString != '') {
                        this.params.field.value = this.params.noIndexedString + this.symbol + this.spacer + '"' + replacedValue;
                        if (this.isQuoteClosedPreviously == true) {
                            this.params.field.value += '"';
                        }
                    }
                    else {
                        this.params.field.value = '"' + replacedValue;
                        if (this.isQuoteClosedPreviously == true) {
                            this.params.field.value += '"';
                        }
                    }
                }
                else {
                    replacedValue = this.params.string.replace(this.params.string, this.currValues[this.position]);
                    if (this.params.noIndexedString != '') {
                        this.params.field.value = this.params.noIndexedString + this.symbol + this.spacer + replacedValue;
                    }
                    else {
                        this.params.field.value = replacedValue;
                    }
                }
            }
            else {
                if (this.isQuoteOpened == true || this.isQuoteClosedPreviously == true) {
                    this.params.field.value = '"' + this.currValues[this.position];
                    if (this.isQuoteClosedPreviously == true) {
                        this.params.field.value += '"';
                    }
                }
                else {
                    this.params.field.value = this.currValues[this.position];
                }
            }
            this.position++;
        }
    }
};

// навигация по результатам запроса с помощью мышки
autosuggestClass.prototype.mouseHandler = function () {
    var _this = this;
    var items = this.params.output.getElementsByTagName(this.htmlResponse.item);
    var items_length = items.length;
    var replacedValue;
    for (var i = 0; i < items_length; i++) {
        items[i].onmouseover = function (i_) {
            return function () {
                for (var k = 0; k < items_length; k++) {
                    items[k].className = '';
                }
                this.className = 'active';
                _this.position = i_ + 1;
            }
        }(i);
        items[i].onmouseout = function () {
            this.className = '';
            /* _this.position = 0; */
        }
        items[i].onclick = function (i_) {
            return function () {

                var holder = getParentByClassName(this, _this.classNames.holder);
                var field = getElementsByClassName(holder, '*', _this.classNames.field)[0];

                if (_this.callback) {
                    jQuery(_this.params.output).addClass('hidden');
                    _this.params.output.innerHTML = '';
                    field.value = '';
                    var call = _this.callback.split('.');
                    window[call[0]][call[1]]({id : _this.currIds[i_], val : _this.currValues[i_]});
                    return false;
                }

                if (_this.isMultiplySuggestionsOn == true) {
                    if (_this.isQuoteOpened == true) {
                        replacedValue = _this.params.string.replace(_this.params.string, _this.currValues[i_]);
                        _this.spacer = '';
                        _this.symbol == ',' ? _this.spacer = ' ' : _this.spacer = '';
                        if (_this.params.noIndexedString != '') {
                            _this.params.field.value = _this.params.noIndexedString + _this.symbol + _this.spacer + '"' + replacedValue;
                        }
                        else {
                            _this.params.field.value = '"' + replacedValue;
                        }
                    }
                    else {
                        replacedValue = _this.params.string.replace(_this.params.string, _this.currValues[i_]);
                        _this.spacer = '';
                        _this.symbol == ',' ? _this.spacer = ' ' : _this.spacer = '';
                        if (_this.params.noIndexedString != '') {
                            _this.params.field.value = _this.params.noIndexedString + _this.symbol + _this.spacer + replacedValue + ", ";
                        }
                        else {
                            _this.params.field.value = replacedValue + ", ";
                        }
                    }
                    jQuery(_this.params.output).addClass('hidden');
                }
                else {
                    if (_this.isQuoteOpened == true) {
                        field.value = '"' + _this.currValues[i_];
                    }
                    else {
                        field.value = _this.currValues[i_];
                    }
                }

                jQuery(_this.params.output).addClass('hidden');

                _this.params.output.innerHTML = '';

                field.focus();
                moveCaretToEnd(field);
            }
        }(i);
    }
};