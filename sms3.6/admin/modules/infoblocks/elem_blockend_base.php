<?php

require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TBlockendBaseElement extends TElems
{
    var $editable = array();
    var $elem_name = "elem_blockend";
    var $elem_table = "infoblocks";
    var $elem_type = "single";

    var $elem_str = array(
        'footer_title' => array('Название кнопки "Подробнее"', 'Footer Title'),
        'footer_title_link' => array('Ссылка на кнопке', 'Footer Title link'),
        'footer_text' => array('', ''),
        'footer_text_fck' => array('', ''),
        'footer_text_area' => array('', ''),
        'footer_text_radio' => array('Текст', 'Text'),
    );

    var $elem_fields = array(
        'columns' => array(
            'footer_text' => array(
                'type' => 'hidden',
            ),
            'footer_text_radio' => array(
                'type' => 'radio',
                'option' => array('0' => 'редактор текста', '1' => 'код без форматирования'),
            ),
            'footer_text_fck' => array(
                'type' => 'fck',
                'size'   => array('100%','300'),
                'db_field' => false,
            ),
            'footer_text_area' => array(
                'type' => 'textarea',
                'db_field' => false,
            ),
            'footer_title' => array(
                'type' => 'text',
            ),
            'footer_title_link' => array(
                'type' => 'text',
            ),
        ),
	'id_field' => 'id',
    );

    var $elem_req_fields = array();

    var $script = "";

    function ElemInit(){
        $columns = sql_getRows("SHOW COLUMNS FROM " . $this->elem_table . "", true);
        if (!isset($columns['footer_text_radio'])) {
            sql_query("ALTER TABLE " . $this->elem_table . " ADD footer_text_radio TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0 -  редактор текста; 1 - html-код баннера'");
        }
        if (!isset($columns['footer_title'])) {
            sql_query("ALTER TABLE {$this->elem_table} ADD footer_title VARCHAR( 255 ) NOT NULL COMMENT 'Название кнопки Подробнее';");
        }
        if (!isset($columns['footer_title_link'])) {
            sql_query("ALTER TABLE {$this->elem_table} ADD footer_title_link VARCHAR( 255 ) NOT NULL COMMENT 'Ссылка на кнопке Подробнее';");
        }

        $id = (int)get('id');
        if ($id) {
            $infoblock_end = sql_getRow("SELECT * FROM " . $this->elem_table . " WHERE id = " . $id);
            if ($infoblock_end['footer_text_radio']) {
                $this->elem_fields['columns']['footer_text']['value'] = htmlspecialchars($infoblock_end['footer_text']);
                $this->elem_fields['columns']['footer_text_area']['value'] = $infoblock_end['footer_text'];
            } else $this->elem_fields['columns']['footer_text_fck']['value'] = $infoblock_end['footer_text'];
        }

        $this->script .= "

        function elem1(name) {
            return $('#tr_fld\\\\[".$this->tabname."\\\\]\\\\[' + name + '\\\\]');
        }

        function elemName1(name) {
            return 'fld[".$this->tabname."][' + name + ']';
        };

        function getFck1(name) {
            name = elemName1(name);
            for(nameFck in CKEDITOR.instances) {
                if(name == nameFck) {
                    return CKEDITOR.instances[name];
                }
            }
        }

        function open_fck_footer(name_fck, name_area) {
            var fck = getFck1(name_fck);
            var footer_text_fck = $(elem1(name_fck));
            var footer_text_area = $(elem1(name_area));

            footer_text_fck.children('span').show();
            footer_text_area.hide();

            var data = footer_text_area.children('textarea').val();
            if (data.length) fck.setData(data);

            fck.container.show();
            fck.updateElement();
        }

        function close_fck_footer(name_fck, name_area) {
            var fck = getFck1(name_fck);
            var footer_text_fck = $(elem1(name_fck));
            var footer_text_area = $(elem1(name_area));
            fck.container.hide();
            fck.updateElement();

            footer_text_fck.children('span').hide();
            footer_text_area.show();
            footer_text_area.children('textarea').css({
                'width'         :   '98%',
                'height'        :   fck.config.height
            });

            var data = fck.getData();
            if (data.length) footer_text_area.children('textarea').val(data);
        }

        $(function () {
            var footer_text_radio   = $(elem1('footer_text_radio')).children('input');
            $(footer_text_radio).click(function() {
                if($(this).val() == 1) {
                    close_fck_footer('footer_text_fck', 'footer_text_area');
                } else {
                    open_fck_footer('footer_text_fck', 'footer_text_area');
                }
            });

            CKEDITOR.on( 'instanceReady', function( ev )
            {
                " . (isset($infoblock_end) && $infoblock_end['footer_text_radio'] ? "close_fck_footer" : "open_fck_footer") . "('footer_text_fck', 'footer_text_area');
            });
        });
    ";

        TElems::ElemInit();
    }

    /**
     * Вызывается перед сохранением в базу
     *
     * @param array $fld
     * @return array
     */
    function ElemRedactBefore($fld) {
        $fld = parent::ElemRedactBefore($fld);
        $fld['footer_text'] =  ($fld['footer_text_radio']) ? $fld['footer_text_area'] : $fld['footer_text_fck'];
        return $fld;
    }
}