<?php
require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TMainElement extends TElems
{

    var $elem_name = "elem_questions";
    var $elem_table = "surveys_quests";
    var $elem_type = "single";
    var $elem_str = array(
        'text' => array('Текст вопроса', 'Title',),
        'type' => array('Тип вопроса', 'Question type',),
        'type_text' => array('текстовое поле в одну строку', 'Text input',),
        'type_multi' => array('с множественным выбором (checkbox)', 'Checkbox',),
        'type_single' => array('с единственным выбором (radio)', 'Radio',),
        'type_textarea' => array('текстовое поле в несколько строк', 'Textarea',),
        'type_catalog' => array('выбор из справочника (catalog)', 'Catalog',),
        'catalog' => array('Справочник', 'Catalog',),
        'priority' => array('Приоритет', 'Priority',),
        'variant_text' => array('Текст варианта ответа', 'Title',),
        'edit_variants' => array('Варианты ответов', 'Questions variants',),
        'btn_add_variant' => array('Добавить', 'Add',),
        'btn_delete_variant' => array('Удалить', 'Remove',),
        'req' => array('Обязательный', 'Req',),
        'free_form' => array('В свободной форме', 'Free',),
    );
    //поля для выборки из базы элема
    var $elem_fields = array(
        'columns' => array(
            'text' => array(
                'type' => 'textarea',
                'rows' => '4',
                'cols' => '30',
            ),
            'req' => array(
                'type' => 'checkbox',
            ),
            'type' => array(
                'type' => 'select',
                'func' => 'get_types',
            ),
            'priority' => array(
                'type' => 'text',
            ),
            'variants' => array(
                'type' => 'words',
            ),
        ),
        'id_field' => 'id',
        'folder' => ''
    );
    var $elem_where = "";
    var $elem_req_fields = array('text');
    var $script = "
    var i_vars = 0;     // счётчик добавленных вариантов
    function variant_append(qid) {
        var uid = 'v' + i_vars++;
        $('#vars_tmpl').tmpl({'pid': qid, 'uid': uid}).appendTo($('#quest_' + qid + ' div.vars_container'));
    }

    function variant_remove(uid) {
        $('#var_' + uid).remove();
    }

    $(document).ready(function(){
        $('#fld\\\\[tab0\\\\]\\\\[type\\\\]').change(function(){
            if ($(this).val() == 'multi' || $(this).val() == 'single') {
                $('div.variants').show();
            } else {
                $('div.variants').hide();
            }

            if ($(this).val() == 'catalog') {
                $('#variantsCatalog').show();
            } else {
                $('#variantsCatalog').hide();
            }
        });
        $('#fld\\\\[tab0\\\\]\\\\[type\\\\]').change();
    });
    ";

    ########################

    function ElemInit() {
        // список вариантов ответа
        $id_quest = (int)get('id', 0, 'pg');
        $id_survey = (int)get('pid', 0, 'pg');

        $value = '';
        if (!empty($id_survey)) {
            $variants = array();
            if (!empty($id_quest) && !empty($id_survey)) {
                $variants = sql_getRows("SELECT * FROM surveys_quest_variants WHERE id_survey = " . $id_survey . " AND id_quest = " . $id_quest . " ORDER BY priority ASC");
            }
            $value .= '<script src="/admin/js_custom/jquery.tmpl.min.js" type="text/javascript"></script>';
            $value .= '<div class="variants" style="display: none;">';
            $value .= '<br /><fieldset id="quest_' . $id_quest . '">';
            $value .= '<legend>' . $this->elem_str['edit_variants'][0] . '</legend>';
            foreach ($variants as $variant) {
                $value .= '
                <div id="var_' . $variant['id'] . '">
                    <div class="elemBox">
                        <label class="float">' . $this->elem_str['variant_text'][0] . ':</label><input class="text" type="text" name="fld[vars][' . $variant['id'] . '][text]" value="' . htmlspecialchars($variant['text']) . '" style="width: 280px;">
                    </div>
                    <div class="elemBox">
                        <label class="float">' . $this->elem_str['priority'][0] . ':</label><input class="text" style="width:50px;" type="text" name="fld[vars][' . $variant['id'] . '][priority]" value="' . $variant['priority'] . '" size=5>
                    </div>
                    <div class="checkBox">
                        <input class="check" type="checkbox" name="fld[vars][' . $variant['id'] . '][free_form]" value="1" ' . ($variant['free_form'] ? 'checked' : '') . '><label class="check">' . $this->elem_str['free_form'][0] . '</label>
                    </div>
                    <div class="checkBox">
                        <input class="check" type="checkbox" name="fld[vars][' . $variant['id'] . '][delete]" value="1" ' . ($variant['delete'] ? 'checked' : '') . '><label class="check">' . $this->elem_str['btn_delete_variant'][0] . '</label>
                    </div>
                    <hr />
                </div>';
            }
            $value .= '<div class="vars_container margBottom"></div>';
            $value .= '<a class="button margBottom" href="javascript:void(0);" onclick="variant_append(' . $id_quest . ');"><span>' . $this->elem_str['btn_add_variant'][0] . '</span></a>';
            $value .= '</fieldset>';
            $value .= '</div>'; // div.variants
            $value .= '
            <script type="text/x-jquery-tmpl" id="vars_tmpl">
                <div id="var_${uid}">
                    <div class="elemBox">
                        <label class="float">' . $this->elem_str['variant_text'][0] . ':</label><input class="text" type="text" name="fld[vars][${uid}][text]" style="width: 280px;">
                    </div>
                    <div class="elemBox">
                        <label class="float">' . $this->elem_str['priority'][0] . ':</label><input class="text" style="width:50px;" type="text" name="fld[vars][${uid}][priority]" size=5>
                    </div>
                    <div class="checkBox">
                        <input class="check" type="checkbox" name="fld[vars][${uid}][free_form]" value="1"><label class="check">' . $this->elem_str['free_form'][0] . '</label>
                    </div>
                    <a class="button" href="javascript:void(0);" onclick="variant_remove(\'${uid}\');">' . $this->elem_str['btn_delete_variant'][0] . '</a>
                    <div class="clear"></div>
                    <hr />
                </div>
            </script>';

            global $surveys_dictonaries; // список справочников из settings.cfg
            // catalog type select
            if(count($surveys_dictonaries)) {
                $value .= '
                <div id="variantsCatalog" style="display: none;">
                    <div class="elemBox">
                        <label class="float">' . $this->elem_str['catalog'][0] . ':</label>
                        <select name="fld[catalog]">';
                        foreach ($surveys_dictonaries as $id => $dict) {
                            $value .='<option value="' . $id . '">' . $dict['title'] . '</option>';
                        }
                $value .= '
                        </select>
                    </div>
                </div>';// div.variantsCatalog
            }

        }

        $this->elem_fields['columns']['variants']['value'] = $value;
        return parent::ElemInit();
    }

    /**
     * Вызывается после сохранения в БД
     * @param array $fld
     * @param integer $id
     * @return array
     */
    function ElemRedactAfter($fld, $id) {
        // сохранение вариантов ответа
        $id_quest = (int)get('id', 0, 'pg');
        $id_survey = (int)get('pid', 0, 'pg');

        // если поменялся тип вопроса, то предыдущие варианты надо удалить
        $old_type = sql_getValue("SELECT type FROM surveys_quests WHERE id=" . $id_quest);
        if ($old_type != $fld['type']) {
            sql_query("DELETE FROM surveys_quest_variants
            WHERE
                id_survey = " . $id_survey . "
                AND id_quest  = " . $id_quest
            );
        }

        $variants = isset($_POST['fld']['vars']) ? (array)$_POST['fld']['vars'] : array();
        if (empty($variants) && ($fld['type'] == "text" || $fld['type'] == "textarea")) {
            // вариант в свободной форме
            $variants[] = array(
                'text' => '',
                'priority' => 0,
                'free_form' => true
            );
        }
        if (!empty($variants) && !empty($id_quest) && !empty($id_survey)) {
            foreach ($variants as $var_id => $variant) {
                if (isset($variant['delete']) && $variant['delete']) {
                    sql_query("DELETE FROM surveys_quest_variants WHERE id = " . $var_id . " AND id_survey = " . $id_survey . " AND id_quest = " . $id_quest . " LIMIT 1");
                    continue;
                }
                if (empty($variant['text']) && !isset($variant['free_form'])) continue;

                $data = array(
                    'text' => $variant['text'],
                    'free_form' => isset($variant['free_form']) ? 1 : 0,
                    'priority' => (int)$variant['priority']
                );

                if ((int)$var_id) {
                    sql_update('surveys_quest_variants', $data, "id = " . $var_id . " AND id_survey = " . $id_survey . " AND id_quest = " . $id_quest);
                } else {
                    $data['id_survey'] = $id_survey;
                    $data['id_quest'] = $id_quest;
                    sql_insert('surveys_quest_variants', $data);
                }
            }
        }

        $dictonary = isset($_POST['fld']['catalog']) ? $_POST['fld']['catalog'] : false;
        if ($fld['type'] == "catalog" && !empty($dictonary) && !empty($id_quest) && !empty($id_survey)) {
            $data = array(
                'text' => $dictonary,
                'free_form' => 1,
                'priority' => 0
            );
            // проверка, есть уже запись или еще нет
            if ($var_id=sql_getValue("SELECT id FROM surveys_quest_variants WHERE id_survey = " . $id_survey . " AND id_quest = " . $id_quest)) {
                sql_update('surveys_quest_variants', $data, "id = " . $var_id . " AND id_survey = " . $id_survey . " AND id_quest = " . $id_quest);
            } else {
                $data['id_survey'] = $id_survey;
                $data['id_quest'] = $id_quest;
                echo sql_insert('surveys_quest_variants', $data);
            }
        }
        return $fld;
    }

    ########################

    function get_types() {
        global $surveys_dictonaries;
        $types = array(
            'text' => $this->str('type_text'),
            'multi' => $this->str('type_multi'),
            'single' => $this->str('type_single'),
            'textarea' => $this->str('type_textarea'),
        );
        if ($surveys_dictonaries) {
            $types['catalog'] = $this->str('type_catalog');
        }
        return $types;
    }
}