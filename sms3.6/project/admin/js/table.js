//**Класс js-функций от которого будут наследоваться вкладки или главная таблица  **//
//**2011-10-17 12:15:03 dereshov Exp**//

function extend(Child, Parent) {
    var F = function () {
    }
    F.prototype = Parent.prototype
    Child.prototype = new F()
    Child.prototype.constructor = Child
    Child.superclass = Parent.prototype
}

function table_js() {
    var roll;
    var thisname;
    var thisname2;
    var src_frame;
    var tabframename;
    var id_table_list_name;
}

table_js.prototype.showSelectDiv = function (id, obj) {
    var self = this;
    var obj_href;
    var obj_init;
    if (typeof(id) != 'undefined') {
        obj_href = self.src_frame + '&id=' + id;
    } else {
        obj_href = self.src_frame;
    }

    obj_init = $(obj).attr('init');
    if (typeof(obj_init) == 'undefined') {
        $(obj).fancybox({
            'type':'iframe',
            'width':'90%',
            'height':'90%',
            'href':obj_href,
            'centerOnScroll':true,
            'hideOnOverlayClick':false
        });
        $(obj).attr('init', 1);
        $(obj).click();
    }
    return true;
}

table_js.prototype.checkInvert = function (name, item) {
    var self = this;
    var state = false;
    if (item) state = item.checked;
    var form = document.forms['editform'];
    for (var i = 0; i < form.elements.length; i++) {
        if (form.elements[i].name && form.elements[i].name.indexOf(name) >= 0 && form.elements[i].type == 'checkbox') {
            form.elements[i].checked = state;
            form.elements[i].parentNode.parentNode.className = state ? 'selected' : 'out';
        }
    }
}

table_js.prototype.clickCheck = function (tr) {
    var self = this;
    // No action if click from INPUT (checkbox)
    var cb = tr.firstChild.firstChild;
    if (!cb || cb.tagName != 'INPUT') return;
    var i = 0;
    if (event.srcElement.tagName != 'INPUT' && !event.ctrlKey) { // click on row
        // clear all `id` checkboxes
        for (i = 0; i < cb.form.elements.length; i++) {
            if (cb.form.elements[i].name && cb.form.elements[i].type == 'checkbox' && cb.form.elements[i].name.indexOf('id') >= 0) {
                cb.form.elements[i].checked = false;
                if (self.roll) cb.form.elements[i].parentNode.parentNode.className = 'out';
            }
        }
        if (cb.type == 'checkbox') cb.checked = !cb.checked;
        if (self.roll) tr.className = 'selected';
    }
    else {        // click on checkbox
        if (cb.type == 'checkbox' && event.ctrlKey) cb.checked = !cb.checked;
        // count checkboxes (WARNING! counted only 'id' checkboxes)
        var count = 0;
        for (i = 0; i < cb.form.elements.length; i++) {
            if (cb.form.elements[i].name && cb.form.elements[i].name.indexOf('id') >= 0 && cb.form.elements[i].type == 'checkbox') {
                count += cb.form.elements[i].checked;
            }
        }
        if (self.roll) tr.className = cb.checked ? 'selected' : 'over';
    }

    // user defined onclick in lib.table()
    //< ?php echo $this->click;?>
}

table_js.prototype.groll = function (name) {
    var self = this;
    var div = document.getElementById(name);
    if (!div) return;
    if (!div.style.display || div.style.display == 'none') {
        div.style.display = 'block';
    } else {
        div.style.display = 'none';
    }
}

table_js.prototype.gEdit = function () {
    var self = this;
    var inhtml = '';
    var coll = document.forms['editform'].tags("input");

    if (coll != null) {
        for (i = 0; i < coll.length; i++) {
            if (coll[i].type == "checkbox") {
                if (coll[i].checked == true) {
                    if (coll[i].name.substring(0, 2) == 'id') {
                        inhtml = inhtml + '<input type="hidden" name="group[id][]" value="' + coll[i].value + '" />';
                    }
                }
            }
        }
    }
    document.forms['groupform'].innerHTML = document.forms['groupform'].innerHTML + inhtml;
    document.forms['groupform'].submit();
}

table_js.prototype.deleteListsElem = function () {
    var self = this;
    var href = location.href;
    var pos = href.search(/#/);
    var tab_id = href.substr(pos + 1);

    var ids = $('#' + self.id_table_list_name).find("input[type=checkbox]:checked");
    for (var i = 0; i < ids.length; i++) {
        var value = parseInt(ids[i].value);
        if (value > 0) {
            var hidden = "<input type='hidden' value='" + value + "' name='fld[" + tab_id + "][del_ids][]' id='fld[" + tab_id + "][del_ids][" + value + "]'>";
            $('#editform').append(hidden);

            var row_id = tab_id + "_" + self.id_table_list_name + "_" + value;
            $('#' + row_id).remove();
        }
    }
}

// добавление нового элемента (под таблицу)
table_js.prototype.addElem = function (ret) {
    if ($("#tags_span_" + ret['id']).length) return;
    var self = this;
    var href = location.href;
    var pos = href.search(/#/);
    var tab_id = href.substr(pos + 1);

    var hidden = "<input type='hidden' value='" + ret['id'] + "' name='fld[" + tab_id + "][ids][]' id='fld_" + tab_id + "_ids_" + ret['id'] + "'>";
    $('#editform').append(hidden);

    var elem = '';
    if ($('#newElems' + tab_id).html() == '') {
        $('#newElems' + tab_id).append('<ul></ul>');
        $('#newElems' + tab_id).show();
    }
    var delItem = $('<span class="delItem" title="удалить">удалить</span>');
    delItem.click(function () {
        self.delElem(ret['id'], tab_id);
    });
    $('#newElems' + tab_id).find('ul').append('<span id="tags_span_' + ret['id'] + '"></span>');
    $('#tags_span_' + ret['id']).append(delItem);
    $('#tags_span_' + ret['id']).append(ret['val']);
}

// удаление элемента под таблицей
table_js.prototype.delElem = function (id, tab_id) {
    $('#tags_span_' + id + '').remove();
    $("#fld_" + tab_id + "_ids_" + id + "").remove();
}

// поменять местами строки
table_js.prototype.changePriority = function (direction, tab_id, id, page_name, elem_name) {
    var self = this;
    $.ajax({
        type: "POST",
        url: location.href,
        data: {'do': 'changePriority', tab: tab_id, id: id, direction: direction, page_name: page_name, elem_name: elem_name},
        dataType: 'json',
    }).
    done(function (ret) {
        if (typeof ret['error'] != 'undefined') alert(ret['error']);
        else {
            var table_main = $('#' + self.id_table_list_name);
            table_main.find('tr[class!=ajax_table_header_row]').remove();
            for (var i=0; i < ret['ret'].length; i++) {
                table_main.append($(ret['ret'][i][0]));
            }
        }
    });
}

// сохранение признака visible у строки
table_js.prototype.changeLinkVisible = function (checkbox, tab_id, id, page_name, elem_name) {
    var self = this;
    $.ajax({
        type: "POST",
        url: location.href,
        data: {'do': 'changeLinkVisible', tab: tab_id, id: id, visible: $(checkbox).attr('checked'), page_name: page_name, elem_name: elem_name},
        dataType: 'json',
    }).
    done(function (ret) {
        if (typeof ret['error'] != 'undefined') alert(ret['error']);
        else {
        }
    });
}